<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Berkas;
use App\Models\Kegiatan;
use App\Models\Pengumpulan;
use App\Models\PengumpulanBerkas;
use App\Models\User;
use App\Services\AksesPengumpulan;
use App\Services\AturanJawaban;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class KelolaPengumpulan
{
    public function __construct(private readonly AksesPengumpulan $akses) {}
    public function buat(int $userId, Kegiatan $bound, array $data): Pengumpulan
    {
        if (
            ! Str::isUuid((string) ($data['token_draf'] ?? ''))
            || ! preg_match('/\A(?:0|[1-9][0-9]{0,9})\z/', (string) ($data['dasar_versi'] ?? ''))
        ) {
            AturanJawaban::gagal('Token atau nomor dasar versi tidak valid.');
        }
        return DB::transaction(function () use ($userId, $bound, $data): Pengumpulan {
            $hint = Pengumpulan::query()->where('pemilik_id', $userId)->where('token_draf', $data['token_draf'])->first();
            $id = $hint?->detail_krs_id ?? $this->akses->detail(User::query()->findOrFail($userId), $bound);
            abort_unless($id !== null, 403, 'Keikutsertaan aktif tidak tersedia atau ganda. Hubungi akademik.');
            $c = $this->kunci($userId, $bound->id, $id);
            $lama = Pengumpulan::query()->where('pemilik_id', $userId)->where('token_draf', $data['token_draf'])->lockForUpdate()->first();
            if ($lama) {
                if ($lama->kegiatan_id !== $c['kegiatan']->id || $lama->detail_krs_id !== (int) $c['detail']->id) {
                    AturanJawaban::gagal('Token sudah digunakan untuk kegiatan lain. Buka halaman kembali.');
                }
                return $lama;
            }
            $this->bolehMenulis($c);
            $q = Pengumpulan::query()->where('kegiatan_id', $bound->id)->where('detail_krs_id', $id);
            $draf = (clone $q)->where('status', Pengumpulan::DRAF)->lockForUpdate()->first();
            if ($draf) {
                abort_unless($draf->pemilik_id === $userId, 403);
                return $draf;
            }
            $terakhir = (clone $q)->orderByDesc('versi')->lockForUpdate()->first();
            $nomor = $terakhir?->versi ?? 0;
            if ($nomor !== (int) $data['dasar_versi'] || $nomor >= 4294967295) {
                AturanJawaban::gagal('Daftar versi berubah. Muat ulang sebelum membuat versi berikutnya.');
            }
            $p = new Pengumpulan();
            $p->kegiatan_id = $bound->id;
            $p->detail_krs_id = $id;
            $p->pemilik_id = $userId;
            $p->versi = $nomor + 1;
            $p->revisi = 1;
            $p->token_draf = $data['token_draf'];
            $p->kunci_kirim = bin2hex(random_bytes(32));
            $p->jawaban_teks = null;
            $p->status = Pengumpulan::DRAF;
            $p->penanda_draf = 1;
            $this->bolehMenulis($c);
            $p->save();
            $this->audit($p, $userId, 'buat_draf', null);
            return $p;
        }, 3);
    }
    public function ubah(int $userId, Pengumpulan $bound, array $data): Pengumpulan
    {
        $teks = AturanJawaban::teks($data['jawaban_teks'] ?? null);
        $ids = AturanJawaban::ids($data['berkas_ids'] ?? []);
        return DB::transaction(function () use ($userId, $bound, $data, $teks, $ids): Pengumpulan {
            $c = $this->kunci($userId, $bound->kegiatan_id, $bound->detail_krs_id);
            $p = $this->pengumpulan($bound->id, $userId, $c);
            if ($p->status !== Pengumpulan::DRAF) {
                AturanJawaban::gagal('Kiriman final terkunci. Buat versi baru untuk memperbaikinya.');
            }
            $this->versi($p, $data);
            $this->bolehMenulis($c);
            $sebelum = $p->ringkasanAudit();
            $this->lampiran($p, $c['kegiatan'], $ids);
            $p->jawaban_teks = $teks;
            $p->revisi++;
            $this->bolehMenulis($c); // Jam dibaca ulang setelah menunggu semua kunci berkas.
            $p->save();
            $this->audit($p, $userId, 'simpan_draf', $sebelum);
            return $p;
        }, 3);
    }
    public function kirim(int $userId, Pengumpulan $bound, array $data): Pengumpulan
    {
        return DB::transaction(function () use ($userId, $bound, $data): Pengumpulan {
            $c = $this->kunci($userId, $bound->kegiatan_id, $bound->detail_krs_id);
            $p = $this->pengumpulan($bound->id, $userId, $c);
            if (! is_string($data['kunci_kirim'] ?? null) || ! hash_equals($p->kunci_kirim, $data['kunci_kirim'])) {
                AturanJawaban::gagal('Kunci pengiriman tidak sesuai. Muat ulang detail jawaban.');
            }
            if ($p->status === Pengumpulan::DIKIRIM) {
                return $p;
            } // Tidak membuat audit/versi/waktu baru.
            $this->versi($p, $data);
            $this->bolehMenulis($c);
            $sebelum = $p->ringkasanAudit();
            $this->lampiranSiap($p, $c['kegiatan']);
            if (blank($p->jawaban_teks) && ! $p->lampiran()->exists()) {
                AturanJawaban::gagal('Isi teks jawaban atau lampirkan minimal satu berkas.');
            }
            $p->hash_jawaban = $p->hitungHash();
            $waktu = $this->bolehMenulis($c); // Keputusan final memakai waktu server setelah seluruh lock.
            $p->status = Pengumpulan::DIKIRIM;
            $p->penanda_draf = null;
            $p->dikirim_at = $waktu->startOfSecond();
            $p->tenggat_snapshot = $c['kegiatan']->tenggat_at;
            $p->revisi_kegiatan = $c['kegiatan']->revisi;
            $p->revisi++;
            $p->save();
            $this->audit($p, $userId, 'kirim', $sebelum);
            return $p;
        }, 3);
    }
    private function kunci(int $userId, int $kegiatanId, int $detailId): array
    {
        $user = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
        $roles = $user->roles()->orderBy('roles.id')->lockForUpdate()->get(['roles.id', 'roles.kode']);
        abort_unless($user->status === 'aktif' && $roles->contains('kode', 'mahasiswa'), 403);
        $hint = DB::table('detail_krs as d')->join('krs as k', 'k.id', '=', 'd.krs_id')
            ->join('registrasi_semester as r', 'r.id', '=', 'k.registrasi_semester_id')
            ->join('riwayat_studi as h', 'h.id', '=', 'r.riwayat_studi_id')->join('mahasiswa as m', 'm.id', '=', 'h.mahasiswa_id')
            ->where('d.id', $detailId)->where('m.user_id', $userId)
            ->first(['d.krs_id', 'd.kelas_kuliah_id', 'k.registrasi_semester_id', 'r.riwayat_studi_id', 'h.mahasiswa_id']);
        abort_unless($hint, 403);
        $mahasiswa = $this->baris('mahasiswa', (int) $hint->mahasiswa_id);
        $riwayat = $this->baris('riwayat_studi', (int) $hint->riwayat_studi_id);
        $hintKelas = DB::table('kelas_kuliah')->where('id', $hint->kelas_kuliah_id)->firstOrFail();
        $hintRombel = DB::table('rombel')->where('id', $hintKelas->rombel_id)->firstOrFail();
        $periode = $this->baris('periode_akademik', (int) $hintRombel->periode_akademik_id);
        $rombel = $this->baris('rombel', (int) $hintKelas->rombel_id);
        $kelas = $this->baris('kelas_kuliah', (int) $hint->kelas_kuliah_id);
        $registrasi = $this->baris('registrasi_semester', (int) $hint->registrasi_semester_id);
        $krs = $this->baris('krs', (int) $hint->krs_id);
        $detail = $this->baris('detail_krs', $detailId); // Serialisasi alokasi nomor versi/satu draf.
        $kegiatan = Kegiatan::query()->whereKey($kegiatanId)->lockForUpdate()->firstOrFail();
        $pertemuan = $kegiatan->pertemuan_id === null ? null : $this->baris('pertemuan', $kegiatan->pertemuan_id);
        if (
            (int) $mahasiswa->user_id !== $userId || (int) $riwayat->mahasiswa_id !== (int) $mahasiswa->id
            || (int) $registrasi->riwayat_studi_id !== (int) $riwayat->id || (int) $krs->registrasi_semester_id !== (int) $registrasi->id
            || (int) $detail->krs_id !== (int) $krs->id || (int) $detail->kelas_kuliah_id !== (int) $kelas->id
            || $kegiatan->kelas_kuliah_id !== (int) $kelas->id || (int) $kelas->rombel_id !== (int) $rombel->id
            || (int) $rombel->periode_akademik_id !== (int) $periode->id || (int) $registrasi->rombel_id !== (int) $rombel->id
            || (int) $registrasi->periode_akademik_id !== (int) $periode->id
            || ($pertemuan && (int) $pertemuan->kelas_kuliah_id !== (int) $kelas->id)
        ) {
            AturanJawaban::gagal('Hubungan peserta, kegiatan, kelas, atau periode tidak sesuai. Hubungi akademik.');
        }
        return compact('user', 'mahasiswa', 'riwayat', 'periode', 'rombel', 'kelas', 'registrasi', 'krs', 'detail', 'kegiatan', 'pertemuan');
    }
    private function baris(string $tabel, int $id): object
    {
        // Nama tabel hanya berasal dari konstanta di kunci(), bukan input HTTP.
        return DB::table($tabel)->where('id', $id)->lockForUpdate()->firstOrFail();
    }
    private function pengumpulan(int $id, int $userId, array $c): Pengumpulan
    {
        return Pengumpulan::query()->whereKey($id)->where('pemilik_id', $userId)->where('kegiatan_id', $c['kegiatan']->id)
            ->where('detail_krs_id', $c['detail']->id)->lockForUpdate()->firstOrFail();
    }
    private function bolehMenulis(array $c): CarbonImmutable
    {
        $waktu = CarbonImmutable::now('UTC');
        if (
            $c['detail']->status !== 'aktif' || $c['krs']->status !== 'disahkan' || $c['registrasi']->status !== 'aktif'
            || $c['riwayat']->status !== 'aktif' || $c['kelas']->status !== 'aktif' || $c['periode']->status !== 'aktif'
            || ($c['pertemuan'] && $c['pertemuan']->status === 'batal') || $c['kegiatan']->metode !== 'pengumpulan_berkas'
            || ! $c['kegiatan']->jendelaTerbuka($waktu)
        ) {
            AturanJawaban::gagal('Pengumpulan belum dibuka/sudah ditutup, tenggat lewat, atau keikutsertaan akademik tidak aktif. Draf tidak dianggap terkumpul.');
        }
        return $waktu;
    }
    private function versi(Pengumpulan $p, array $data): void
    {
        if (! is_string($data['versi_form'] ?? null) || ! hash_equals($p->versiForm(), $data['versi_form']) || $p->revisi >= 4294967295) {
            AturanJawaban::gagal('Draf berubah di halaman lain. Muat ulang dan periksa jawaban sebelum melanjutkan.');
        }
    }
    private function lampiran(Pengumpulan $p, Kegiatan $k, array $ids): void
    {
        if (count($ids) > $k->maks_berkas) {
            AturanJawaban::gagal('Jumlah berkas melebihi batas kegiatan.');
        }
        $lama = $p->semuaLampiran()->orderBy('berkas_id')->lockForUpdate()->get()->keyBy('berkas_id');
        $semua = array_values(array_unique([...$ids, ...$lama->keys()->map(fn($id) => (int) $id)->all()]));
        sort($semua, SORT_NUMERIC);
        $files = Berkas::query()->whereIn('id', $semua)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        foreach ($ids as $id) {
            $b = $files->get($id);
            if (! $b) {
                AturanJawaban::gagal('Salah satu berkas tidak tersedia.');
            }
            AturanJawaban::berkas($b, $k, $p->pemilik_id);
            $row = $lama->get($id);
            if ($row && ! $row->cocok($b)) {
                AturanJawaban::gagal('Snapshot berkas berubah; hubungi pengelola.');
            }
            if (! $row) {
                $row = new PengumpulanBerkas();
                $row->pengumpulan_id = $p->id;
                $row->berkas_id = $id;
                foreach (PengumpulanBerkas::SNAPSHOT as $field) {
                    $row->{$field} = $b->{$field};
                }
                $row->aktif = true;
                $row->dilepas_at = null;
                $row->save();
            } elseif (! $row->aktif) {
                $row->aktif = true;
                $row->dilepas_at = null;
                $row->save();
            }
        }
        foreach ($lama as $row) {
            if ($row->aktif && ! in_array($row->berkas_id, $ids, true)) {
                $row->aktif = false;
                $row->dilepas_at = now('UTC');
                $row->save();
            }
        }
    }
    private function lampiranSiap(Pengumpulan $p, Kegiatan $k): void
    {
        $rows = $p->lampiran()->orderBy('berkas_id')->lockForUpdate()->get();
        if ($rows->count() > $k->maks_berkas) {
            AturanJawaban::gagal('Lampiran melebihi batas kegiatan.');
        }
        $files = Berkas::query()->whereIn('id', $rows->pluck('berkas_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        foreach ($rows as $row) {
            $b = $files->get($row->berkas_id);
            if (! $b || ! $row->cocok($b)) {
                AturanJawaban::gagal('Berkas hilang atau snapshot berubah. Kiriman belum disimpan.');
            }
            AturanJawaban::berkas($b, $k, $p->pemilik_id);
        }
    }
    private function audit(Pengumpulan $p, int $userId, string $aksi, ?array $sebelum): void
    {
        $a = new AuditLog();
        $a->pelaku_id = $userId;
        $a->entitas = 'pengumpulan';
        $a->entitas_id = $p->id;
        $a->versi_entitas = $p->revisi;
        $a->aksi = $aksi;
        $a->sebelum = $sebelum;
        $a->sesudah = $p->ringkasanAudit();
        $a->alasan = null;
        $a->waktu = now('UTC');
        $a->save();
    }
}
