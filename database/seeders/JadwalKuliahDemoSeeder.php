<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JadwalKuliahDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            $jadwal = [
                [
                    'kode_kelas' => 'PAI-1A-PAI101',
                    'hari' => 1,
                    'jam_mulai' => '08:00:00',
                    'jam_selesai' => '09:40:00',
                    'lokasi' => 'Ruang PAI 101',
                ],
                [
                    'kode_kelas' => 'PAI-1A-PAI102',
                    'hari' => 2,
                    'jam_mulai' => '10:00:00',
                    'jam_selesai' => '11:40:00',
                    'lokasi' => 'Ruang PAI 102',
                ],
                [
                    'kode_kelas' => 'PAI-1A-PAI103',
                    'hari' => 3,
                    'jam_mulai' => '13:00:00',
                    'jam_selesai' => '14:40:00',
                    'lokasi' => 'Ruang PAI 103',
                ],
            ];

            foreach ($jadwal as $item) {
                $kelasId = DB::table('kelas_kuliah')
                    ->where('kode', $item['kode_kelas'])
                    ->value('id');

                if (! $kelasId) {
                    throw new RuntimeException(
                        'Kelas '.$item['kode_kelas'].' tidak ditemukan. '
                        .'Jalankan DataAkademikDemoSeeder terlebih dahulu.'
                    );
                }

                DB::table('jadwal_kuliah')->updateOrInsert(
                    [
                        'kelas_kuliah_id' => $kelasId,
                        'hari' => $item['hari'],
                    ],
                    [
                        'jam_mulai' => $item['jam_mulai'],
                        'jam_selesai' => $item['jam_selesai'],
                        'berlaku_mulai' => '2026-09-01',
                        'berlaku_selesai' => '2027-01-31',
                        'metode' => 'luring',
                        'lokasi' => $item['lokasi'],
                        'tautan_pertemuan' => null,
                        'aktif' => true,
                        'revisi' => 1,
                        'dinonaktifkan_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $this->command?->info(
                'Tiga jadwal kuliah demo berhasil dibuat.'
            );
        });
    }
}