<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn(
                'Seeder demo dilewati karena environment bukan local/testing. '
                . 'Buat akun admin produksi dengan: php artisan siakad:buat-admin',
            );

            return;
        }

        $this->call([
            AkunDemoSeeder::class,
            DataAkademikDemoSeeder::class,
            PengajarKelasDemoSeeder::class,
            JadwalKuliahDemoSeeder::class,
            PembelajaranPresensiDemoSeeder::class,
        ]);
    }
}