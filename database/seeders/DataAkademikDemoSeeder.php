<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DataAkademikDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            /*
             * 1. Peran sistem.
             */
            foreach ([
                'mahasiswa' => 'Mahasiswa',
                'dosen' => 'Dosen',
                'admin_akademik' => 'Admin Akademik',
                'admin_keuangan' => 'Admin Keuangan',
            ] as $kode => $nama) {
                DB::table('roles')->updateOrInsert(
                    ['kode' => $kode],
                    [
                        'nama' => $nama,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
             * 2. Admin Akademik untuk pengesahan KRS.
             */
            DB::table('users')->updateOrInsert(
                ['username' => 'admin'],
                [
                    'email' => 'admin@ilyasinstitute.ac.id',
                    'password_hash' => Hash::make('AdminIlyas!2026'),
                    'nama' => 'Administrator Akademik',
                    'telepon' => null,
                    'status' => 'aktif',
                    'remember_token' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $adminId = DB::table('users')
                ->where('username', 'admin')
                ->value('id');

            $adminRoleId = DB::table('roles')
                ->where('kode', 'admin_akademik')
                ->value('id');

            DB::table('user_roles')->insertOrIgnore([
                'user_id' => $adminId,
                'role_id' => $adminRoleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            /*
             * 3. Akun dan profil mahasiswa harus sudah tersedia.
             */
            $mahasiswaUserId = DB::table('users')
                ->where('username', 'mahasiswa')
                ->value('id');

            if (! $mahasiswaUserId) {
                throw new RuntimeException(
                    'Akun mahasiswa belum tersedia.'
                );
            }

            $mahasiswaId = DB::table('mahasiswa')
                ->where('user_id', $mahasiswaUserId)
                ->value('id');

            if (! $mahasiswaId) {
                throw new RuntimeException(
                    'Profil mahasiswa belum tersedia.'
                );
            }

            /*
             * 4. Program studi.
             */
            DB::table('program_studi')->updateOrInsert(
                ['kode' => 'PAI'],
                [
                    'nama' => 'Pendidikan Agama Islam',
                    'jenjang' => 'S1',
                    'aktif' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $programStudiId = DB::table('program_studi')
                ->where('kode', 'PAI')
                ->value('id');

            /*
             * 5. Periode akademik.
             */
            DB::table('periode_akademik')->updateOrInsert(
                ['kode' => '2026-GANJIL'],
                [
                    'tahun_mulai' => 2026,
                    'jenis' => 'ganjil',
                    'mulai' => '2026-09-01',
                    'selesai' => '2027-01-31',
                    'krs_mulai' => '2026-09-01 00:00:00',
                    'krs_selesai' => '2026-10-15 23:59:59',
                    'status' => 'aktif',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $periodeId = DB::table('periode_akademik')
                ->where('kode', '2026-GANJIL')
                ->value('id');

            /*
             * 6. Kurikulum.
             */
            DB::table('kurikulum')->updateOrInsert(
                [
                    'program_studi_id' => $programStudiId,
                    'kode' => 'PAI-2026',
                ],
                [
                    'nama' => 'Kurikulum PAI 2026',
                    'tahun_berlaku' => 2026,
                    'status' => 'aktif',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $kurikulumId = DB::table('kurikulum')
                ->where('program_studi_id', $programStudiId)
                ->where('kode', 'PAI-2026')
                ->value('id');

            /*
             * 7. Mata kuliah dan kurikulum mata kuliah.
             */
            $mataKuliah = [
                [
                    'kode' => 'PAI101',
                    'nama' => 'Ulumul Quran',
                    'sks' => '3.0',
                ],
                [
                    'kode' => 'PAI102',
                    'nama' => 'Fiqih Ibadah',
                    'sks' => '3.0',
                ],
                [
                    'kode' => 'PAI103',
                    'nama' => 'Akhlak Tasawuf',
                    'sks' => '2.0',
                ],
            ];

            $kurikulumMataKuliahIds = [];

            foreach ($mataKuliah as $item) {
                DB::table('mata_kuliah')->updateOrInsert(
                    [
                        'program_studi_id' => $programStudiId,
                        'kode' => $item['kode'],
                    ],
                    [
                        'nama' => $item['nama'],
                        'aktif' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $mataKuliahId = DB::table('mata_kuliah')
                    ->where('program_studi_id', $programStudiId)
                    ->where('kode', $item['kode'])
                    ->value('id');

                DB::table('kurikulum_mata_kuliah')->updateOrInsert(
                    [
                        'kurikulum_id' => $kurikulumId,
                        'mata_kuliah_id' => $mataKuliahId,
                    ],
                    [
                        'sks' => $item['sks'],
                        'semester_rekomendasi' => 1,
                        'sifat' => 'wajib',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $kurikulumMataKuliahIds[$item['kode']] =
                    DB::table('kurikulum_mata_kuliah')
                        ->where('kurikulum_id', $kurikulumId)
                        ->where('mata_kuliah_id', $mataKuliahId)
                        ->value('id');
            }

            /*
             * 8. Paket semester.
             */
            DB::table('paket_semester')->updateOrInsert(
                [
                    'kurikulum_id' => $kurikulumId,
                    'semester_studi' => 1,
                    'versi' => 1,
                ],
                [
                    'nama' => 'Paket Semester 1',
                    'status' => 'diterbitkan',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $paketId = DB::table('paket_semester')
                ->where('kurikulum_id', $kurikulumId)
                ->where('semester_studi', 1)
                ->where('versi', 1)
                ->value('id');

            $detailPaketIds = [];

            foreach ($kurikulumMataKuliahIds as $kode => $kmkId) {
                DB::table('detail_paket')->updateOrInsert(
                    [
                        'paket_semester_id' => $paketId,
                        'kurikulum_mata_kuliah_id' => $kmkId,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $detailPaketIds[$kode] = DB::table('detail_paket')
                    ->where('paket_semester_id', $paketId)
                    ->where('kurikulum_mata_kuliah_id', $kmkId)
                    ->value('id');
            }

            /*
             * 9. Rombel.
             */
            DB::table('rombel')->updateOrInsert(
                [
                    'periode_akademik_id' => $periodeId,
                    'kode' => 'PAI-1A',
                ],
                [
                    'paket_semester_id' => $paketId,
                    'kapasitas' => 30,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $rombelId = DB::table('rombel')
                ->where('periode_akademik_id', $periodeId)
                ->where('kode', 'PAI-1A')
                ->value('id');

            /*
             * 10. Riwayat studi.
             * aktif_guard tidak dimasukkan karena generated column.
             */
            DB::table('riwayat_studi')->updateOrInsert(
                [
                    'mahasiswa_id' => $mahasiswaId,
                    'kurikulum_id' => $kurikulumId,
                    'periode_mulai_id' => $periodeId,
                ],
                [
                    'angkatan' => 2026,
                    'periode_akhir_id' => null,
                    'dosen_pa_id' => null,
                    'status' => 'aktif',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $riwayatStudiId = DB::table('riwayat_studi')
                ->where('mahasiswa_id', $mahasiswaId)
                ->where('kurikulum_id', $kurikulumId)
                ->where('periode_mulai_id', $periodeId)
                ->value('id');

            /*
             * 11. Registrasi semester aktif.
             */
            DB::table('registrasi_semester')->updateOrInsert(
                [
                    'riwayat_studi_id' => $riwayatStudiId,
                    'periode_akademik_id' => $periodeId,
                ],
                [
                    'rombel_id' => $rombelId,
                    'semester_studi' => 1,
                    'status' => 'aktif',
                    'alasan_status' => null,
                    'penempatan_dikunci_at' => '2026-09-02 08:00:00',
                    'revisi' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $registrasiId = DB::table('registrasi_semester')
                ->where('riwayat_studi_id', $riwayatStudiId)
                ->where('periode_akademik_id', $periodeId)
                ->value('id');

            /*
             * 12. Kelas kuliah.
             */
            $kelasIds = [];

            foreach ($mataKuliah as $item) {
                DB::table('kelas_kuliah')->updateOrInsert(
                    [
                        'rombel_id' => $rombelId,
                        'detail_paket_id' => $detailPaketIds[$item['kode']],
                    ],
                    [
                        'kode' => 'PAI-1A-' . $item['kode'],
                        'nama_mk_snapshot' => $item['nama'],
                        'sks_snapshot' => $item['sks'],
                        'status' => 'aktif',
                        'revisi' => 1,
                        'diaktifkan_at' => '2026-09-03 08:00:00',
                        'diselesaikan_at' => null,
                        'diarsipkan_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $kelasIds[] = DB::table('kelas_kuliah')
                    ->where('rombel_id', $rombelId)
                    ->where(
                        'detail_paket_id',
                        $detailPaketIds[$item['kode']]
                    )
                    ->value('id');
            }

            /*
             * 13. KRS disahkan.
             */
            DB::table('krs')->updateOrInsert(
                ['registrasi_semester_id' => $registrasiId],
                [
                    'status' => 'disahkan',
                    'versi' => 3,
                    'diajukan_at' => '2026-09-10 08:00:00',
                    'disahkan_oleh' => $adminId,
                    'disahkan_at' => '2026-09-12 09:00:00',
                    'catatan' => 'KRS semester satu telah disahkan.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $krsId = DB::table('krs')
                ->where('registrasi_semester_id', $registrasiId)
                ->value('id');

            foreach ($kelasIds as $kelasId) {
                DB::table('detail_krs')->updateOrInsert(
                    [
                        'krs_id' => $krsId,
                        'kelas_kuliah_id' => $kelasId,
                    ],
                    [
                        'status' => 'aktif',
                        'aktif_at' => '2026-09-12 09:00:00',
                        'batal_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $this->command?->info(
                'Data akademik demo dan KRS mahasiswa berhasil dibuat.'
            );
        });
    }
}