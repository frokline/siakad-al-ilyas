<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use \App\Models\Concerns\MenjagaPeriodeRegistrasi;

class RiwayatStudi extends Model
{
    use HasFactory;

    public const AKTIF = 'aktif';
    public const SELESAI = 'selesai';
    public const PINDAH = 'pindah';
    public const KELUAR = 'keluar';

    public const STATUS = [
        self::AKTIF => 'Aktif',
        self::SELESAI => 'Selesai studi',
        self::PINDAH => 'Pindah',
        self::KELUAR => 'Keluar',
    ];

    public const IDENTITAS = [
        'mahasiswa_id',
        'kurikulum_id',
        'angkatan',
        'periode_mulai_id',
    ];

    protected $table = 'riwayat_studi';

    protected $fillable = [
        'angkatan',
    ];

    protected $hidden = [
        'aktif_guard',
    ];

    protected $attributes = [
        'status' => self::AKTIF,
    ];

    protected function casts(): array
    {
        return [
            'mahasiswa_id' => 'integer',
            'kurikulum_id' => 'integer',
            'angkatan' => 'integer',
            'periode_mulai_id' => 'integer',
            'periode_akhir_id' => 'integer',
            'dosen_pa_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RiwayatStudi $riwayat): void {
            if (! $riwayat->isAktif()) {
                throw ValidationException::withMessages([
                    'status' => 'Riwayat baru harus berstatus aktif.',
                ]);
            }
        });

        static::saving(function (RiwayatStudi $riwayat): void {
            if (! array_key_exists($riwayat->status, self::STATUS)) {
                throw ValidationException::withMessages([
                    'status' => 'Status riwayat studi tidak valid.',
                ]);
            }

            if ($riwayat->angkatan < 1900 || $riwayat->angkatan > 9999) {
                throw ValidationException::withMessages([
                    'angkatan' => 'Angkatan harus antara 1900 dan 9999.',
                ]);
            }

            if ($riwayat->isAktif() && $riwayat->periode_akhir_id !== null) {
                throw ValidationException::withMessages([
                    'periode_akhir_id' =>
                    'Riwayat aktif tidak boleh memiliki periode akhir.',
                ]);
            }

            if (! $riwayat->isAktif() && $riwayat->periode_akhir_id === null) {
                throw ValidationException::withMessages([
                    'periode_akhir_id' =>
                    'Periode akhir wajib diisi saat menutup riwayat.',
                ]);
            }

            if ($riwayat->isDirty('aktif_guard')) {
                throw ValidationException::withMessages([
                    'riwayat_studi' =>
                    'Kolom pembatas riwayat aktif tidak dapat diubah.',
                ]);
            }
        });

        static::updating(function (RiwayatStudi $riwayat): void {
            if ($riwayat->getRawOriginal('status') !== self::AKTIF) {
                throw ValidationException::withMessages([
                    'riwayat_studi' =>
                    'Riwayat yang sudah ditutup tidak dapat diubah.',
                ]);
            }

            if ($riwayat->isDirty(self::IDENTITAS)) {
                throw ValidationException::withMessages([
                    'riwayat_studi' =>
                    'Identitas studi tidak dapat diubah. '
                        . 'Perpindahan studi harus menggunakan riwayat baru.',
                ]);
            }
        });

        static::deleting(function (): void {
            throw ValidationException::withMessages([
                'riwayat_studi' =>
                'Riwayat studi tidak dapat dihapus. Gunakan penutupan riwayat.',
            ]);
        });
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function periodeMulai(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_mulai_id');
    }

    public function periodeAkhir(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_akhir_id');
    }

    public function dosenPa(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_pa_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('riwayat_studi.status', self::AKTIF);
    }

    public function isAktif(): bool
    {
        return $this->status === self::AKTIF;
    }

public function registrasiSemester(): HasMany
{
    return $this->hasMany(
        RegistrasiSemester::class,
        'riwayat_studi_id'
    );
}

    public function krs(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Krs::class,
            \App\Models\RegistrasiSemester::class,
            'riwayat_studi_id',
            'registrasi_semester_id',
            'id',
            'id'
        );
    }
}
