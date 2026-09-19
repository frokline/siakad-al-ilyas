<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class MataKuliah extends Model
{
    use HasFactory;

    public const STATUS = [
        1 => 'Aktif',
        0 => 'Nonaktif',
    ];

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'kode',
        'nama',
    ];

    protected $attributes = [
        'aktif' => true,
    ];

    protected function casts(): array
    {
        return [
            'program_studi_id' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (MataKuliah $mataKuliah): void {
            if ($mataKuliah->isDirty(['program_studi_id', 'kode'])) {
                throw ValidationException::withMessages([
                    'mata_kuliah' => 'Kode dan program studi mata kuliah tidak dapat diubah setelah dibuat.',
                ]);
            }
        });

        static::deleting(function (MataKuliah $mataKuliah): void {
            throw ValidationException::withMessages([
                'mata_kuliah' => 'Gunakan status nonaktif untuk menghentikan penggunaan mata kuliah.',
            ]);
        });
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('mata_kuliah.aktif', true);
    }

    public function kurikulumMataKuliah(): HasMany
    {
        return $this->hasMany(KurikulumMataKuliah::class, 'mata_kuliah_id');
    }
}
