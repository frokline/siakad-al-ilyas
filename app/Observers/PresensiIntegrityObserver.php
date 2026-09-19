<?php

namespace App\Observers;

use App\Models\Pertemuan;
use App\Models\Presensi;
use App\Models\PresensiPertemuan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

final class PresensiIntegrityObserver
{
    public function updating(Pertemuan $sesi): void
    {
        if (! $sesi->isDirty('status') || $sesi->status !== Pertemuan::SELESAI) {
            return;
        }
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Penyelesaian sesi harus dalam transaksi.');
        }
        $daftar = PresensiPertemuan::query()->where('pertemuan_id', $sesi->id)->lockForUpdate()->first();
        if ($daftar === null || $daftar->status !== PresensiPertemuan::DITUTUP) {
            throw ValidationException::withMessages(['pertemuan' => 'Catat seluruh peserta dan tutup presensi sebelum menyelesaikan pertemuan.']);
        }
        $baris = $daftar->presensi()->orderBy('id')->lockForUpdate()->get(['id', 'status']);
        if ($baris->count() !== $daftar->jumlah_peserta || $baris->contains('status', Presensi::BELUM)) {
            throw ValidationException::withMessages(['pertemuan' => 'Daftar presensi belum lengkap.']);
        }
    }
}
