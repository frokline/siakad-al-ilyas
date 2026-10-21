<?php

namespace Tests\Feature;

use App\Actions\PerbaruiKataSandiMahasiswa;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PortalKataSandiMahasiswaTest extends TestCase
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
            'database.default' => 'portal_kata_sandi_mahasiswa_uji',
            'database.connections.portal_kata_sandi_mahasiswa_uji' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge('portal_kata_sandi_mahasiswa_uji');

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge('portal_kata_sandi_mahasiswa_uji');
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
                'password_hash' => Hash::make('SandiLama#2026'),
                'nama' => 'Mahasiswa Satu',
                'telepon' => '081111111111',
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'username' => 'admin',
                'email' => 'admin@example.test',
                'password_hash' => Hash::make('SandiAdmin#2026'),
                'nama' => 'Admin Akademik',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'username' => 'mahasiswa-nonaktif',
                'email' => 'nonaktif@example.test',
                'password_hash' => Hash::make('SandiLama#2026'),
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
                'role_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
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
                'alamat' => 'Alamat Lama Satu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_kata_sandi_berhasil_diganti(): void
    {
        $user = User::findOrFail(1);

        app(PerbaruiKataSandiMahasiswa::class)
            ->jalankan($user, [
                'current_password' => 'SandiLama#2026',
                'password' => 'SandiBaru#2026',
            ]);

        $hashBaru = DB::table('users')
            ->where('id', 1)
            ->value('password_hash');

        $this->assertNotSame(
            'SandiBaru#2026',
            $hashBaru
        );

        $this->assertTrue(
            Hash::check('SandiBaru#2026', $hashBaru)
        );
    }

    public function test_kata_sandi_saat_in_salah_ditolak(): void
    {
        $hashLama = DB::table('users')
            ->where('id', 1)
            ->value('password_hash');

        try {
            app(PerbaruiKataSandiMahasiswa::class)
                ->jalankan(User::findOrFail(1), [
                    'current_password' => 'SandiSalah#2026',
                    'password' => 'SandiBaru#2026',
                ]);

            $this->fail(
                'Kata sandi saat ini yang salah seharusnya ditolak.'
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'current_password',
                $exception->errors()
            );
        }

        $this->assertSame(
            $hashLama,
            DB::table('users')
                ->where('id', 1)
                ->value('password_hash')
        );
    }

    public function test_akun_nonaktif_tidak_dapat_mengganti_kata_sandi(): void
    {
        $this->expectException(
            AuthorizationException::class
        );

        app(PerbaruiKataSandiMahasiswa::class)
            ->jalankan(User::findOrFail(3), [
                'current_password' => 'SandiLama#2026',
                'password' => 'SandiBaru#2026',
            ]);
    }

    public function test_peran_mahasiswa_dicabut_otomatis_menggagalkan(): void
    {
        DB::table('user_roles')
            ->where('user_id', 1)
            ->delete();

        $this->expectException(
            AuthorizationException::class
        );

        app(PerbaruiKataSandiMahasiswa::class)
            ->jalankan(User::findOrFail(1), [
                'current_password' => 'SandiLama#2026',
                'password' => 'SandiBaru#2026',
            ]);
    }
}
