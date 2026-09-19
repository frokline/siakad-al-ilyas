<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\PermohonanSurat;
use Illuminate\Console\Command;

class PeriksaRiwayatSurat extends Command
{
    protected $signature = 'surat:periksa-riwayat';
    protected $description = 'Memeriksa urutan status, slot, hasil, dan audit surat tanpa mengubah data.';
    public function handle(): int
    {
        $jumlah = 0;
        $rusak = 0;
        PermohonanSurat::query()->with('riwayat')->chunkById(200, function ($rows) use (&$jumlah, &$rusak): void {
            foreach ($rows as $p) {
                $jumlah++;
                $masalah = [];
                $status = null;
                $revisi = 0;
                foreach ($p->riwayat->sortBy('revisi_permohonan') as $r) {
                    if (
                        $r->revisi_permohonan !== $revisi + 1 || $r->status_lama !== $status
                        || ($status === null ? $r->status_baru !== 'diajukan' : ! in_array($r->status_baru, PermohonanSurat::TRANSISI[$status] ?? [], true))
                    ) {
                        $masalah[] = 'urutan riwayat';
                    }
                    if (! AuditLog::query()->where('entitas', 'permohonan_surat')->where('entitas_id', $p->id)
                        ->where('versi_entitas', $r->revisi_permohonan)->where('aksi', $r->status_baru)->exists()) {
                        $masalah[] = 'audit hilang';
                    }
                    $status = $r->status_baru;
                    $revisi = $r->revisi_permohonan;
                }
                if ($status !== $p->status || $revisi !== $p->revisi) {
                    $masalah[] = 'status/revisi tidak cocok';
                }
                $slot = in_array($p->status, ['diajukan', 'diproses'], true) ? PermohonanSurat::slot($p->registrasi_semester_id, $p->jenis_surat_id) : null;
                if ($p->slot_aktif !== $slot) {
                    $masalah[] = 'slot aktif';
                }
                $hasil = $p->hasil_berkas_id !== null;
                if (
                    $hasil !== ($p->hasil_snapshot !== null) || $hasil !== ($p->terbit_at !== null) || $hasil !== ($p->nomor_surat !== null)
                    || ($p->status === 'terbit' && ! $hasil) || ($hasil && ! in_array($p->status, ['terbit', 'dibatalkan'], true))
                ) {
                    $masalah[] = 'kelengkapan hasil';
                }
                if ($masalah) {
                    $rusak++;
                    $this->error('Permohonan #' . $p->id . ': ' . implode(', ', array_unique($masalah)));
                }
            }
        });
        $this->line('Diperiksa: ' . $jumlah . '; perlu ditinjau: ' . $rusak . '. Tidak ada data diubah.');
        return $rusak === 0 ? self::SUCCESS : self::FAILURE;
    }
}
