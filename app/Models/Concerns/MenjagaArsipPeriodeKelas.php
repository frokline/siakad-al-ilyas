<?php

namespace App\Models\Concerns;

use App\Models\KelasKuliah;
use App\Models\Rombel;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Validation\ValidationException;
use LogicException;

trait MenjagaArsipPeriodeKelas
{
    protected static function bootMenjagaArsipPeriodeKelas(): void
    {
        static::updating(function (self $periode): void {
            if (! $periode->isDirty('status') || $periode->status !== 'arsip') {
                return;
            }

            if ($periode->getConnection()->transactionLevel() < 1) {
                throw new LogicException('Pengarsipan periode harus di dalam transaksi.');
            }

            static::query()->whereKey($periode->getKey())
                ->lockForUpdate()->firstOrFail();

            $belumTuntas = $periode->kelasKuliah()
                ->whereIn('kelas_kuliah.status', KelasKuliah::BELUM_TUNTAS)
                ->orderBy('kelas_kuliah.id')
                ->lockForUpdate()
                ->first(['kelas_kuliah.id']);

            if ($belumTuntas !== null) {
                throw ValidationException::withMessages([
                    'status' => 'Masih ada kelas persiapan atau aktif. Selesaikan kelas aktif dan arsipkan persiapan yang dibatalkan sebelum mengarsipkan periode.',
                ]);
            }
        });
    }

    public function kelasKuliah(): HasManyThrough
    {
        return $this->hasManyThrough(
            KelasKuliah::class,
            Rombel::class,
            'periode_akademik_id',
            'rombel_id',
            'id',
            'id'
        );
    }
}
