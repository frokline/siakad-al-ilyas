<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Dosen extends Model
{
    use HasFactory;

    public const AKTIF = 'aktif';
    public const NONAKTIF = 'nonaktif';

    public const STATUS = [
        self::AKTIF => 'Aktif',
        self::NONAKTIF => 'Nonaktif',
    ];

    protected $table = 'dosen';

    protected $fillable = [
        'kode_dosen',
        'nidn',
        'gelar',
    ];

    protected $attributes = [
        'status' => self::AKTIF,
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Dosen $dosen): void {
            if ($dosen->isDirty('user_id')) {
                throw ValidationException::withMessages([
                    'user_id' => 'Akun yang terhubung dengan dosen tidak dapat diganti melalui formulir ini.',
                ]);
            }
        });

        static::deleting(function (Dosen $dosen): void {
            throw ValidationException::withMessages([
                'dosen' => 'Gunakan status nonaktif untuk menghentikan penggunaan data dosen.',
            ]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('dosen.status', self::AKTIF);
    }

    public function isAktif(): bool
    {
        return $this->status === self::AKTIF;
    }

    public function mahasiswaBimbingan(): HasMany
    {
        return $this->hasMany(RiwayatStudi::class, 'dosen_pa_id');
    }

    public function pengajarKelas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PengajarKelas::class, 'dosen_id');
    }

    public function jadwalMengajar(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\JadwalKuliah::class,
            \App\Models\PengajarKelas::class,
            'dosen_id',
            'kelas_kuliah_id',
            'id',
            'kelas_kuliah_id'
        )->where('pengajar_kelas.aktif', true)->where('jadwal_kuliah.aktif', true);
    }

    public function pertemuanTanggungJawab(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Pertemuan::class,
            \App\Models\PengajarKelas::class,
            'dosen_id',
            'pengajar_kelas_id',
            'id',
            'id'
        );
    }
}
