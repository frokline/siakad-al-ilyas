<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\PengajarKelas;
use App\Models\PeriodeAkademik;
use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\PresensiPertemuan;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\User;
use App\Support\TokenPresensi;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class KelolaPresensi
{
    public function execute(string $aksi, int $pelakuId, Pertemuan $bound, array $data, ?int $barisId = null): void
    {
        abort_unless(in_array($aksi, ['siapkan', 'catat', 'koreksi', 'tutup'], true), 404);
        $petunjuk = $bound->loadMissing('kelasKuliah.rombel');

        try {
            DB::transaction(function () use ($aksi, $pelakuId, $bound, $petunjuk, $data, $barisId): void {
                $pelaku = User::query()->whereKey($pelakuId)->lockForUpdate()->firstOrFail();
                $roles = $pelaku->roles()->whereIn('roles.kode', [Role::ADMIN_AKADEMIK, Role::DOSEN])
                    ->orderBy('roles.id')->lockForUpdate()->get(['roles.id', 'roles.kode']);
                $admin = $roles->contains('kode', Role::ADMIN_AKADEMIK);
                abort_unless($pelaku->isAktif() && ($admin || $roles->contains('kode', Role::DOSEN)), 403);

                $periode = PeriodeAkademik::query()->whereKey($petunjuk->kelasKuliah->rombel->periode_akademik_id)
                    ->lockForUpdate()->firstOrFail();
                $rombel = Rombel::query()->whereKey($petunjuk->kelasKuliah->rombel_id)->lockForUpdate()->firstOrFail();
                $kelas = KelasKuliah::query()->whereKey($bound->kelas_kuliah_id)->lockForUpdate()->firstOrFail();
                $tim = PengajarKelas::query()->where('kelas_kuliah_id', $kelas->id)->orderBy('id')->lockForUpdate()->get();
                $sesi = Pertemuan::query()->whereKey($bound->id)->where('kelas_kuliah_id', $kelas->id)
                    ->lockForUpdate()->firstOrFail();

                if (! $admin) {
                    $dosen = Dosen::query()->where('user_id', $pelaku->id)->lockForUpdate()->first();
                    abort_unless($dosen && $dosen->status === Dosen::AKTIF
                        && $tim->contains(fn(PengajarKelas $p): bool => $p->aktif && $p->dosen_id === $dosen->id), 403);
                }

                $daftar = PresensiPertemuan::query()->where('pertemuan_id', $sesi->id)->lockForUpdate()->first();
                $aktif = $kelas->status === KelasKuliah::AKTIF && $periode->status === 'aktif'
                    && $sesi->status === Pertemuan::BERLANGSUNG;
                if ($aksi === 'siapkan' && $daftar !== null) {
                    return; // Pengiriman ulang tidak membuat ulang daftar/snapshot.
                }
                if (! ($aksi === 'koreksi' && $admin) && ! $aktif) {
                    $this->gagal('presensi', 'Pencatatan memerlukan pertemuan berlangsung, kelas aktif, dan periode aktif.');
                }
                $waktu = CarbonImmutable::now('UTC')->startOfSecond();

                if ($aksi === 'siapkan') {
                    $this->konfirmasi($data);
                    TokenPresensi::periksa(TokenPresensi::pertemuan($sesi), $data['versi_pertemuan'] ?? null, 'versi_pertemuan');
                    // Lock kelas di atas juga digunakan KelolaKrs sebelum mengubah keanggotaan kelas.
                    // Lock join menjaga snapshot identitas, KRS, registrasi, dan riwayat tetap konsisten.
                    $peserta = DB::table('detail_krs as d')
                        ->join('krs as k', 'k.id', '=', 'd.krs_id')
                        ->join('registrasi_semester as r', 'r.id', '=', 'k.registrasi_semester_id')
                        ->join('riwayat_studi as h', 'h.id', '=', 'r.riwayat_studi_id')
                        ->join('mahasiswa as m', 'm.id', '=', 'h.mahasiswa_id')
                        ->join('users as u', 'u.id', '=', 'm.user_id')
                        ->where('d.kelas_kuliah_id', $kelas->id)->where('d.status', 'aktif')
                        ->where('k.status', 'disahkan')->where('r.status', 'aktif')->where('h.status', 'aktif')
                        ->where('r.rombel_id', $rombel->id)->where('r.periode_akademik_id', $periode->id)
                        ->orderBy('d.id')->lockForUpdate()->get([
                            'd.id as detail_krs_id',
                            'm.id as mahasiswa_id',
                            'm.nim',
                            'u.nama',
                            'k.id as krs_id',
                            'k.versi as krs_versi',
                            'r.id as registrasi_semester_id',
                            'h.id as riwayat_studi_id',
                        ]);
                    if ($peserta->isEmpty()) {
                        $this->gagal('presensi', 'Belum ada peserta dengan KRS disahkan dan registrasi/riwayat aktif.');
                    }
                    if ($peserta->pluck('mahasiswa_id')->unique()->count() !== $peserta->count()) {
                        $this->gagal('presensi', 'Ada mahasiswa ganda dalam peserta kelas. Perbaiki KRS sebelum membuka presensi.');
                    }
                    $daftar = new PresensiPertemuan();
                    $daftar->pertemuan_id = $sesi->id;
                    $daftar->kelas_kuliah_id = $kelas->id;
                    $daftar->status = PresensiPertemuan::TERBUKA;
                    $daftar->jumlah_peserta = $peserta->count();
                    $daftar->dibuka_oleh = $pelaku->id;
                    $daftar->dibuka_at = $waktu;
                    $daftar->revisi = 1;
                    $daftar->save();
                    $this->audit($daftar, $pelaku, 'presensi_pertemuan', 'siapkan', null, 'Membekukan daftar peserta dari KRS disahkan.', $waktu);
                    foreach ($peserta as $orang) {
                        $baris = new Presensi();
                        $baris->presensi_pertemuan_id = $daftar->id;
                        $baris->kelas_kuliah_id = $kelas->id;
                        $baris->detail_krs_id = (int) $orang->detail_krs_id;
                        $baris->mahasiswa_id = (int) $orang->mahasiswa_id;
                        $baris->peserta_snapshot = (array) $orang;
                        $baris->status = Presensi::BELUM;
                        $baris->revisi = 1;
                        $baris->save();
                        $this->audit($baris, $pelaku, 'presensi', 'siapkan', null, null, $waktu);
                    }
                    return;
                }

                if ($daftar === null) {
                    $this->gagal('presensi', 'Siapkan daftar peserta terlebih dahulu.');
                }
                if ($aksi === 'tutup') {
                    $this->konfirmasi($data);
                    if ($daftar->status === PresensiPertemuan::DITUTUP) {
                        return;
                    }
                    $baris = $daftar->presensi()->orderBy('id')->lockForUpdate()->get();
                    TokenPresensi::periksa($daftar->versiPenutupan($baris), $data['versi_daftar'] ?? null, 'versi_daftar');
                    if ($baris->count() !== $daftar->jumlah_peserta || $baris->contains('status', Presensi::BELUM)) {
                        $this->gagal('presensi', 'Seluruh peserta harus dicatat sebelum presensi ditutup.');
                    }
                    $sebelum = $daftar->ringkasanAudit();
                    $daftar->status = PresensiPertemuan::DITUTUP;
                    $daftar->ditutup_oleh = $pelaku->id;
                    $daftar->ditutup_at = $waktu;
                    $daftar->revisi++;
                    $daftar->save();
                    $this->audit($daftar, $pelaku, 'presensi_pertemuan', 'tutup', $sebelum, 'Seluruh peserta telah diperiksa.', $waktu);
                    return;
                }

                $baris = Presensi::query()->whereKey($barisId)->where('presensi_pertemuan_id', $daftar->id)
                    ->where('kelas_kuliah_id', $kelas->id)->lockForUpdate()->firstOrFail();
                if (! ($aksi === 'koreksi' && $admin) && $daftar->status !== PresensiPertemuan::TERBUKA) {
                    $this->gagal('presensi', 'Presensi sudah ditutup. Koreksi hanya melalui admin akademik.');
                }
                if (! in_array($sesi->status, [Pertemuan::BERLANGSUNG, Pertemuan::SELESAI], true)) {
                    $this->gagal('presensi', 'Status pertemuan tidak sesuai untuk pencatatan atau koreksi.');
                }
                $status = $data['status'] ?? null;
                $catatan = $data['catatan'] ?? null;
                if (
                    ! is_string($status) || ! isset(Presensi::PILIHAN[$status])
                    || ($catatan !== null && (! is_string($catatan) || mb_strlen($catatan) > 1000))
                ) {
                    $this->gagal('status', 'Status atau catatan tidak valid.');
                }
                $catatan = is_string($catatan) && trim($catatan) !== '' ? trim($catatan) : null;
                $alasan = null;
                if ($aksi === 'koreksi') {
                    $alasan = $data['alasan'] ?? null;
                    if (! is_string($alasan) || mb_strlen(trim($alasan)) < 10 || mb_strlen($alasan) > 2000) {
                        $this->gagal('alasan', 'Alasan koreksi wajib 10–2.000 karakter.');
                    }
                    if ($baris->status === Presensi::BELUM) {
                        $this->gagal('presensi', 'Gunakan tombol pencatatan pertama untuk peserta ini.');
                    }
                }
                // Efek idempoten: kiriman identik tidak menaikkan revisi atau membuat audit ganda.
                if ($baris->status === $status && $baris->catatan === $catatan) {
                    return;
                }
                TokenPresensi::periksa($baris->versiForm(), $data['versi_presensi'] ?? null, 'versi_presensi');
                if ($aksi === 'catat' && $baris->status !== Presensi::BELUM) {
                    $this->gagal('presensi', 'Peserta sudah dicatat. Gunakan halaman koreksi dengan alasan.');
                }
                if ($baris->revisi >= 4294967295) {
                    $this->gagal('presensi', 'Batas revisi tercapai. Hubungi pengelola sistem.');
                }
                $sebelum = $baris->ringkasanAudit();
                $baris->status = $status;
                $baris->catatan = $catatan;
                $baris->dicatat_oleh = $pelaku->id;
                $baris->dicatat_at = $waktu;
                $baris->revisi++;
                $baris->save();
                $this->audit($baris, $pelaku, 'presensi', $aksi, $sebelum, $alasan === null ? null : trim($alasan), $waktu);
            }, 3);
        } catch (QueryException $exception) {
            if (in_array((int) ($exception->errorInfo[1] ?? 0), [1062, 1205, 1213], true)) {
                $this->gagal('presensi', 'Data berubah bersamaan. Muat ulang halaman dan periksa hasil sebelum mencoba lagi.');
            }
            throw $exception;
        }
    }

    private function audit(Model $model, User $pelaku, string $entitas, string $aksi, ?array $sebelum, ?string $alasan, CarbonImmutable $waktu): void
    {
        $audit = new AuditLog();
        $audit->pelaku_id = $pelaku->id;
        $audit->entitas = $entitas;
        $audit->entitas_id = $model->id;
        $audit->versi_entitas = $model->revisi;
        $audit->aksi = $aksi;
        $audit->sebelum = $sebelum;
        $audit->sesudah = $model->ringkasanAudit();
        $audit->alasan = $alasan;
        $audit->waktu = $waktu;
        $audit->save();
    }

    private function konfirmasi(array $data): void
    {
        if (! in_array($data['konfirmasi'] ?? null, [true, 1, '1', 'on', 'yes', 'true'], true)) {
            $this->gagal('konfirmasi', 'Centang konfirmasi terlebih dahulu.');
        }
    }
    private function gagal(string $field, string $pesan): never
    {
        throw ValidationException::withMessages([$field => $pesan]);
    }
}
