<?php

namespace App\Console\Commands;

use App\Models\Berkas;
use App\Services\LogBerkas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class RekonsiliasiBerkas extends Command
{
    protected $signature = 'berkas:rekonsiliasi
                            {--dry-run : Periksa kandidat tanpa mengubah data}';

    protected $description = 'Menangani metadata unggahan berkas yang kedaluwarsa';

    public function handle(LogBerkas $audit): int
    {
        $menit = (int) config('berkas.batas_menunggu_menit', 30);

        if ($menit < 1) {
            $this->error('Batas waktu rekonsiliasi harus minimal 1 menit.');

            return self::FAILURE;
        }

        $batas = now()->subMinutes($menit);

        $query = Berkas::query()
            ->where('status', Berkas::MENUNGGU)
            ->where('created_at', '<=', $batas);

        if ($this->option('dry-run')) {
            $jumlah = (clone $query)->count();

            $this->info("Kandidat kedaluwarsa: {$jumlah}");
            $this->line("Batas waktu: {$batas->toDateTimeString()}");
            $this->comment('Mode pemeriksaan: tidak ada data yang diubah.');

            foreach ((clone $query)->orderBy('id')->limit(50)->pluck('id') as $id) {
                $this->line("ID berkas: {$id}");
            }

            if ($jumlah > 50) {
                $this->line('Daftar dibatasi hingga 50 ID pertama.');
            }

            return self::SUCCESS;
        }

        $berhasil = 0;
        $dilewati = 0;
        $gagal = 0;

        $query->select('id')->orderBy('id')->chunkById(
            100,
            function ($berkas) use (
                $audit, $batas, &$berhasil, &$dilewati, &$gagal
            ): void {
                foreach ($berkas as $item) {
                    try {
                        $hasil = DB::transaction(function () use (
                            $item, $audit, $batas
                        ): string {
                            $file = Berkas::query()
                                ->whereKey($item->id)
                                ->lockForUpdate()
                                ->first();

                            if (
                                ! $file
                                || $file->status !== Berkas::MENUNGGU
                                || $file->created_at > $batas
                            ) {
                                return 'dilewati';
                            }

                            $sebelum = $file->ringkasanAudit();
                            $alasan = 'Unggahan melewati batas waktu pemrosesan tanpa penyelesaian.';

                            $file->status = Berkas::DITOLAK;
                            $file->pesan_status = $alasan;
                            $file->revisi++;
                            $file->save();

                            $audit->tulis(
                                $file,
                                null,
                                'rekonsiliasi_kedaluwarsa',
                                $sebelum,
                                $alasan
                            );

                            return 'berhasil';
                        }, 3);

                        if ($hasil === 'berhasil') {
                            $berhasil++;
                        } else {
                            $dilewati++;
                        }
                    } catch (Throwable $error) {
                        $gagal++;

                        $this->warn(
                            "Gagal memproses berkas ID {$item->id}: "
                            . $error::class
                        );
                    }
                }
            }
        );

        $this->info("Berhasil direkonsiliasi: {$berhasil}");
        $this->line("Dilewati: {$dilewati}");
        $this->line("Gagal: {$gagal}");

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }
}