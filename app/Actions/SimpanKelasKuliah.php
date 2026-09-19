<?php

namespace App\Actions;

use App\Models\DetailPaket;
use App\Models\KelasKuliah;
use App\Models\Kurikulum;
use App\Models\KurikulumMataKuliah;
use App\Models\MataKuliah;
use App\Models\PaketSemester;
use App\Models\PeriodeAkademik;
use App\Models\ProgramStudi;
use App\Models\Rombel;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SimpanKelasKuliah
{
    public function execute(
        int $actorId,
        array $data,
        ?KelasKuliah $bound = null
    ): KelasKuliah {
        // FK parent ini immutable pada modul sebelumnya.
        $petunjukRombel = Rombel::query()->findOrFail(
            $bound?->rombel_id ?? (int) $data['rombel_id']
        );
        $petunjukPaket = PaketSemester::query()->findOrFail($petunjukRombel->paket_semester_id);

        try {
            return DB::transaction(function () use (
                $actorId,
                $data,
                $bound,
                $petunjukRombel,
                $petunjukPaket
            ): KelasKuliah {
                $actor = User::query()->whereKey($actorId)->lockForUpdate()->firstOrFail();
                Gate::forUser($actor)->authorize('kelola-kelas-kuliah');

                $kurikulum = Kurikulum::query()
                    ->whereKey($petunjukPaket->kurikulum_id)
                    ->lockForUpdate()->firstOrFail();
                $prodi = ProgramStudi::query()
                    ->whereKey($kurikulum->program_studi_id)
                    ->lockForUpdate()->firstOrFail();
                $paket = PaketSemester::query()
                    ->whereKey($petunjukPaket->id)
                    ->lockForUpdate()->firstOrFail();
                $periode = PeriodeAkademik::query()
                    ->whereKey($petunjukRombel->periode_akademik_id)
                    ->lockForUpdate()->firstOrFail();
                $rombel = Rombel::query()
                    ->whereKey($petunjukRombel->id)
                    ->lockForUpdate()->firstOrFail();

                if (! in_array($periode->status, Rombel::STATUS_PERIODE_TERBUKA, true)) {
                    $this->gagal('kelas', 'Periode sudah diarsipkan. Data kelas hanya dapat dibaca.');
                }

                if (
                    $rombel->periode_akademik_id !== $periode->id
                    || $rombel->paket_semester_id !== $paket->id
                    || $paket->kurikulum_id !== $kurikulum->id
                ) {
                    $this->gagal('rombel_id', 'Hubungan rombel, periode, atau paket tidak sesuai.');
                }

                $detailId = $bound?->detail_paket_id ?? (int) $data['detail_paket_id'];
                $detail = DetailPaket::query()
                    ->whereKey($detailId)
                    ->where('paket_semester_id', $paket->id)
                    ->lockForUpdate()->first();

                if ($detail === null) {
                    $this->gagal('detail_paket_id', 'Mata kuliah harus berasal dari paket rombel yang dipilih.');
                }

                $kmk = KurikulumMataKuliah::query()
                    ->whereKey($detail->kurikulum_mata_kuliah_id)
                    ->lockForUpdate()->firstOrFail();
                $mataKuliah = MataKuliah::query()
                    ->whereKey($kmk->mata_kuliah_id)
                    ->lockForUpdate()->firstOrFail();

                if (
                    $kmk->kurikulum_id !== $kurikulum->id
                    || $mataKuliah->program_studi_id !== $prodi->id
                ) {
                    $this->gagal('detail_paket_id', 'Kurikulum atau program studi mata kuliah tidak sesuai.');
                }

                // Semua penulis kelas mengunci rombel yang sama terlebih dahulu.
                $kelasRombel = KelasKuliah::query()
                    ->where('rombel_id', $rombel->id)
                    ->orderBy('id')->lockForUpdate()->get();

                if ($bound) {
                    $kelas = $kelasRombel->firstWhere('id', $bound->id);
                    abort_unless($kelas, 404);

                    if (! hash_equals($kelas->versiForm(), (string) ($data['versi'] ?? ''))) {
                        $this->gagal('versi', 'Data telah berubah. Muat formulir terbaru sebelum menyimpan.');
                    }

                    if ($kelas->status === KelasKuliah::ARSIP && $kelas->diaktifkan_at !== null) {
                        $this->gagal('kelas', 'Arsip kelas yang sudah berjalan tidak dapat diubah.');
                    }

                    $status = (string) $data['status'];
                    if (! array_key_exists($status, $kelas->pilihanStatus())) {
                        $this->gagal('status', 'Perubahan status tidak diizinkan.');
                    }

                    $kode = $kelas->kodeDapatDiubah() ? (string) $data['kode'] : $kelas->kode;
                } else {
                    $kelas = new KelasKuliah();
                    $kelas->rombel_id = $rombel->id;
                    $kelas->detail_paket_id = $detail->id;
                    $kelas->nama_mk_snapshot = trim($mataKuliah->nama);
                    $kelas->sks_snapshot = (string) $kmk->sks;
                    $status = KelasKuliah::PERSIAPAN;
                    $kode = (string) $data['kode'];
                }

                $mengaktifkan = $kelas->exists
                    && $kelas->status !== KelasKuliah::AKTIF
                    && $status === KelasKuliah::AKTIF;
                $memulihkan = $kelas->exists
                    && $kelas->status === KelasKuliah::ARSIP
                    && $status === KelasKuliah::PERSIAPAN;

                if (! $kelas->exists || $mengaktifkan || $memulihkan) {
                    if (! $prodi->aktif || $kurikulum->status !== 'aktif' || ! $mataKuliah->aktif) {
                        $this->gagal('kelas', 'Program studi, kurikulum, dan mata kuliah harus aktif.');
                    }

                    // Rombel lama tetap menggunakan versi paketnya, termasuk paket arsip.
                    if (! in_array($paket->status, [PaketSemester::DITERBITKAN, PaketSemester::ARSIP], true)) {
                        $this->gagal('rombel_id', 'Rombel harus menggunakan paket yang sudah pernah diterbitkan.');
                    }
                }

                if ($mengaktifkan && $periode->status !== 'aktif') {
                    $this->gagal('status', 'Aktifkan periode akademik terlebih dahulu sebelum mengaktifkan kelas.');
                }

                $detailGanda = $kelasRombel->first(
                    fn(KelasKuliah $lain): bool =>
                    $lain->id !== $kelas->id && $lain->detail_paket_id === $detail->id
                );
                if ($detailGanda !== null) {
                    $this->gagal('detail_paket_id', 'Kelas mata kuliah ini sudah ada, termasuk jika diarsipkan. Buka kelas tersebut.');
                }

                $kodeGanda = $kelasRombel->first(
                    fn(KelasKuliah $lain): bool =>
                    $lain->id !== $kelas->id && $lain->kode === $kode
                );
                if ($kodeGanda !== null) {
                    $this->gagal('kode', 'Kode kelas sudah digunakan dalam rombel ini.');
                }

                if ($kelas->exists && $kelas->revisi >= 4294967295) {
                    $this->gagal('versi', 'Batas revisi tercapai. Hubungi pengelola sistem.');
                }

                if ($kelas->exists && $kelas->status !== $status) {
                    $waktu = now('UTC');
                    switch ($status) {
                        case KelasKuliah::PERSIAPAN:
                            $kelas->diarsipkan_at = null;
                            break;
                        case KelasKuliah::AKTIF:
                            $kelas->diaktifkan_at = $waktu;
                            break;
                        case KelasKuliah::SELESAI:
                            $kelas->diselesaikan_at = $waktu;
                            break;
                        case KelasKuliah::ARSIP:
                            $kelas->diarsipkan_at = $waktu;
                            break;
                    }
                }

                $kelas->kode = $kode;
                $kelas->status = $status;
                $kelas->revisi = $kelas->exists ? $kelas->revisi + 1 : 1;
                $kelas->save();

                return $kelas;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            $this->gagal('kode', 'Kode kelas atau mata kuliah sudah digunakan dalam rombel ini. Muat ulang daftar kelas.');
        }
    }

    private function gagal(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
