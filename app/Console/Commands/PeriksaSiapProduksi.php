<?php

namespace App\Console\Commands;

use Database\Seeders\AkunDemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PeriksaSiapProduksi extends Command
{
    protected $signature = 'siakad:periksa-produksi';

    protected $description = 'Memeriksa konfigurasi .env, migration, dan data sebelum aplikasi dipublikasikan';

    private int $lulus = 0;

    private int $peringatan = 0;

    private int $gagal = 0;

    public function handle(): int
    {
        $this->components->info('Pemeriksaan siap produksi');

        $this->newLine();
        $this->line('<options=bold>Konfigurasi .env</>');
        $this->periksaKonfigurasi();

        $this->newLine();
        $this->line('<options=bold>Database</>');
        $this->periksaDatabase();

        $this->newLine();
        $this->components->twoColumnDetail(
            'Hasil: '.$this->lulus.' lulus, '.$this->peringatan.' peringatan, '.$this->gagal.' gagal',
            $this->gagal > 0
                ? '<fg=red;options=bold>TIDAK SIAP</>'
                : ($this->peringatan > 0
                    ? '<fg=yellow;options=bold>SIAP DENGAN PERINGATAN</>'
                    : '<fg=green;options=bold>SIAP</>'),
        );

        if ($this->gagal > 0) {
            $this->newLine();
            $this->components->error(
                'Perbaiki seluruh pemeriksaan gagal sebelum publikasi. Salin .env.production.example menjadi .env untuk titik awal.',
            );

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function periksaKonfigurasi(): void
    {
        if (! file_exists(base_path('.env'))) {
            $this->catat(
                'File .env',
                'PERINGATAN',
                'tidak ditemukan; konfigurasi mungkin dibaca dari environment server',
            );
        }

        $environment = (string) config('app.env');
        $this->catat(
            'APP_ENV',
            $environment === 'production' ? 'LULUS' : 'GAGAL',
            $environment.($environment === 'production' ? '' : ' (harus production)'),
        );

        $debug = (bool) config('app.debug');
        $this->catat(
            'APP_DEBUG',
            $debug ? 'GAGAL' : 'LULUS',
            $debug ? 'masih true; stack trace bocor ke publik' : 'false',
        );

        $key = (string) config('app.key');
        $this->catat(
            'APP_KEY',
            $key === '' ? 'GAGAL' : 'LULUS',
            $key === '' ? 'kosong; jalankan php artisan key:generate' : 'tersedia',
        );

        $url = (string) config('app.url');
        $tanpaDomain = $url === ''
            || str_contains($url, 'localhost')
            || str_contains($url, '127.0.0.1');

        $this->catat(
            'APP_URL',
            $tanpaDomain ? 'GAGAL' : (str_starts_with($url, 'http://') ? 'PERINGATAN' : 'LULUS'),
            $url === ''
                ? 'kosong'
                : $url.($tanpaDomain
                    ? ' (tautan unduh bermeterai tidak valid)'
                    : (str_starts_with($url, 'http://') ? ' (memakai http://; pakai https)' : '')),
        );

        $kanalLog = (string) config('logging.default', 'stack');
        $logLevel = (string) config('logging.channels.'.$kanalLog.'.level', 'debug');
        $this->catat(
            'LOG_LEVEL',
            in_array($logLevel, ['debug', 'info'], true) ? 'PERINGATAN' : 'LULUS',
            $logLevel.($logLevel === 'debug' ? ' (terlalu ramai untuk produksi)' : ''),
        );

        $secure = config('session.secure');
        $this->catat(
            'SESSION_SECURE_COOKIE',
            $secure === false ? 'PERINGATAN' : 'LULUS',
            $secure === null
                ? 'otomatis (Secure hanya saat HTTPS)'
                : ($secure ? 'dipaksa true' : 'dipaksa false; cookie bisa terkirim lewat http'),
        );

        $mailer = (string) config('mail.default');
        $alamatMail = (string) config('mail.from.address');
        $mailKurang = $mailer === 'log' || str_contains($alamatMail, 'example.com');
        $this->catat(
            'Pengaturan mail',
            $mailKurang ? 'PERINGATAN' : 'LULUS',
            $mailer.' / '.$alamatMail.($mailKurang ? ' (email tidak benar-benar terkirim)' : ''),
        );

        $scanner = (string) config('berkas.scanner', 'none');
        $this->catat(
            'Pemindai berkas',
            $scanner === 'none' || $scanner === '' ? 'PERINGATAN' : 'LULUS',
            $scanner === '' ? 'none' : $scanner,
        );
    }

    private function periksaDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $exception) {
            $this->catat('Koneksi database', 'GAGAL', $exception->getMessage());

            return false;
        }

        $this->catat('Koneksi database', 'LULUS', config('database.default'));

        $this->periksaMigrationTertunda();
        $this->periksaTabelPendukung();
        $this->periksaAkunDemo();

        return true;
    }

    private function periksaMigrationTertunda(): void
    {
        if (! Schema::hasTable('migrations')) {
            $this->catat('Migration', 'GAGAL', 'belum ada satupun; jalankan php artisan migrate');

            return;
        }

        $berkas = array_map(
            static fn (string $path): string => basename($path, '.php'),
            glob(database_path('migrations/*.php')) ?: [],
        );

        $sudah = DB::table('migrations')->pluck('migration')->all();
        $tertunda = array_values(array_diff($berkas, $sudah));

        $this->catat(
            'Migration',
            $tertunda === [] ? 'LULUS' : 'GAGAL',
            $tertunda === []
                ? count($berkas).' migration sudah berjalan'
                : count($tertunda).' belum berjalan: '.implode(', ', $tertunda),
        );
    }

    private function periksaTabelPendukung(): void
    {
        $tabel = [
            'Queue '.config('queue.default') => config('queue.default') === 'database' ? 'jobs' : null,
            'Session '.config('session.driver') => config('session.driver') === 'database' ? 'sessions' : null,
            'Cache '.config('cache.default') => config('cache.default') === 'database' ? 'cache' : null,
        ];

        foreach ($tabel as $nama => $isi) {
            if ($isi === null) {
                $this->catat($nama, 'LULUS', 'tidak memakai driver database');

                continue;
            }

            $ada = Schema::hasTable($isi);

            $this->catat(
                $nama,
                $ada ? 'LULUS' : 'GAGAL',
                $ada ? 'tabel '.$isi.' tersedia' : 'tabel '.$isi.' belum ada; jalankan php artisan migrate',
            );
        }
    }

    private function periksaAkunDemo(): void
    {
        if (! Schema::hasTable('users')) {
            $this->catat('Akun demo', 'GAGAL', 'tabel users belum ada');

            return;
        }

        $ditemukan = [];

        foreach (AkunDemoSeeder::AKUN_DEMO as $akun) {
            $hash = DB::table('users')
                ->where('username', $akun['username'])
                ->value('password_hash');

            if (is_string($hash) && Hash::check($akun['password'], $hash)) {
                $ditemukan[] = $akun['username'];
            }
        }

        $this->catat(
            'Akun demo',
            $ditemukan === [] ? 'LULUS' : 'GAGAL',
            $ditemukan === []
                ? 'tidak ada kredensial publik di database'
                : 'masih memakai kata sandi demo: '.implode(', ', $ditemukan),
        );
    }

    private function catat(string $nama, string $status, string $pesan): void
    {
        match ($status) {
            'LULUS' => $this->lulus++,
            'PERINGATAN' => $this->peringatan++,
            default => $this->gagal++,
        };

        $warna = match ($status) {
            'LULUS' => 'green',
            'PERINGATAN' => 'yellow',
            default => 'red',
        };

        $this->components->twoColumnDetail(
            $nama.' <fg=gray>'.$pesan.'</>',
            '<fg='.$warna.';options=bold>'.$status.'</>',
        );
    }
}
