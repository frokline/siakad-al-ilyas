<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;
use \App\Models\Concerns\MenjagaKapasitasRegistrasi;

class Rombel extends Model
{
    use HasFactory;
    use MenjagaKapasitasRegistrasi;

    public const STATUS_PERIODE_TERBUKA = [
        'persiapan',
        'aktif',
    ];

    protected $table = 'rombel';

    protected $fillable = [
        'kode',
        'kapasitas',
    ];

    protected function casts(): array
    {
        return [
            'periode_akademik_id' => 'integer',
            'paket_semester_id' => 'integer',
            'kapasitas' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Rombel $rombel): void {
            if (
                $rombel->exists
                && $rombel->isDirty([
                    'periode_akademik_id',
                    'paket_semester_id',
                ])
            ) {
                throw ValidationException::withMessages([
                    'rombel' =>
                    'Periode dan paket rombel tidak dapat diubah. '
                        . 'Buat rombel baru untuk penempatan yang berbeda.',
                ]);
            }

            if (
                $rombel->kapasitas !== null
                && (
                    $rombel->kapasitas < 1
                    || $rombel->kapasitas > 32767
                )
            ) {
                throw ValidationException::withMessages([
                    'kapasitas' =>
                    'Kapasitas harus antara 1 dan 32767, atau dikosongkan.',
                ]);
            }

            $periode = PeriodeAkademik::query()
                ->whereKey((int) $rombel->periode_akademik_id)
                ->lockForUpdate()
                ->first();

            if (
                $periode === null
                || ! in_array(
                    $periode->status,
                    self::STATUS_PERIODE_TERBUKA,
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'periode_akademik_id' =>
                    'Rombel hanya dapat dikelola pada periode '
                        . 'persiapan atau aktif.',
                ]);
            }
        });

        static::creating(function (Rombel $rombel): void {
            $paket = PaketSemester::query()
                ->whereKey((int) $rombel->paket_semester_id)
                ->lockForUpdate()
                ->first();

            if ($paket === null || ! $paket->isDiterbitkan()) {
                throw ValidationException::withMessages([
                    'paket_semester_id' =>
                    'Rombel baru harus menggunakan paket yang diterbitkan.',
                ]);
            }

            $detail = $paket->details()
                ->lockForUpdate()
                ->first(['id']);

            if ($detail === null) {
                throw ValidationException::withMessages([
                    'paket_semester_id' =>
                    'Paket yang dipilih belum memiliki mata kuliah.',
                ]);
            }
        });

        static::deleting(function (): void {
            throw ValidationException::withMessages([
                'rombel' =>
                'Rombel tidak dapat dihapus karena merupakan catatan akademik.',
            ]);
        });
    }

    public function periodeAkademik(): BelongsTo
    {
        return $this->belongsTo(
            PeriodeAkademik::class,
            'periode_akademik_id'
        );
    }

    public function paketSemester(): BelongsTo
    {
        return $this->belongsTo(
            PaketSemester::class,
            'paket_semester_id'
        );
    }

    public function dapatDiubah(): bool
    {
        return in_array(
            $this->periodeAkademik->status,
            self::STATUS_PERIODE_TERBUKA,
            true
        );
    }

    public function kelasKuliah(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\KelasKuliah::class, 'rombel_id');
    }

    public function pengajarKelas(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\PengajarKelas::class,
            \App\Models\KelasKuliah::class,
            'rombel_id',
            'kelas_kuliah_id',
            'id',
            'id'
        );
    }

    public function jadwalKuliah(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\JadwalKuliah::class,
            \App\Models\KelasKuliah::class,
            'rombel_id',
            'kelas_kuliah_id',
            'id',
            'id'
        );
    }

    public function pertemuan(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Pertemuan::class,
            \App\Models\KelasKuliah::class,
            'rombel_id',
            'kelas_kuliah_id',
            'id',
            'id'
        );
    }
}
