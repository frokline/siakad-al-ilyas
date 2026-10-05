<?php

namespace Tests\Feature;

use App\Actions\PerbaruiProfilMahasiswa;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Services\AksesProfilMahasiswa;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PortalProfilMahasiswaAksesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped(
                'Aktifkan pdo_sqlite untuk pengujian terisolasi.'
            );
        }

        $this->app->detectEnvironment(
            fn (): string => 'testing'
        );

        config([
            'database.default' => 'portal_profil_mahasiswa_uji',
            'database.connections.portal_profil_mahasiswa_uji' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge('portal_profil_mahasiswa_uji');

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge('portal_profil_mahasiswa_uji');
        } finally {
            parent::tearDown();
        }
    }

    private function buatTabel(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->string('password_hash');
            $table->string('nama');
            $table->string('telepon')->nullable();
            $table->string('status');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('mahasiswa', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('nim')->unique();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    private function buatData(): void
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'kode' => 'mahasiswa',
                'nama' => 'Mahasiswa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'kode' => 'admin_akademik',
                'nama' => 'Admin Akademik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('users')->insert([
            [
                'id' => 1,
                'username' => 'mahasiswa-1',
                'email' => 'mahasiswa1@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Mahasiswa Satu',
                'telepon' => '081111111111',
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'username' => 'mahasiswa-2',
                'email' => 'mahasiswa2@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Mahasiswa Dua',
                'telepon' => '082222222222',
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'username' => 'admin',
                'email' => 'admin@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Admin Akademik',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'username' => 'mahasiswa-nonaktif',
                'email' => 'nonaktif@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Mahasiswa Nonaktif',
                'telepon' => '084444444444',
                'status' => 'nonaktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('user_roles')->insert([
            [
                'user_id' => 1,
                'role_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'role_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'role_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'role_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('mahasiswa')->insert([
            [
                'id' => 11,
                'user_id' => 1,
                'nim' => 'M001',
                'tempat_lahir' => 'Banjarmasin',
                'tanggal_lahir' => '2005-01-01',
                'jenis_kelamin' => 'L',
                'alamat' => 'Alamat Lama Satu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'user_id' => 2,
                'nim' => 'M002',
                'tempat_lahir' => 'Banjarbaru',
                'tanggal_lahir' => '2005-02-02',
                'jenis_kelamin' => 'P',
                'alamat' => 'Alamat Lama Dua',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'user_id' => 4,
                'nim' => 'M004',
                'tempat_lahir' => null,
                'tanggal_lahir' => null,
                'jenis_kelamin' => 'L',
                'alamat' => 'Alamat Nonaktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_mahasiswa_hanya_dapat_melihat_profil_sendiri(): void
    {
        $akses = app(AksesProfilMahasiswa::class);
        $user = User::findOrFail(1);

        $this->assertTrue(
            $akses->lihat(
                $user,
                Mahasiswa::findOrFail(11)
            )
        );

        $this->assertFalse(
            $akses->lihat(
                $user,
                Mahasiswa::findOrFail(12)
            )
        );
    }

    public function test_telepon_dan_alamat_dapat_diperbarui(): void
    {
        $user = User::findOrFail(1);

        $hasil = app(PerbaruiProfilMahasiswa::class)
            ->jalankan($user, [
                'telepon' => '+62 812 3456 7890',
                'alamat' => 'Alamat Baru Mahasiswa Satu',
            ]);

        $this->assertSame(11, $hasil->id);

        $this->assertDatabaseHas('users', [
            'id' => 1,
            'telepon' => '+62 812 3456 7890',
        ]);

        $this->assertDatabaseHas('mahasiswa', [
            'id' => 11,
            'user_id' => 1,
            'nim' => 'M001',
            'alamat' => 'Alamat Baru Mahasiswa Satu',
        ]);
    }

    public function test_kolom_akademik_palsu_ditolak(): void
    {
        try {
            app(PerbaruiProfilMahasiswa::class)
                ->jalankan(User::findOrFail(1), [
                    'telepon' => '081234567890',
                    'alamat' => 'Alamat Baru',
                    'nim' => 'NIM-PALSU',
                    'status' => 'selesai',
                    'user_id' => 2,
                ]);

            $this->fail(
                'Kolom akademik palsu seharusnya ditolak.'
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'profil',
                $exception->errors()
            );
        }

        $this->assertDatabaseHas('mahasiswa', [
            'id' => 11,
            'user_id' => 1,
            'nim' => 'M001',
            'alamat' => 'Alamat Lama Satu',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => 1,
            'telepon' => '081111111111',
        ]);
    }

    public function test_pencabutan_role_membatalkan_perubahan(): void
    {
        $user = User::findOrFail(1);

        DB::table('user_roles')
            ->where('user_id', 1)
            ->delete();

        $this->expectException(
            AuthorizationException::class
        );

        try {
            app(PerbaruiProfilMahasiswa::class)
                ->jalankan($user, [
                    'telepon' => '089999999999',
                    'alamat' => 'Alamat Tidak Sah',
                ]);
        } finally {
            $this->assertDatabaseHas('users', [
                'id' => 1,
                'telepon' => '081111111111',
            ]);

            $this->assertDatabaseHas('mahasiswa', [
                'id' => 11,
                'alamat' => 'Alamat Lama Satu',
            ]);
        }
    }

    public function test_akun_nonaktif_tidak_dapat_memperbarui_profil(): void
    {
        $this->expectException(
            AuthorizationException::class
        );

        try {
            app(PerbaruiProfilMahasiswa::class)
                ->jalankan(User::findOrFail(4), [
                    'telepon' => '087777777777',
                    'alamat' => 'Alamat Tidak Sah',
                ]);
        } finally {
            $this->assertDatabaseHas('users', [
                'id' => 4,
                'telepon' => '084444444444',
            ]);

            $this->assertDatabaseHas('mahasiswa', [
                'id' => 14,
                'alamat' => 'Alamat Nonaktif',
            ]);
        }
    }

    public function test_role_dibaca_ulang_meski_model_sudah_dimiliki(): void
    {
        $user = User::findOrFail(1);
        $akses = app(AksesProfilMahasiswa::class);

        $this->assertTrue($akses->masuk($user));

        DB::table('user_roles')
            ->where('user_id', 1)
            ->delete();

        $this->assertFalse($akses->masuk($user));

        $this->assertSame(
            0,
            $akses
                ->batasi(Mahasiswa::query(), $user)
                ->count()
        );
    }
}