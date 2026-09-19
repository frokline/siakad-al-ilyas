<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Pengumuman;
use App\Models\SasaranPengumuman;
use App\Models\User;
use App\Services\AksesPengumuman;
use App\Services\AturanPengumuman;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class KelolaPengumuman
{
    public function __construct(private AksesPengumuman $akses) {}
    public function buat(int $aktor, array $input): Pengumuman
    {
        $v = AturanPengumuman::isi($input, true);
        $hash = hash('sha256', json_encode([$v['judul'], $v['isi'], $v['berakhir_at'], $v['sasaran']], JSON_THROW_ON_ERROR));
        return DB::transaction(function () use ($aktor, $v, $hash): Pengumuman {
            $u = $this->aktor($aktor);
            $lama = Pengumuman::query()->where('pembuat_id', $aktor)->where('form_token', $v['form_token'])->lockForUpdate()->first();
            if ($lama) {
                abort_unless($this->akses->mengelola($u, $lama), 403);
                if (! hash_equals($lama->hash_permohonan, $hash)) {
                    $this->gagal('form_token', 'Token telah dipakai dengan isi berbeda. Buka formulir baru.');
                }
                return $lama;
            }
            $this->konteks($u, $v['sasaran'], true);
            $this->akhir($v['berakhir_at']);
            $p = new Pengumuman();
            $p->forceFill([
                'pembuat_id' => $aktor,
                'judul' => $v['judul'],
                'isi' => $v['isi'],
                'berakhir_at' => $v['berakhir_at'],
                'status' => 'draf',
                'revisi' => 1,
                'form_token' => $v['form_token'],
                'hash_permohonan' => $hash
            ])->save();
            $this->sasaran($p, $v['sasaran']);
            $this->audit($u, $p, 'buat', null, 'Membuat draf pengumuman.');
            return $p;
        }, 3);
    }
    public function ubah(int $aktor, Pengumuman $asal, array $input): Pengumuman
    {
        $v = AturanPengumuman::isi($input, false);
        return DB::transaction(function () use ($aktor, $asal, $v): Pengumuman {
            $u = $this->aktor($aktor);
            $p = Pengumuman::query()->lockForUpdate()->findOrFail($asal->id);
            $lama = $p->sasaran()->orderBy('kunci_sasaran')->get();
            $this->konteks($u, $lama->toArray(), false);
            abort_unless($this->akses->mengelola($u, $p), 403);
            $this->versi($p, $v['versi']);
            if ($p->status !== 'draf') {
                $this->gagal('status', 'Hanya draf yang dapat diubah.');
            }
            $this->konteks($u, $v['sasaran'], true);
            $this->akhir($v['berakhir_at']);
            $sebelum = $p->ringkasanAudit();
            $kunciLama = $lama->pluck('kunci_sasaran')->all();
            $kunciBaru = array_map(fn($s) => SasaranPengumuman::kunci($s), $v['sasaran']);
            $p->forceFill(['judul' => $v['judul'], 'isi' => $v['isi'], 'berakhir_at' => $v['berakhir_at']]);
            if (! $p->isDirty() && $kunciLama === $kunciBaru) {
                return $p;
            }
            if ($kunciLama !== $kunciBaru) {
                foreach ($lama as $s) {
                    $s->delete();
                }
                $this->sasaran($p, $v['sasaran']);
            }
            $p->revisi++;
            $p->save();
            $this->audit($u, $p, 'ubah', $sebelum, 'Memperbarui draf pengumuman.');
            return $p;
        }, 3);
    }
    public function tindakan(int $aktor, Pengumuman $asal, array $input): Pengumuman
    {
        $v = AturanPengumuman::tindakan($input);
        return DB::transaction(function () use ($aktor, $asal, $v): Pengumuman {
            $u = $this->aktor($aktor);
            $p = Pengumuman::query()->lockForUpdate()->findOrFail($asal->id);
            $sasaran = $p->sasaran()->orderBy('kunci_sasaran')->get()->toArray();
            $this->konteks($u, $sasaran, $v['aksi'] === 'terbit');
            abort_unless($this->akses->mengelola($u, $p), 403);
            $this->versi($p, $v['versi']);
            if ($p->status === 'arsip' || ($v['aksi'] === 'terbit' && $p->status !== 'draf')) {
                $this->gagal('status', 'Transisi status tidak diizinkan. Muat ulang halaman.');
            }
            $sebelum = $p->ringkasanAudit();
            if ($v['aksi'] === 'terbit') {
                $this->akhir($p->berakhir_at?->format('Y-m-d H:i:s'));
                $p->terbit_at = now('UTC');
            } else {
                $p->diarsipkan_at = now('UTC');
            }
            $p->status = $v['aksi'];
            $p->revisi++;
            $p->save();
            $this->audit($u, $p, $v['aksi'], $sebelum, trim($v['alasan']));
            return $p;
        }, 3);
    }
    private function aktor(int $id): User
    {
        $u = User::query()->lockForUpdate()->findOrFail($id);
        DB::table('user_roles')->where('user_id', $id)->orderBy('role_id')->lockForUpdate()->get();
        $u->roles()->orderBy('roles.id')->lockForUpdate()->get();
        DB::table('dosen')->where('user_id', $id)->orderBy('id')->lockForUpdate()->get();
        abort_unless($u->status === 'aktif' && $this->akses->menulis($u), 403);
        return $u;
    }
    private function konteks(User $u, array $sasaran, bool $tulis): void
    {
        if (count($sasaran) < 1 || count($sasaran) > 10) {
            $this->gagal('sasaran', 'Pilih satu sampai sepuluh sasaran.');
        }
        $admin = $this->akses->admin($u);
        // Urutan deterministik untuk mengurangi deadlock antar-publikasi.
        usort($sasaran, fn($a, $b) => [$a['lingkup'], $a['program_studi_id'] ?? 0, $a['kelas_kuliah_id'] ?? 0, $a['role_id'] ?? 0]
            <=> [$b['lingkup'], $b['program_studi_id'] ?? 0, $b['kelas_kuliah_id'] ?? 0, $b['role_id'] ?? 0]);
        foreach ($sasaran as $s) {
            if (! $admin && $s['lingkup'] !== 'kelas') {
                abort(403, 'Dosen hanya mengumumkan untuk kelas yang diampu.');
            }
            if ($s['role_id'] !== null) {
                $role = DB::table('roles')->where('id', $s['role_id'])->lockForUpdate()->first();
                if (! $role || ! in_array($role->kode, AksesPengumuman::ROLES, true)) {
                    $this->gagal('sasaran', 'Peran sasaran tidak valid.');
                }
                if (! $admin && ! in_array($role->kode, ['dosen', 'mahasiswa'], true)) {
                    abort(403);
                }
                if ($s['lingkup'] !== 'kampus' && ! in_array($role->kode, ['dosen', 'mahasiswa'], true)) {
                    $this->gagal('sasaran', 'Sasaran prodi/kelas hanya dapat difilter ke dosen atau mahasiswa.');
                }
            }
            if ($s['lingkup'] === 'prodi') {
                $prodi = DB::table('program_studi')->where('id', $s['program_studi_id'])->lockForUpdate()->first();
                if (! $prodi || ($tulis && ! $prodi->aktif)) {
                    $this->gagal('sasaran', 'Program studi tidak tersedia/aktif.');
                }
            }
            if ($s['lingkup'] === 'kelas') {
                $hint = DB::table('kelas_kuliah')->where('id', $s['kelas_kuliah_id'])->first();
                if (! $hint) {
                    $this->gagal('sasaran', 'Kelas tidak tersedia.');
                }
                $b = DB::table('rombel')->where('id', $hint->rombel_id)->first();
                if (! $b) {
                    $this->gagal('sasaran', 'Rombel tidak tersedia.');
                }
                $periode = DB::table('periode_akademik')->where('id', $b->periode_akademik_id)->lockForUpdate()->first();
                $rombel = DB::table('rombel')->where('id', $b->id)->lockForUpdate()->first();
                $kelas = DB::table('kelas_kuliah')->where('id', $hint->id)->lockForUpdate()->first();
                if (! $kelas || ! $rombel || ! $periode || $kelas->rombel_id != $rombel->id || $rombel->periode_akademik_id != $periode->id) {
                    $this->gagal('sasaran', 'Konteks kelas berubah. Muat ulang formulir.');
                }
                $pengajar = DB::table('pengajar_kelas')->where('kelas_kuliah_id', $kelas->id)->orderBy('id')->lockForUpdate()->get();
                if (! $admin) {
                    $dosen = DB::table('dosen')->where('user_id', $u->id)->where('status', 'aktif')->first();
                    abort_unless($dosen && $pengajar->contains(fn($p) => $p->dosen_id == $dosen->id && (bool) $p->aktif), 403);
                }
                if ($tulis && (! in_array($kelas->status, ['persiapan', 'aktif'], true) || $periode->status !== 'aktif')) {
                    $this->gagal('sasaran', 'Penulisan memerlukan kelas persiapan/aktif dan periode aktif.');
                }
            }
        }
    }
    private function sasaran(Pengumuman $p, array $targets): void
    {
        foreach ($targets as $s) {
            $model = new SasaranPengumuman();
            $model->forceFill($s + ['pengumuman_id' => $p->id])->save();
        }
    }
    private function versi(Pengumuman $p, string $versi): void
    {
        if ($p->revisi >= 4294967295 || ! hash_equals($p->versiForm(), $versi)) {
            $this->gagal('versi', 'Data telah berubah. Muat ulang sebelum melanjutkan.');
        }
    }
    private function akhir(?string $akhir): void
    {
        if ($akhir !== null && CarbonImmutable::parse($akhir, 'UTC')->lessThanOrEqualTo(now('UTC'))) {
            $this->gagal('berakhir_lokal', 'Batas tayang harus setelah waktu sekarang.');
        }
    }
    private function audit(User $u, Pengumuman $p, string $aksi, ?array $sebelum, string $alasan): void
    {
        $log = new AuditLog();
        $log->forceFill([
            'pelaku_id' => $u->id,
            'entitas' => 'pengumuman',
            'entitas_id' => $p->id,
            'versi_entitas' => $p->revisi,
            'aksi' => $aksi,
            'sebelum' => $sebelum,
            'sesudah' => $p->ringkasanAudit(),
            'alasan' => $alasan,
            'waktu' => now('UTC')
        ])->save();
    }
    private function gagal(string $key, string $pesan): never
    {
        throw ValidationException::withMessages([$key => $pesan]);
    }
}
