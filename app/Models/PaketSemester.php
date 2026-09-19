<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class PaketSemester extends Model
{
    use HasFactory;

    public const DRAF = 'draf';
    public const DITERBITKAN = 'diterbitkan';
    public const ARSIP = 'arsip';

    public const STATUS = [
        self::DRAF => 'Draf',
        self::DITERBITKAN => 'Diterbitkan',
        self::ARSIP => 'Arsip',
    ];

    protected $table = 'paket_semester';

    protected $fillable = [
        'nama',
    ];

    protected $attributes = [
        'status' => self::DRAF,
    ];

    protected function casts(): array
    {
        return [
            'kurikulum_id' => 'integer',
            'semester_studi' => 'integer',
            'versi' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaketSemester $paket): void {
            if (! $paket->isDraf()) {
                throw ValidationException::withMessages([
                    'paket_semester' => 'Paket baru harus berstatus draf.',
                ]);
            }
        });

        static::saving(function (PaketSemester $paket): void {
            if (! array_key_exists($paket->status, self::STATUS)) {
                throw ValidationException::withMessages([
                    'paket_semester' => 'Status paket tidak valid.',
                ]);
            }

            foreach (['semester_studi', 'versi'] as $field) {
                if ($paket->{$field} < 1 || $paket->{$field} > 32767) {
                    throw ValidationException::withMessages([
                        $field => 'Nilai harus antara 1 dan 32767.',
                    ]);
                }
            }
        });

        static::updating(function (PaketSemester $paket): void {
            if ($paket->isDirty([
                'kurikulum_id',
                'semester_studi',
                'versi',
            ])) {
                throw ValidationException::withMessages([
                    'paket_semester' =>
                    'Kurikulum, semester studi, dan versi tidak dapat diubah.',
                ]);
            }

            $statusAwal = $paket->getRawOriginal('status');

            $diizinkan = match ($statusAwal) {
                self::DRAF => [
                    self::DRAF,
                    self::DITERBITKAN,
                    self::ARSIP,
                ],
                self::DITERBITKAN => [
                    self::DITERBITKAN,
                    self::ARSIP,
                ],
                self::ARSIP => [
                    self::ARSIP,
                ],
                default => [],
            };

            if (! in_array($paket->status, $diizinkan, true)) {
                throw ValidationException::withMessages([
                    'paket_semester' => 'Perubahan status paket tidak diizinkan.',
                ]);
            }

            if ($statusAwal !== self::DRAF && $paket->isDirty('nama')) {
                throw ValidationException::withMessages([
                    'nama' => 'Nama paket yang sudah diterbitkan atau diarsipkan '
                        . 'tidak dapat diubah.',
                ]);
            }

            if (
                $statusAwal === self::DRAF
                && $paket->status === self::DITERBITKAN
            ) {
                $detail = $paket->details()
                    ->lockForUpdate()
                    ->first(['id']);

                if ($detail === null) {
                    throw ValidationException::withMessages([
                        'paket_semester' =>
                        'Paket kosong tidak dapat diterbitkan.',
                    ]);
                }
            }
        });

        static::deleting(function (): void {
            throw ValidationException::withMessages([
                'paket_semester' =>
                'Paket tidak dapat dihapus. Gunakan pengarsipan.',
            ]);
        });
    }

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPaket::class, 'paket_semester_id');
    }

    public function isDraf(): bool
    {
        return $this->status === self::DRAF;
    }

    public function isDiterbitkan(): bool
    {
        return $this->status === self::DITERBITKAN;
    }

    public function isArsip(): bool
    {
        return $this->status === self::ARSIP;
    }

    public function rombel(): HasMany
    {
        return $this->hasMany(Rombel::class, 'paket_semester_id');
    }
}
