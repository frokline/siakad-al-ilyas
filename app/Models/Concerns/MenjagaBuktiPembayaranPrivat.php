<?php

namespace App\Models\Concerns;

use App\Models\Berkas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use LogicException;

/**
 * Menjaga agar berkas privat pembayaran dan surat tidak dapat
 * digunakan sebagai lampiran pembelajaran.
 *
 * @mixin Model
 */
trait MenjagaBuktiPembayaranPrivat
{
    protected static function bootMenjagaBuktiPembayaranPrivat(): void
    {
        /** @disregard P1013 Trait ini hanya digunakan oleh model Eloquent. */
        static::saving(function (Model $lampiran): void {
            $berkasId = $lampiran->getAttribute('berkas_id');

            if (DB::transactionLevel() < 1) {
                throw new LogicException(
                    'Penautan berkas harus melalui transaksi.'
                );
            }

            Berkas::query()
                ->whereKey($berkasId)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                Schema::hasTable('permohonan_surat')
                && DB::table('permohonan_surat')
                ->where(function ($query) use ($berkasId): void {
                    $query
                        ->where('lampiran_berkas_id', $berkasId)
                        ->orWhere('hasil_berkas_id', $berkasId);
                })
                ->lockForUpdate()
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'berkas_ids' => 'Dokumen surat privat tidak boleh dijadikan lampiran pembelajaran.',
                ]);
            }

            if (
                Schema::hasTable('pembayaran')
                && DB::table('pembayaran')
                ->where('bukti_berkas_id', $berkasId)
                ->lockForUpdate()
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'berkas_ids' => 'Berkas bukti pembayaran tidak boleh dijadikan lampiran pembelajaran.',
                ]);
            }
        });
    }
}
