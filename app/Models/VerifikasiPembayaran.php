<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class VerifikasiPembayaran extends Model
{
    public const TERIMA = 'terima';
    public const TOLAK = 'tolak';

    public const TINDAKAN = [
        self::TERIMA => 'Menerima pembayaran',
        self::TOLAK => 'Menolak pembayaran',
    ];

    protected $table = 'verifikasi_pembayaran';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'pembayaran_id' => 'integer',
            'petugas_id' => 'integer',
            'revisi_pembayaran' => 'integer',
            'waktu' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $verifikasi): void {
            $valid = (
                $verifikasi->getConnection()->transactionLevel() >= 1
                && array_key_exists(
                    (string) $verifikasi->tindakan,
                    self::TINDAKAN
                )
                && $verifikasi->status_sebelum === Pembayaran::MENUNGGU
                && in_array(
                    $verifikasi->status_sesudah,
                    [
                        Pembayaran::DITERIMA,
                        Pembayaran::DITOLAK,
                    ],
                    true
                )
                && (
                    (
                        $verifikasi->tindakan === self::TERIMA
                        && $verifikasi->status_sesudah === Pembayaran::DITERIMA
                    )
                    || (
                        $verifikasi->tindakan === self::TOLAK
                        && $verifikasi->status_sesudah === Pembayaran::DITOLAK
                    )
                )
                && $verifikasi->revisi_pembayaran >= 2
                && $verifikasi->revisi_pembayaran <= 4294967295
                && mb_strlen(trim((string) $verifikasi->catatan)) >= 10
                && $verifikasi->waktu !== null
            );

            if (! $valid) {
                throw new LogicException(
                    'Riwayat verifikasi pembayaran tidak konsisten.'
                );
            }
        });

        static::updating(function (): never {
            throw new LogicException(
                'Riwayat verifikasi pembayaran tidak dapat diubah.'
            );
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Riwayat verifikasi pembayaran tidak dapat dihapus.'
            );
        });
    }

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(
            Pembayaran::class,
            'pembayaran_id'
        );
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'petugas_id'
        );
    }
}
