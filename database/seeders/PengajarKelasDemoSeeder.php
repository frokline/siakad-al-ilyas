<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PengajarKelasDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            $dosenId = DB::table('dosen')
                ->where('kode_dosen', 'DSN001')
                ->value('id');

            if (! $dosenId) {
                throw new RuntimeException(
                    'Dosen DSN001 belum tersedia.'
                );
            }

            $kodeKelas = [
                'PAI-1A-PAI101',
                'PAI-1A-PAI102',
                'PAI-1A-PAI103',
            ];

            foreach ($kodeKelas as $kode) {
                $kelasId = DB::table('kelas_kuliah')
                    ->where('kode', $kode)
                    ->value('id');

                if (! $kelasId) {
                    throw new RuntimeException(
                        'Kelas ' . $kode . ' belum tersedia.'
                    );
                }

                DB::table('pengajar_kelas')->updateOrInsert(
                    [
                        'kelas_kuliah_id' => $kelasId,
                        'dosen_id' => $dosenId,
                        'peran' => 'pengajar',
                    ],
                    [
                        'aktif' => true,
                        'revisi' => 1,
                        'diaktifkan_at' => $now,
                        'dinonaktifkan_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $this->command?->info(
                'Penugasan dosen DSN001 ke tiga kelas demo berhasil dibuat.'
            );
        });
    }
}