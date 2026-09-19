<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use LogicException;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'audit_log';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'entitas_id' => 'integer',
            'versi_entitas' => 'integer',
            'sebelum' => 'array',
            'sesudah' => 'array',
            'waktu' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $audit): void {
            if (
                DB::transactionLevel() < 1
                || blank($audit->entitas) || blank($audit->aksi)
                || $audit->entitas_id < 1 || $audit->waktu === null
            ) {
                throw new LogicException('Audit harus ditulis lengkap dalam transaksi perubahan.');
            }
        });

        static::updating(function (): never {
            throw new LogicException('Riwayat audit tidak boleh diubah.');
        });

        static::deleting(function (): never {
            throw new LogicException('Riwayat audit tidak boleh dihapus.');
        });
    }

    public function pelaku(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelaku_id');
    }
}
