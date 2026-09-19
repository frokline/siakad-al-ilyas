<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SinkronNotifikasi;
use App\Services\SumberNotifikasi;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

class SinkronkanNotifikasi extends Command
{
    protected $signature = 'notifikasi:sinkronkan {--user= : Batasi ke satu ID pengguna} {--hari= : Rentang perubahan, 1 sampai 3650 hari}';
    protected $description = 'Buat notifikasi idempoten dari revisi terbaru sumber yang dapat dibaca penerima';
    public function handle(SinkronNotifikasi $sinkron): int
    {
        $v = Validator::make(
            ['user' => $this->option('user'), 'hari' => $this->option('hari') ?? config('notifikasi.hari_sinkronisasi', 30)],
            ['user' => ['nullable', 'integer', 'min:1'], 'hari' => ['required', 'integer', 'between:1,3650']]
        );
        if ($v->fails()) {
            $this->error($v->errors()->first());
            return self::INVALID;
        }
        $data = $v->validated();
        if (! Schema::hasTable('notifikasi')) {
            $this->error('Jalankan migration notifikasi dahulu.');
            return self::FAILURE;
        }
        foreach (app(SumberNotifikasi::class)->aktif() as $jenis) {
            $d = app(SumberNotifikasi::class)->definisi($jenis);
            if (
                ! Schema::hasTable($d['tabel']) || ! Schema::hasColumns($d['tabel'], ['id', 'updated_at', $jenis === 'krs' ? 'versi' : 'revisi'])
                || ! Route::has($d['route'])
            ) {
                $this->error("Modul {$jenis} belum lengkap. Periksa tabel/kolom/route atau nonaktifkan pada config/notifikasi.php.");
                return self::FAILURE;
            }
        }
        if (! empty($data['user']) && ! User::query()->whereKey((int) $data['user'])->exists()) {
            $this->error('ID pengguna tidak ditemukan.');
            return self::INVALID;
        }
        // Cache lock dipakai untuk menghindari beban ganda. Unique DB tetap pengaman terakhir.
        $lock = Cache::lock('siakad:notifikasi:sinkronkan', 3600);
        if (! $lock->get()) {
            $this->warn('Sinkronisasi lain masih berjalan.');
            return self::FAILURE;
        }
        try {
            $sejak = CarbonImmutable::now('UTC')->subDays((int) $data['hari']);
            $jumlah = 0;
            $pengguna = 0;
            User::query()->where('status', 'aktif')->when(! empty($data['user']), fn($q) => $q->whereKey((int) $data['user']))
                ->select('id')->chunkById(50, function ($rows) use ($sinkron, $sejak, &$jumlah, &$pengguna): void {
                    foreach ($rows as $u) {
                        $jumlah += $sinkron->pengguna((int) $u->id, $sejak);
                        $pengguna++;
                    }
                });
            $this->info("Selesai: {$pengguna} akun diperiksa, {$jumlah} notifikasi baru.");
            return self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
