<?php

namespace Tests\Feature;

use App\Models\Presensi;
use App\Models\User;
use App\Services\AksesPresensiMahasiswa;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PortalPresensiMahasiswaAksesTest extends TestCase
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
            'database.default' => 'portal_presensi_mahasiswa_uji',
            'database.connections.portal_presensi_mahasiswa_uji' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge('portal_presensi_mahasiswa_uji');

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge('portal_presensi_mahasiswa_uji');
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

        Schema::create('presensi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('presensi_pertemuan_id');
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->unsignedBigInteger('detail_krs_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->text('peserta_snapshot')->nullable();
            $table->string('status');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->dateTime('dicatat_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();
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
                'kode' => 'dosen',
            ],
            [
                'id' => 3,
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
                'username' => 'dosen',
                'nama' => 'Dosen Pengajar',
                'status' => 'aktif',
                'password_hash' => 'hash-uji',
            ],
            [
                'id' => 4,
                'username' => 'admin',
                'nama' => 'Admin Akademik',
                'status' => 'aktif',
                'password_hash' => 'hash-uji',
            ],
            [
                'id' => 5,
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
                'role_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5,
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
                'id' => 15,
                'user_id' => 5,
                'nim' => 'M005',
            ],
        ]);

        DB::table('presensi')->insert([
            [
                'id' => 101,
                'presensi_pertemuan_id' => 201,
                'kelas_kuliah_id' => 301,
                'detail_krs_id' => 401,
                'mahasiswa_id' => 11,
                'peserta_snapshot' => json_encode([
                    'nim' => 'M001',
                    'nama' => 'Mahasiswa Satu',
                ], JSON_THROW_ON_ERROR),
                'status' => 'hadir',
                'catatan' => null,
                'dicatat_oleh' => 3,
                'dicatat_at' => '2026-09-10 08:10:00',
                'revisi' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 102,
                'presensi_pertemuan_id' => 202,
                'kelas_kuliah_id' => 301,
                'detail_krs_id' => 401,
                'mahasiswa_id' => 11,
                'peserta_snapshot' => json_encode([
                    'nim' => 'M001',
                    'nama' => 'Mahasiswa Satu',
                ], JSON_THROW_ON_ERROR),
                'status' => 'izin',
                'catatan' => '<script>alert("xss")</script>',
                'dicatat_oleh' => 3,
                'dicatat_at' => '2026-09-11 08:10:00',
                'revisi' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 201,
                'presensi_pertemuan_id' => 203,
                'kelas_kuliah_id' => 302,
                'detail_krs_id' => 402,
                'mahasiswa_id' => 12,
                'peserta_snapshot' => json_encode([
                    'nim' => 'M002',
                    'nama' => 'Mahasiswa Dua',
                ], JSON_THROW_ON_ERROR),
                'status' => 'alpa',
                'catatan' => null,
                'dicatat_oleh' => 3,
                'dicatat_at' => '2026-09-12 08:10:00',
                'revisi' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_mahasiswa_aktif_dengan_profil_dapat_masuk(): void
    {
        $akses = app(AksesPresensiMahasiswa::class);

        $this->assertTrue(
            $akses->masuk(User::findOrFail(1))
        );

        $this->assertFalse(
            $akses->masuk(User::findOrFail(3))
        );

        $this->assertFalse(
            $akses->masuk(User::findOrFail(4))
        );

        $this->assertFalse(
            $akses->masuk(User::findOrFail(5))
        );
    }

    public function test_query_hanya_menampilkan_presensi_sendiri(): void
    {
        $user = User::findOrFail(1);

        $ids = app(AksesPresensiMahasiswa::class)
            ->batasi(Presensi::query(), $user)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertSame([101, 102], $ids);
        $this->assertNotContains(201, $ids);
    }

    public function test_presensi_mahasiswa_lain_tidak_dapat_dibaca(): void
    {
        $akses = app(AksesPresensiMahasiswa::class);
        $user = User::findOrFail(1);

        $this->assertTrue(
            $akses->lihat(
                $user,
                Presensi::findOrFail(101)
            )
        );

        $this->assertFalse(
            $akses->lihat(
                $user,
                Presensi::findOrFail(201)
            )
        );
    }

    public function test_dosen_dan_admin_tidak_mendapat_data_presensi(): void
    {
        $akses = app(AksesPresensiMahasiswa::class);

        $this->assertSame(
            0,
            $akses
                ->batasi(
                    Presensi::query(),
                    User::findOrFail(3)
                )
                ->count()
        );

        $this->assertSame(
            0,
            $akses
                ->batasi(
                    Presensi::query(),
                    User::findOrFail(4)
                )
                ->count()
        );
    }

    public function test_pencabutan_role_langsung_menghentikan_akses(): void
    {
        $user = User::findOrFail(1);
        $akses = app(AksesPresensiMahasiswa::class);

        $this->assertTrue($akses->masuk($user));

        DB::table('user_roles')
            ->where('user_id', 1)
            ->delete();

        $this->assertFalse($akses->masuk($user));

        $this->assertSame(
            0,
            $akses
                ->batasi(Presensi::query(), $user)
                ->count()
        );
    }

    public function test_catatan_berbahaya_tetap_disimpan_sebagai_teks(): void
    {
        $presensi = Presensi::findOrFail(102);

        $this->assertSame(
            '<script>alert("xss")</script>',
            $presensi->catatan
        );

        $hasilBlade = e($presensi->catatan);

        $this->assertStringContainsString(
            '&lt;script&gt;',
            $hasilBlade
        );

        $this->assertStringNotContainsString(
            '<script>',
            $hasilBlade
        );
    }
}