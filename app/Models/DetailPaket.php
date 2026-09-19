<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class DetailPaket extends Model
{
    use HasFactory;

    protected $table = 'detail_paket';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'paket_semester_id' => 'integer',
            'kurikulum_mata_kuliah_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DetailPaket $detail): void {
            $paket = self::requireDraft(
                (int) $detail->paket_semester_id
            );

            $mataKuliah = KurikulumMataKuliah::query()
                ->whereKey($detail->kurikulum_mata_kuliah_id)
                ->lockForUpdate()
                ->first();

            if (
                $mataKuliah === null
                || (int) $mataKuliah->kurikulum_id !== $paket->kurikulum_id
            ) {
                throw ValidationException::withMessages([
                    'mata_kuliah_ids' =>
                    'Mata kuliah harus berasal dari kurikulum paket.',
                ]);
            }
        });

        static::updating(function (): void {
            throw ValidationException::withMessages([
                'mata_kuliah_ids' =>
                'Ubah isi paket melalui pilihan mata kuliah pada formulir paket.',
            ]);
        });

        static::deleting(function (DetailPaket $detail): void {
            self::requireDraft((int) $detail->paket_semester_id);
        });
    }

    private static function requireDraft(int $paketId): PaketSemester
    {
        $paket = PaketSemester::query()
            ->whereKey($paketId)
            ->lockForUpdate()
            ->first();

        if ($paket === null || ! $paket->isDraf()) {
            throw ValidationException::withMessages([
                'mata_kuliah_ids' =>
                'Susunan mata kuliah hanya dapat diubah pada paket draf.',
            ]);
        }

        return $paket;
    }

    public function paketSemester(): BelongsTo
    {
        return $this->belongsTo(PaketSemester::class, 'paket_semester_id');
    }

    public function kurikulumMataKuliah(): BelongsTo
    {
        return $this->belongsTo(
            KurikulumMataKuliah::class,
            'kurikulum_mata_kuliah_id'
        );
    }

    public function kelasKuliah(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\KelasKuliah::class, 'detail_paket_id');
    }

    public function presensi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Presensi::class, 'detail_krs_id');
    }
}
