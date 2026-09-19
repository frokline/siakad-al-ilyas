<?php

namespace App\Models;

use App\Services\SumberNotifikasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $guarded = ['*'];
    protected function casts(): array
    {
        return [
            'penerima_id' => 'integer',
            'sumber_id' => 'integer',
            'sumber_revisi' => 'integer',
            'dibaca_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $n): void {
            $def = SumberNotifikasi::DAFTAR[$n->jenis] ?? null;
            if (
                $n->getConnection()->transactionLevel() < 1 || ! $def || $n->penerima_id < 1 || $n->sumber_id < 1
                || $n->sumber_revisi < 1 || $n->sumber_revisi > 4294967295 || $n->sumber_tabel !== $def['tabel']
                || $n->judul !== $def['judul'] || $n->kunci_peristiwa !== self::kunci($n->jenis, $n->sumber_id, $n->sumber_revisi)
            ) {
                throw new LogicException('Notifikasi harus dibuat oleh layanan dari sumber terdaftar.');
            }
            if (! $n->exists && $n->dibaca_at !== null) {
                throw new LogicException('Notifikasi baru harus belum dibaca.');
            }
            if ($n->exists && ($n->isDirty(['penerima_id', 'jenis', 'judul', 'sumber_tabel', 'sumber_id', 'sumber_revisi', 'kunci_peristiwa', 'created_at'])
                || ($n->isDirty('dibaca_at') && ($n->getRawOriginal('dibaca_at') !== null || $n->dibaca_at === null)))) {
                throw new LogicException('Identitas tetap; waktu baca pertama tidak boleh diubah.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Penghapusan notifikasi bukan bagian modul ini.');
        });
    }
    public static function kunci(string $jenis, int $id, int $revisi): string
    {
        return $jenis . ':' . $id . ':revisi:' . $revisi;
    }
    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }
    // Referensi sumber adalah referensi logis terdaftar, bukan morphTo dari nama class input.
}
