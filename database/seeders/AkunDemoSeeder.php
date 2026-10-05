<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AkunDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            $roles = [
                'mahasiswa' => 'Mahasiswa',
                'dosen' => 'Dosen',
                'admin_akademik' => 'Admin Akademik',
                'admin_keuangan' => 'Admin Keuangan',
            ];

            foreach ($roles as $kode => $nama) {
                DB::table('roles')->updateOrInsert(
                    ['kode' => $kode],
                    [
                        'nama' => $nama,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $akun = [
                [
                    'username' => 'mahasiswa',
                    'email' => 'mahasiswa@ilyasinstitute.ac.id',
                    'password' => 'Mahasiswa!2026',
                    'nama' => 'Ahmad Fauzan',
                    'role' => 'mahasiswa',
                ],
                [
                    'username' => 'dosen',
                    'email' => 'dosen@ilyasinstitute.ac.id',
                    'password' => 'Dosen!2026',
                    'nama' => 'Muhammad Afdhal',
                    'role' => 'dosen',
                ],
                [
                    'username' => 'admin',
                    'email' => 'admin@ilyasinstitute.ac.id',
                    'password' => 'AdminIlyas!2026',
                    'nama' => 'Administrator Akademik',
                    'role' => 'admin_akademik',
                ],
                [
                    'username' => 'keuangan',
                    'email' => 'keuangan@ilyasinstitute.ac.id',
                    'password' => 'Keuangan!2026',
                    'nama' => 'Administrator Keuangan',
                    'role' => 'admin_keuangan',
                ],
            ];

            foreach ($akun as $data) {
                DB::table('users')->updateOrInsert(
                    ['username' => $data['username']],
                    [
                        'email' => $data['email'],
                        'password_hash' => Hash::make($data['password']),
                        'nama' => $data['nama'],
                        'telepon' => null,
                        'status' => 'aktif',
                        'remember_token' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $userId = DB::table('users')
                    ->where('username', $data['username'])
                    ->value('id');

                $roleId = DB::table('roles')
                    ->where('kode', $data['role'])
                    ->value('id');

                DB::table('user_roles')->insertOrIgnore([
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $mahasiswaUserId = DB::table('users')
                ->where('username', 'mahasiswa')
                ->value('id');

            DB::table('mahasiswa')->updateOrInsert(
                ['user_id' => $mahasiswaUserId],
                [
                    'nim' => '20260001',
                    'tempat_lahir' => 'Banjarmasin',
                    'tanggal_lahir' => '2005-01-01',
                    'jenis_kelamin' => 'L',
                    'alamat' => 'Banjarmasin',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $dosenUserId = DB::table('users')
                ->where('username', 'dosen')
                ->value('id');

            DB::table('dosen')->updateOrInsert(
                ['user_id' => $dosenUserId],
                [
                    'kode_dosen' => 'DSN001',
                    'status' => 'aktif',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $this->command?->info(
                'Akun demo, role, profil mahasiswa, dan profil dosen berhasil dibuat.'
            );
        });
    }
}
