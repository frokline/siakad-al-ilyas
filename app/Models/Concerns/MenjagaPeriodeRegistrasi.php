<?php

namespace App\Models\Concerns;

use App\Models\PeriodeAkademik;
use App\Models\RegistrasiSemester;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use LogicException;

trait MenjagaPeriodeRegistrasi
{
    protected static function bootMenjagaPeriodeRegistrasi(): void
    {
        static::updating(function (self $riwayat): void {
            if (
                ! $riwayat->isDirty(['status', 'periode_akhir_id'])
                || $riwayat->periode_akhir_id === null
            ) {
                return;
            }

            if ($riwayat->getConnection()->transactionLevel() < 1) {
                throw new LogicException('Penutupan riwayat studi harus di dalam transaksi.');
            }

            $akhir = PeriodeAkademik::query()
                ->whereKey($riwayat->periode_akhir_id)
                ->lockForUpdate()
                ->firstOrFail();

            $periodeIds = $riwayat->registrasiSemester()
                ->where('status', '<>', RegistrasiSemester::BATAL)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id', 'periode_akademik_id'])
                ->pluck('periode_akademik_id');

            if ($periodeIds->isEmpty()) {
                return;
            }

            $melewatiAkhir = PeriodeAkademik::query()
                ->whereKey($periodeIds->all())
                ->where('mulai', '>', $akhir->mulai->toDateString())
                ->orderBy('id')
                ->lockForUpdate()
                ->first(['id']);

            if ($melewatiAkhir !== null) {
                throw ValidationException::withMessages([
                    'periode_akhir_id' => 'Masih ada registrasi setelah periode akhir. Batalkan registrasi terkait atau pilih periode akhir yang sesuai.',
                ]);
            }
        });
    }

    public function registrasiSemester(): HasMany
    {
        return $this->hasMany(RegistrasiSemester::class, 'riwayat_studi_id');
    }
}
