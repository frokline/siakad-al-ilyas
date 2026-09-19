<?php

namespace App\Models\Concerns;

use App\Models\RegistrasiSemester;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use LogicException;

trait MenjagaKapasitasRegistrasi
{
    protected static function bootMenjagaKapasitasRegistrasi(): void
    {
        static::updating(function (self $rombel): void {
            if (! $rombel->isDirty('kapasitas') || $rombel->kapasitas === null) {
                return;
            }

            if ($rombel->kapasitas < $rombel->hitungKursiTerkunci()) {
                throw ValidationException::withMessages([
                    'kapasitas' => 'Kapasitas tidak boleh lebih kecil dari jumlah registrasi terdaftar dan aktif.',
                ]);
            }
        });
    }

    public function registrasiSemester(): HasMany
    {
        return $this->hasMany(RegistrasiSemester::class, 'rombel_id');
    }

    public function hitungKursiTerkunci(?int $kecualiRegistrasiId = null): int
    {
        if ($this->getConnection()->transactionLevel() < 1) {
            throw new LogicException('Pemeriksaan kapasitas harus di dalam transaksi.');
        }

        // Semua alokasi kursi dan perubahan kapasitas mengunci parent yang sama.
        static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

        return $this->registrasiSemester()
            ->menempatiKursi()
            ->when($kecualiRegistrasiId !== null, function ($query) use ($kecualiRegistrasiId): void {
                $query->where('id', '<>', $kecualiRegistrasiId);
            })
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id'])
            ->count();
    }
}
