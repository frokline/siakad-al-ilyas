<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\User;
use App\Services\AksesKrsMahasiswa;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PortalKrsAksesTest extends TestCase
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
            fn(): string => 'testing'
        );

        config([
            'database.default' => 'portal_krs_uji',
            'database.connections.portal_krs_uji' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge('portal_krs_uji');

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge('portal_krs_uji');
        } finally {
            parent::tearDown();
        }
    }

    private function buatTabel(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username');
            $table->string('nama');
            $table->string('status');
            $table->string('password_hash');
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('kode');
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
        });

        Schema::create('mahasiswa', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('nim');
        });

        Schema::create('riwayat_studi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
        });

        Schema::create(
            'registrasi_semester',
            function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('riwayat_studi_id');
            }
        );

        Schema::create('krs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger(
                'registrasi_semester_id'
            );
            $table->string('status');
            $table->unsignedInteger('versi')->default(1);
            $table->dateTime('diajukan_at')->nullable();
            $table->unsignedBigInteger(
                'disahkan_oleh'
            )->nullable();
            $table->dateTime('disahkan_at')->nullable();
            $table->text('catatan')->nullable();
        });
    }

    private function buatData(): void
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'kode' => 'mahasiswa',
            ],
            [
                'id' => 2,
                'kode' => 'admin_akademik',
            ],
        ]);

        DB::table('users')->insert([
            [
                'id' => 1,
                'username' => 'mahasiswa-1',
                'nama' => 'Mahasiswa Satu',
                'status' => 'aktif',
                'password_hash' => 'hash-uji',
            ],
            [
                'id' => 2,
                'username' => 'mahasiswa-2',
                'nama' => 'Mahasiswa Dua',
                'status' => 'aktif',
                'password_hash' => 'hash-uji',
            ],
            [
                'id' => 3,
                'username' => 'admin',
                'nama' => 'Admin Akademik',
                'status' => 'aktif',
                'password_hash' => 'hash-uji',
            ],
            [
                'id' => 4,
                'username' => 'mahasiswa-nonaktif',
                'nama' => 'Mahasiswa Nonaktif',
                'status' => 'nonaktif',
                'password_hash' => 'hash-uji',
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
            ],
            [
                'id' => 12,
                'user_id' => 2,
                'nim' => 'M002',
            ],
            [
                'id' => 14,
                'user_id' => 4,
                'nim' => 'M004',
            ],
        ]);

        DB::table('riwayat_studi')->insert([
            [
                'id' => 21,
                'mahasiswa_id' => 11,
            ],
            [
                'id' => 22,
                'mahasiswa_id' => 12,
            ],
        ]);

        DB::table('registrasi_semester')->insert([
            [
                'id' => 31,
                'riwayat_studi_id' => 21,
            ],
            [
                'id' => 32,
                'riwayat_studi_id' => 22,
            ],
        ]);

        DB::table('krs')->insert([
            [
                'id' => 101,
                'registrasi_semester_id' => 31,
                'status' => Krs::DISAHKAN,
                'versi' => 3,
                'diajukan_at' => '2026-09-10 08:00:00',
                'disahkan_oleh' => 3,
                'disahkan_at' => '2026-09-12 09:00:00',
                'catatan' => null,
            ],
            [
                'id' => 102,
                'registrasi_semester_id' => 31,
                'status' => Krs::DRAF,
                'versi' => 1,
                'diajukan_at' => null,
                'disahkan_oleh' => null,
                'disahkan_at' => null,
                'catatan' => null,
            ],
            [
                'id' => 201,
                'registrasi_semester_id' => 32,
                'status' => Krs::DISAHKAN,
                'versi' => 3,
                'diajukan_at' => '2026-09-10 08:00:00',
                'disahkan_oleh' => 3,
                'disahkan_at' => '2026-09-12 09:00:00',
                'catatan' => null,
            ],
        ]);
    }

    public function test_mahasiswa_aktif_dengan_profil_dapat_masuk(): void
    {
        $akses = app(AksesKrsMahasiswa::class);

        $this->assertTrue(
            $akses->masuk(User::findOrFail(1))
        );

        $this->assertFalse(
            $akses->masuk(User::findOrFail(3))
        );

        $this->assertFalse(
            $akses->masuk(User::findOrFail(4))
        );
    }

    public function test_query_hanya_menampilkan_krs_milik_sendiri(): void
    {
        $user = User::findOrFail(1);

        $ids = app(AksesKrsMahasiswa::class)
            ->batasi(Krs::query(), $user)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertSame(
            [101, 102],
            $ids
        );

        $this->assertNotContains(201, $ids);
    }

    public function test_mahasiswa_tidak_dapat_melihat_krs_orang_lain(): void
    {
        $akses = app(AksesKrsMahasiswa::class);
        $user = User::findOrFail(1);

        $this->assertTrue(
            $akses->lihat(
                $user,
                Krs::findOrFail(101)
            )
        );

        $this->assertFalse(
            $akses->lihat(
                $user,
                Krs::findOrFail(201)
            )
        );
    }

    public function test_hanya_krs_disahkan_yang_dapat_dicetak(): void
    {
        $akses = app(AksesKrsMahasiswa::class);
        $user = User::findOrFail(1);

        $this->assertTrue(
            $akses->cetak(
                $user,
                Krs::findOrFail(101)
            )
        );

        $this->assertFalse(
            $akses->cetak(
                $user,
                Krs::findOrFail(102)
            )
        );

        $this->assertFalse(
            $akses->cetak(
                $user,
                Krs::findOrFail(201)
            )
        );
    }

    public function test_pencabutan_role_langsung_menghentikan_akses(): void
    {
        $user = User::findOrFail(1);
        $akses = app(AksesKrsMahasiswa::class);

        $this->assertTrue($akses->masuk($user));

        DB::table('user_roles')
            ->where('user_id', 1)
            ->delete();

        $this->assertFalse($akses->masuk($user));

        $this->assertSame(
            0,
            $akses->batasi(Krs::query(), $user)->count()
        );
    }
}