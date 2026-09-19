<?php

namespace App\Models\Concerns;


use App\Models\Berkas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use LogicException;

trait MenjagaBuktiPembayaranPrivat
{
    protected static function bootMenjagaBuktiPembayaranPrivat(): void
    {
        static::saving(function ($lampiran): void {
            if (DB::transactionLevel() < 1) {
                throw new LogicException('Penautan berkas harus melalui transaksi.');
            }
            Berkas::query()->whereKey($lampiran->berkas_id)->lockForUpdate()->firstOrFail();
            if (
                Schema::hasTable('permohonan_surat') && DB::table('permohonan_surat')
                ->where(fn($q) => $q->where('lampiran_berkas_id', $lampiran->berkas_id)->orWhere('hasil_berkas_id', $lampiran->berkas_id))
                ->lockForUpdate()->first()
            ) {
                throw ValidationException::withMessages(['berkas_ids' => 'Dokumen surat privat tidak boleh dijadikan lampiran pembelajaran.']);
            }
            if (Schema::hasTable('pembayaran') && DB::table('pembayaran')->where('bukti_berkas_id', $lampiran->berkas_id)->lockForUpdate()->first()) {
                throw ValidationException::withMessages(['berkas_ids' => 'Berkas bukti pembayaran tidak boleh dijadikan lampiran pembelajaran.']);
            }
        });
    }
}
