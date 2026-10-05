<?php

namespace Tests\Feature;

use App\Models\JadwalKuliah;
use App\Models\User;
use App\Services\AksesJadwalMahasiswa;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PortalJadwalAksesTest extends TestCase
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
            'database.default' => 'portal_jadwal_uji',
            'database.connections.portal_jadwal_uji' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge('portal_jadwal_uji');

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge('portal_jadwal_uji');
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
            $table->string('status');
        });

        Schema::create('periode_akademik', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
        });

        Schema::create('rombel', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('periode_akademik_id');
        });

        Schema::create(
            'registrasi_semester',
            function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('riwayat_studi_id');
                $table->unsignedBigInteger('rombel_id');
                $table->unsignedBigInteger('periode_akademik_id');
                $table->string('status');
            }
        );

        Schema::create('kelas_kuliah', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('rombel_id');
            $table->string('status');
        });

        Schema::create('krs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger(
                'registrasi_semester_id'
            );
            $table->string('status');
        });

        Schema::create('detail_krs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('krs_id');
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->string('status');
        });

        Schema::create('jadwal_kuliah', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->boolean('aktif');
        });
    }

    private function buatData(): void
    {
        DB::table('roles')->insert([
            'id' => 1,
            'kode' => 'mahasiswa',
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
                'id' => 13,
                'user_id' => 3,
                'nim' => 'M003',
            ],
        ]);

        DB::table('riwayat_studi')->insert([
            [
                'id' => 21,
                'mahasiswa_id' => 11,
                'status' => 'aktif',
            ],
            [
                'id' => 22,
                'mahasiswa_id' => 12,
                'status' => 'aktif',
            ],
        ]);

        DB::table('periode_akademik')->insert([
            'id' => 31,
            'status' => 'aktif',
        ]);

        DB::table('rombel')->insert([
            [
                'id' => 41,
                'periode_akademik_id' => 31,
            ],
            [
                'id' => 42,
                'periode_akademik_id' => 31,
            ],
        ]);

        DB::table('registrasi_semester')->insert([
            [
                'id' => 51,
                'riwayat_studi_id' => 21,
                'rombel_id' => 41,
                'periode_akademik_id' => 31,
                'status' => 'aktif',
            ],
            [
                'id' => 52,
                'riwayat_studi_id' => 22,
                'rombel_id' => 42,
                'periode_akademik_id' => 31,
                'status' => 'aktif',
            ],
        ]);

        DB::table('kelas_kuliah')->insert([
            [
                'id' => 61,
                'rombel_id' => 41,
                'status' => 'aktif',
            ],
            [
                'id' => 62,
                'rombel_id' => 42,
                'status' => 'aktif',
            ],
        ]);

        DB::table('krs')->insert([
            [
                'id' => 71,
                'registrasi_semester_id' => 51,
                'status' => 'disahkan',
            ],
            [
                'id' => 72,
                'registrasi_semester_id' => 52,
                'status' => 'disahkan',
            ],
        ]);

        DB::table('detail_krs')->insert([
            [
                'id' => 81,
                'krs_id' => 71,
                'kelas_kuliah_id' => 61,
                'status' => 'aktif',
            ],
            [
                'id' => 82,
                'krs_id' => 72,
                'kelas_kuliah_id' => 62,
                'status' => 'aktif',
            ],
        ]);

        DB::table('jadwal_kuliah')->insert([
            [
                'id' => 91,
                'kelas_kuliah_id' => 61,
                'aktif' => true,
            ],
            [
                'id' => 92,
                'kelas_kuliah_id' => 62,
                'aktif' => true,
            ],
            [
                'id' => 93,
                'kelas_kuliah_id' => 61,
                'aktif' => false,
            ],
        ]);
    }

    public function test_mahasiswa_hanya_melihat_jadwal_kelasnya(): void
    {
        $user = User::findOrFail(1);

        $ids = app(AksesJadwalMahasiswa::class)
            ->batasi(JadwalKuliah::query(), $user)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertSame([91], $ids);
        $this->assertNotContains(92, $ids);
        $this->assertNotContains(93, $ids);
    }

    public function test_jadwal_mahasiswa_lain_tidak_dapat_dibaca(): void
    {
        $akses = app(AksesJadwalMahasiswa::class);
        $user = User::findOrFail(1);

        $this->assertTrue(
            $akses->lihat(
                $user,
                JadwalKuliah::findOrFail(91)
            )
        );

        $this->assertFalse(
            $akses->lihat(
                $user,
                JadwalKuliah::findOrFail(92)
            )
        );
    }

    public function test_jadwal_nonaktif_tidak_dapat_dibaca(): void
    {
        $akses = app(AksesJadwalMahasiswa::class);

        $this->assertFalse(
            $akses->lihat(
                User::findOrFail(1),
                JadwalKuliah::findOrFail(93)
            )
        );
    }

    public function test_pencabutan_role_langsung_menghentikan_akses(): void
    {
        $user = User::findOrFail(1);
        $akses = app(AksesJadwalMahasiswa::class);

        $this->assertTrue($akses->masuk($user));

        DB::table('user_roles')
            ->where('user_id', 1)
            ->delete();

        $this->assertFalse($akses->masuk($user));

        $this->assertSame(
            0,
            $akses
                ->batasi(JadwalKuliah::query(), $user)
                ->count()
        );
    }
}