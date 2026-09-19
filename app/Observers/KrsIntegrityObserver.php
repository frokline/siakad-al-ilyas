<?php

namespace App\Observers;

use App\Models\DetailKrs;
use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\PeriodeAkademik;
use App\Models\RegistrasiSemester;
use App\Models\RiwayatStudi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KrsIntegrityObserver
{
    public function updating(Model $model): void
    {
        if (! $model->isDirty('status')) {
            return;
        }

        $relevan = match (true) {
            $model instanceof RegistrasiSemester => $model->status !== RegistrasiSemester::AKTIF,
            $model instanceof RiwayatStudi => $model->status !== RiwayatStudi::AKTIF,
            $model instanceof PeriodeAkademik => $model->status === 'arsip',
            $model instanceof KelasKuliah => in_array($model->status, [KelasKuliah::SELESAI, KelasKuliah::ARSIP], true),
            default => false,
        };

        if (! $relevan) {
            return;
        }

        if (DB::transactionLevel() < 1) {
            $this->gagal('Perubahan status harus menggunakan transaksi modul terkait.');
        }

        $terkini = $model->newModelQuery()->whereKey($model->getKey())->lockForUpdate()->firstOrFail();
        if ($terkini->getRawOriginal('status') !== $model->getRawOriginal('status')) {
            $this->gagal('Status sudah berubah. Muat ulang halaman sebelum melanjutkan.');
        }

        if ($model instanceof RegistrasiSemester) {
            $ada = Krs::query()->where('registrasi_semester_id', $model->id)
                ->where('status', '<>', Krs::DIBATALKAN)
                ->lockForUpdate()->first(['id']);

            if ($ada) {
                $this->gagal('Batalkan KRS registrasi ini terlebih dahulu sebelum mengubah status registrasi.');
            }
            return;
        }

        if ($model instanceof RiwayatStudi || $model instanceof PeriodeAkademik) {
            $kolom = $model instanceof RiwayatStudi ? 'riwayat_studi_id' : 'periode_akademik_id';
            $tertunda = Krs::query()
                ->join('registrasi_semester as rs', 'rs.id', '=', 'krs.registrasi_semester_id')
                ->where('rs.' . $kolom, $model->id)
                ->whereIn('krs.status', [Krs::DRAF, Krs::DIAJUKAN])
                ->orderBy('krs.id')->lockForUpdate()->first(['krs.id']);

            if ($tertunda) {
                $this->gagal('Masih ada KRS draf/diajukan. Selesaikan pengesahan atau pembatalannya dahulu.');
            }

            if ($model instanceof RiwayatStudi) {
                $belumTuntas = DetailKrs::query()
                    ->join('krs as k', 'k.id', '=', 'detail_krs.krs_id')
                    ->join('registrasi_semester as rs', 'rs.id', '=', 'k.registrasi_semester_id')
                    ->join('kelas_kuliah as kk', 'kk.id', '=', 'detail_krs.kelas_kuliah_id')
                    ->where('rs.riwayat_studi_id', $model->id)
                    ->where('k.status', Krs::DISAHKAN)
                    ->where('detail_krs.status', DetailKrs::AKTIF)
                    ->whereIn('kk.status', KelasKuliah::BELUM_TUNTAS)
                    ->orderBy('detail_krs.id')->lockForUpdate()->first(['detail_krs.id']);

                if ($belumTuntas) {
                    $this->gagal('Mahasiswa masih mengikuti kelas berjalan. Selesaikan kelas atau batalkan KRS terkait dahulu.');
                }
            }
            return;
        }

        if ($model instanceof KelasKuliah) {
            $statusPenghalang = [Krs::DRAF, Krs::DIAJUKAN];
            if ($model->status === KelasKuliah::ARSIP && $model->diaktifkan_at === null) {
                $statusPenghalang[] = Krs::DISAHKAN;
            }

            $terkait = DetailKrs::query()
                ->join('krs as k', 'k.id', '=', 'detail_krs.krs_id')
                ->where('detail_krs.kelas_kuliah_id', $model->id)
                ->whereIn('k.status', $statusPenghalang)
                ->orderBy('detail_krs.id')->lockForUpdate()->first(['detail_krs.id']);

            if ($terkait) {
                $this->gagal('Kelas masih terkait KRS yang belum tuntas diproses. Selesaikan KRS sebelum menutup kelas.');
            }
        }
    }

    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['status' => $pesan]);
    }
}
