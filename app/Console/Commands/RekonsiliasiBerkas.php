<?php

namespace App\Console\Commands;

use App\Models\Berkas;
use App\Services\LogBerkas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RekonsiliasiBerkas extends Command
{
    protected $signature = 'berkas:rekonsiliasi {--terapkan : Tandai proses menunggu yang kedaluwarsa sebagai gagal}';
    protected $description = 'Periksa unggahan terhenti; tanpa penghapusan fisik objek.';
    public function handle(LogBerkas $audit): int
    {
        $batas = now('UTC')->subMinutes(max(30, (int) config('berkas.batas_menunggu_menit')));
        $jumlah = 0;
        Berkas::query()->where('status', Berkas::MENUNGGU)->where('created_at', '<=', $batas)
            ->select('id')->chunkById(100, function ($rows) use (&$jumlah, $audit, $batas): void {
                foreach ($rows as $item) {
                    if (! $this->option('terapkan')) {
                        $this->line('Menunggu terlalu lama: berkas #' . $item->id);
                        $jumlah++;
                        continue;
                    }
                    $diubah = DB::transaction(function () use ($item, $batas, $audit): bool {
                        $file = Berkas::query()->whereKey($item->id)->lockForUpdate()->first();
                        if (! $file || $file->status !== Berkas::MENUNGGU || $file->created_at->gt($batas)) {
                            return false;
                        }
                        $sebelum = $file->ringkasanAudit();
                        $file->status = Berkas::DITOLAK;
                        $file->pesan_status = 'Proses unggah melewati batas waktu. Gunakan formulir baru untuk mencoba kembali.';
                        $file->revisi++;
                        $file->save();
                        $audit->tulis($file, null, 'rekonsiliasi', $sebelum, $file->pesan_status);
                        return true;
                    }, 3);
                    if ($diubah) {
                        $jumlah++;
                    }
                }
            });
        $this->info(($this->option('terapkan') ? 'Ditandai gagal: ' : 'Ditemukan: ') . $jumlah);
        return self::SUCCESS;
    }
}
