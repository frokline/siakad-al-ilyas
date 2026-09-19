<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class RiwayatSurat extends Model
{
    public const UPDATED_AT = null;
    protected $table = 'riwayat_surat';
    protected $guarded = ['*'];
    protected function casts(): array
    {
        return ['permohonan_surat_id' => 'integer', 'pelaku_id' => 'integer', 'revisi_permohonan' => 'integer', 'waktu' => 'immutable_datetime', 'created_at' => 'immutable_datetime'];
    }
    protected static function booted(): void
    {
        static::saving(function (self $r): void {
            if ($r->exists || $r->getConnection()->transactionLevel() < 1) {
                throw new LogicException('Riwayat surat hanya ditambahkan dalam transaksi.');
            }
            $p = PermohonanSurat::query()->whereKey($r->permohonan_surat_id)->lockForUpdate()->firstOrFail();
            $lama = self::query()->where('permohonan_surat_id', $p->id)->orderByDesc('revisi_permohonan')->lockForUpdate()->first();
            if (
                $r->revisi_permohonan !== $p->revisi || $r->status_baru !== $p->status || ! $r->waktu
                || mb_strlen(trim((string) $r->catatan)) < 10 || mb_strlen((string) $r->catatan) > 1000
                || ($lama ? ($r->status_lama !== $lama->status_baru || $r->revisi_permohonan !== $lama->revisi_permohonan + 1
                    || ! in_array($r->status_baru, PermohonanSurat::TRANSISI[$lama->status_baru] ?? [], true))
                    : ($r->revisi_permohonan !== 1 || $r->status_lama !== null || $r->status_baru !== 'diajukan'))
            ) {
                throw new LogicException('Riwayat harus berurutan dan cocok dengan status permohonan.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Riwayat surat tidak boleh dihapus.');
        });
    }
    public function permohonanSurat(): BelongsTo
    {
        return $this->belongsTo(PermohonanSurat::class, 'permohonan_surat_id');
    }
    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelaku_id');
    }
}
