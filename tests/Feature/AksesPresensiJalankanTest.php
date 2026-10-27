<?php

namespace Tests\Feature;

use App\Models\Pertemuan;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AksesPresensiJalankanTest extends TestCase
{
    private string $connection = 'akses_presensi_jalankan_uji';

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped(
                'Aktifkan ekstensi pdo_sqlite untuk pengujian terisolasi.'
            );
        }

        $this->app->detectEnvironment(
            fn(): string => 'testing'
        );

        config([
            'database.default' => $this->connection,
            'database.connections.' . $this->connection => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge($this->connection);

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge($this->connection);
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
            $table->string('email')->nullable();
            $table->string('status');
            $table->string('password_hash');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('kode');
            $table->string('nama')->nullable();
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
        });

        Schema::create('dosen', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('kode_dosen');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create(
            'periode_akademik',
            function (Blueprint $table): void {
                $table->id();
                $table->string('kode');
                $table->string('status');
                $table->timestamps();
            }
        );

        Schema::create('rombel', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('periode_akademik_id');
            $table->string('kode');
            $table->timestamps();
        });

        Schema::create(
            'kelas_kuliah',
            function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('rombel_id');
                $table->string('status');
                $table->timestamps();
            }
        );

        Schema::create('pengajar_kelas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->unsignedBigInteger('dosen_id');
            $table->string('peran');
            $table->boolean('aktif');
            $table->unsignedInteger('revisi')->default(1);
            $table->dateTime('diaktifkan_at')->nullable();
            $table->dateTime('dinonaktifkan_at')->nullable();
            $table->integer('koordinator_aktif')->nullable();
            $table->timestamps();
        });

        Schema::create('pertemuan', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->unsignedBigInteger('pengajar_kelas_id');
            $table->string('status');
            $table->timestamps();
        });
    }

    private function buatData(): void
    {
        $now = now()->toDateTimeString();

        DB::table('roles')->insert([
            'id' => 1,
            'kode' => 'dosen',
            'nama' => 'Dosen',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('users')->insert([
            [
                'id' => 1,
                'username' => 'dosen-penanggung-jawab',
                'nama' => 'Dosen Penanggung Jawab',
                'email' => null,
                'status' => 'aktif',
                'password_hash' => 'hash-uji',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'username' => 'dosen-satu-tim',
                'nama' => 'Dosen Satu Tim',
                'email' => null,
                'status' => 'aktif',
                'password_hash' => 'hash-uji',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'username' => 'dosen-kelas-lain',
                'nama' => 'Dosen Kelas Lain',
                'email' => null,
                'status' => 'aktif',
                'password_hash' => 'hash-uji',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('user_roles')->insert([
            [
                'user_id' => 1,
                'role_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 2,
                'role_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 3,
                'role_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('dosen')->insert([
            [
                'id' => 11,
                'user_id' => 1,
                'kode_dosen' => 'D001',
                'status' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 12,
                'user_id' => 2,
                'kode_dosen' => 'D002',
                'status' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 13,
                'user_id' => 3,
                'kode_dosen' => 'D003',
                'status' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('periode_akademik')->insert([
            [
                'id' => 31,
                'kode' => '2026-GANJIL',
                'status' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('rombel')->insert([
            [
                'id' => 41,
                'periode_akademik_id' => 31,
                'kode' => 'UJI-A',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 42,
                'periode_akademik_id' => 31,
                'kode' => 'UJI-B',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('kelas_kuliah')->insert([
            [
                'id' => 51,
                'rombel_id' => 41,
                'status' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 52,
                'rombel_id' => 42,
                'status' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Dosen 11 penanggung jawab kelas 51.
        // Dosen 12 anggota tim kelas 51, tetapi bukan penanggung jawab sesi.
        // Dosen 13 hanya ditugaskan di kelas 52.
        DB::table('pengajar_kelas')->insert([
            [
                'id' => 101,
                'kelas_kuliah_id' => 51,
                'dosen_id' => 11,
                'peran' => 'koordinator',
                'aktif' => true,
                'revisi' => 1,
                'diaktifkan_at' => $now,
                'dinonaktifkan_at' => null,
                'koordinator_aktif' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 102,
                'kelas_kuliah_id' => 51,
                'dosen_id' => 12,
                'peran' => 'pengajar',
                'aktif' => true,
                'revisi' => 1,
                'diaktifkan_at' => $now,
                'dinonaktifkan_at' => null,
                'koordinator_aktif' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 103,
                'kelas_kuliah_id' => 52,
                'dosen_id' => 13,
                'peran' => 'koordinator',
                'aktif' => true,
                'revisi' => 1,
                'diaktifkan_at' => $now,
                'dinonaktifkan_at' => null,
                'koordinator_aktif' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Pertemuan 201 dimiliki kelas 51 dan penanggung jawabnya dosen 11.
        DB::table('pertemuan')->insert([
            'id' => 201,
            'kelas_kuliah_id' => 51,
            'pengajar_kelas_id' => 101,
            'status' => 'terjadwal',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function test_dosen_penanggung_jawab_diizinkan_menjalankan_pertemuan(): void
    {
        $user = User::query()->findOrFail(1);
        $sesi = Pertemuan::query()->findOrFail(201);

        $this->assertTrue(
            Gate::forUser($user)->allows(
                'jalankan-pertemuan-presensi',
                $sesi
            )
        );
    }

    public function test_dosen_lain_dalam_tim_yang_sama_ditolak_menjalankan_pertemuan(): void
    {
        $user = User::query()->findOrFail(2);
        $sesi = Pertemuan::query()->findOrFail(201);

        $this->assertFalse(
            Gate::forUser($user)->allows(
                'jalankan-pertemuan-presensi',
                $sesi
            )
        );
    }

    public function test_dosen_dari_kelas_lain_ditolak_menjalankan_pertemuan(): void
    {
        $user = User::query()->findOrFail(3);
        $sesi = Pertemuan::query()->findOrFail(201);

        $this->assertFalse(
            Gate::forUser($user)->allows(
                'jalankan-pertemuan-presensi',
                $sesi
            )
        );
    }
}
