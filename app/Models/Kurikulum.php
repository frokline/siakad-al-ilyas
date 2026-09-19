<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Kurikulum extends Model
{
    use HasFactory;

    public const DRAF = 'draf';
    public const AKTIF = 'aktif';
    public const ARSIP = 'arsip';

    public const STATUS = [
        self::DRAF => 'Draf',
        self::AKTIF => 'Aktif',
        self::ARSIP => 'Arsip',
    ];

    public const IDENTITAS = [
        'program_studi_id',
        'kode',
        'nama',
        'tahun_berlaku',
    ];

    protected $table = 'kurikulum';

    protected $fillable = [
        'kode',
        'nama',
        'tahun_berlaku',
    ];

    protected $attributes = [
        'status' => self::DRAF,
    ];

    protected function casts(): array
    {
        return [
            'program_studi_id' => 'integer',
            'tahun_berlaku' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Kurikulum $kurikulum): void {
            if ($kurikulum->status !== self::DRAF) {
                throw ValidationException::withMessages([
                    'status' => 'Kurikulum baru harus berstatus draf.',
                ]);
            }
        });

        static::updating(function (Kurikulum $kurikulum): void {
            $statusAwal = $kurikulum->getRawOriginal('status');

            $prodiBerubah = $kurikulum->isDirty('program_studi_id');

            $akanDiaktifkan = $kurikulum->isDirty('status')
                && $kurikulum->status === self::AKTIF;

            if ($prodiBerubah || $akanDiaktifkan) {
                $detailPertama = $kurikulum->details()
                    ->lockForUpdate()
                    ->first(['id']);

                if ($prodiBerubah && $detailPertama !== null) {
                    throw ValidationException::withMessages([
                        'program_studi_id' => 'Program studi tidak dapat diganti setelah kurikulum memiliki mata kuliah.',
                    ]);
                }

                if ($akanDiaktifkan && $detailPertama === null) {
                    throw ValidationException::withMessages([
                        'status' => 'Tambahkan minimal satu mata kuliah sebelum mengaktifkan kurikulum.',
                    ]);
                }
            }

            $statusDiizinkan = $statusAwal === self::DRAF
                ? array_keys(self::STATUS)
                : [self::AKTIF, self::ARSIP];

            if (! in_array($kurikulum->status, $statusDiizinkan, true)) {
                throw ValidationException::withMessages([
                    'status' => 'Kurikulum aktif atau arsip tidak dapat kembali menjadi draf.',
                ]);
            }

            if (
                $statusAwal !== self::DRAF
                && $kurikulum->isDirty(self::IDENTITAS)
            ) {
                throw ValidationException::withMessages([
                    'kurikulum' => 'Identitas kurikulum telah terkunci. Buat kurikulum versi baru.',
                ]);
            }
        });

        static::deleting(function (Kurikulum $kurikulum): void {
            throw ValidationException::withMessages([
                'kurikulum' => 'Gunakan status arsip untuk menghentikan penggunaan kurikulum.',
            ]);
        });
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('kurikulum.status', self::AKTIF);
    }

    public function identitasDapatDiubah(): bool
    {
        return ! $this->exists || $this->status === self::DRAF;
    }

    public function details(): HasMany
    {
        return $this->hasMany(KurikulumMataKuliah::class, 'kurikulum_id');
    }

    public function pilihanStatus(): array
    {
        return $this->identitasDapatDiubah()
            ? self::STATUS
            : [
                self::AKTIF => self::STATUS[self::AKTIF],
                self::ARSIP => self::STATUS[self::ARSIP],
            ];
    }

    public function riwayatStudi(): HasMany
    {
        return $this->hasMany(RiwayatStudi::class, 'kurikulum_id');
    }

    public function paketSemester(): HasMany
    {
        return $this->hasMany(PaketSemester::class, 'kurikulum_id');
    }
}
