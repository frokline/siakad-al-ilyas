<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class KurikulumMataKuliah extends Model
{
    use HasFactory;

    public const SIFAT = [
        'wajib' => 'Wajib',
        'pilihan' => 'Pilihan',
    ];

    protected $table = 'kurikulum_mata_kuliah';

    protected $fillable = [
        'sks',
        'semester_rekomendasi',
        'sifat',
    ];

    protected $attributes = [
        'sifat' => 'wajib',
    ];

    protected function casts(): array
    {
        return [
            'kurikulum_id' => 'integer',
            'mata_kuliah_id' => 'integer',
            'sks' => 'decimal:1',
            'semester_rekomendasi' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (KurikulumMataKuliah $detail): void {
            if (
                $detail->exists
                && $detail->isDirty(['kurikulum_id', 'mata_kuliah_id'])
            ) {
                throw ValidationException::withMessages([
                    'detail' => 'Kurikulum dan mata kuliah pada entri ini tidak dapat diganti.',
                ]);
            }

            $detail->pastikanKurikulumDraf();
        });

        static::deleting(function (KurikulumMataKuliah $detail): void {
            $detail->pastikanKurikulumDraf();
        });
    }

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    private function pastikanKurikulumDraf(): void
    {
        $status = $this->kurikulum()
            ->lockForUpdate()
            ->value('status');

        if ($status !== Kurikulum::DRAF) {
            throw ValidationException::withMessages([
                'kurikulum' => 'Susunan mata kuliah hanya dapat diubah pada kurikulum draf.',
            ]);
        }
    }

    public function detailPaket(): HasMany
    {
        return $this->hasMany(
            DetailPaket::class,
            'kurikulum_mata_kuliah_id'
        );
    }
}
