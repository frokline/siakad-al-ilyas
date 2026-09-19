<?php

namespace App\Services;

use App\Models\Berkas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use LogicException;

final class PrivasiBuktiPembayaran
{
    public static function khususKeuangan(Berkas $b): void
    {
        // Pemanggil sudah mengunci row berkas. Semua penaut lampiran memakai guard yang sama.
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Pemeriksaan privasi harus di dalam transaksi.');
        }
        if (
            Schema::hasTable('permohonan_surat') && DB::table('permohonan_surat')
            ->where(fn($q) => $q->where('lampiran_berkas_id', $b->id)->orWhere('hasil_berkas_id', $b->id))->lockForUpdate()->first()
        ) {
            throw ValidationException::withMessages(['bukti_berkas_id' => 'Dokumen surat privat tidak boleh dijadikan bukti pembayaran.']);
        }
        foreach (['materi_berkas', 'kegiatan_berkas', 'pengumpulan_berkas'] as $tabel) {
            if (Schema::hasTable($tabel) && DB::table($tabel)->where('berkas_id', $b->id)->lockForUpdate()->first()) {
                throw ValidationException::withMessages(['bukti_berkas_id' => 'Berkas pernah dipakai sebagai lampiran pembelajaran. Unggah bukti khusus keuangan yang benar.']);
            }
        }
    }
}
