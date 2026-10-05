<?php

namespace Tests\Feature;

use App\Models\KelasKuliah;
use App\Models\User;
use App\Services\AksesKelasDosen;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PortalKelasDosenAksesTest extends TestCase
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
            'database.default' => 'portal_kelas_dosen_uji',
            'database.connections.portal_kelas_dosen_uji' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge('portal_kelas_dosen_uji');

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge('portal_kelas_dosen_uji');
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

        Schema::create('dosen', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('kode_dosen');
            $table->string('status');
        });

        Schema::create(
            'periode_akademik',
            function (Blueprint $table): void {
                $table->id();
                $table->string('kode');
                $table->string('status');
            }
        );

        Schema::create('rombel', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('periode_akademik_id');
            $table->string('kode');
        });

        Schema::create(
            'kelas_kuliah',
            function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('rombel_id');
                $table->unsignedBigInteger('detail_paket_id');
                $table->string('kode');
                $table->string('nama_mk_snapshot');
                $table->decimal('sks_snapshot', 4, 1);
                $table->string('status');
                $table->unsignedInteger('revisi')->default(1);
                $table->dateTime('diaktifkan_at')->nullable();
                $table->dateTime('diselesaikan_at')->nullable();
                $table->dateTime('diarsipkan_at')->nullable();
                $table->timestamps();
            }
        );

        Schema::create(
            'pengajar_kelas',
            function (Blueprint $table): void {
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
            }
        );
        Schema::create(
            'presensi_pertemuan',
            function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('pertemuan_id');
                $table->unsignedBigInteger('kelas_kuliah_id');
                $table->string('status');
                $table->unsignedInteger('jumlah_peserta');
                $table->unsignedBigInteger('dibuka_oleh');
                $table->dateTime('dibuka_at');
                $table->unsignedBigInteger(
                    'ditutup_oleh'
                )->nullable();
                $table->dateTime('ditutup_at')->nullable();
                $table->unsignedInteger('revisi')->default(1);
                $table->timestamps();
            }
        );

        Schema::create('presensi', function (
            Blueprint $table
        ): void {
            $table->id();
            $table->unsignedBigInteger(
                'presensi_pertemuan_id'
            );
            $table->unsignedBigInteger(
                'kelas_kuliah_id'
            );
            $table->unsignedBigInteger('detail_krs_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->json('peserta_snapshot');
            $table->string('status');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger(
                'dicatat_oleh'
            )->nullable();
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
                'kode' => 'dosen',
            ],
            [
                'id' => 2,
                'kode' => 'admin_akademik',
            ],
        ]);

        DB::table('users')->insert([
            [
                'id' => 1,
                'username' => 'dosen-1',
                'nama' => 'Dosen Satu',
                'status' => 'aktif',
                'password_hash' => 'hash-uji',
            ],
            [
                'id' => 2,
                'username' => 'dosen-2',
                'nama' => 'Dosen Dua',
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
                'username' => 'dosen-nonaktif',
                'nama' => 'Dosen Nonaktif',
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

        DB::table('dosen')->insert([
            [
                'id' => 11,
                'user_id' => 1,
                'kode_dosen' => 'D001',
                'status' => 'aktif',
            ],
            [
                'id' => 12,
                'user_id' => 2,
                'kode_dosen' => 'D002',
                'status' => 'aktif',
            ],
            [
                'id' => 14,
                'user_id' => 4,
                'kode_dosen' => 'D004',
                'status' => 'aktif',
            ],
        ]);

        DB::table('periode_akademik')->insert([
            [
                'id' => 21,
                'kode' => '2026-GANJIL',
                'status' => 'aktif',
            ],
            [
                'id' => 22,
                'kode' => '2025-GENAP',
                'status' => 'arsip',
            ],
        ]);

        DB::table('rombel')->insert([
            [
                'id' => 31,
                'periode_akademik_id' => 21,
                'kode' => 'PAI-1A',
            ],
            [
                'id' => 32,
                'periode_akademik_id' => 21,
                'kode' => 'PAI-1B',
            ],
            [
                'id' => 33,
                'periode_akademik_id' => 22,
                'kode' => 'PAI-LAMA',
            ],
        ]);

        DB::table('kelas_kuliah')->insert([
            [
                'id' => 41,
                'rombel_id' => 31,
                'detail_paket_id' => 1,
                'kode' => 'PAI-1A-MK01',
                'nama_mk_snapshot' => 'Mata Kuliah Satu',
                'sks_snapshot' => 3,
                'status' => 'aktif',
                'revisi' => 1,
                'diaktifkan_at' => now(),
                'diselesaikan_at' => null,
                'diarsipkan_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 42,
                'rombel_id' => 32,
                'detail_paket_id' => 2,
                'kode' => 'PAI-1B-MK02',
                'nama_mk_snapshot' => 'Kelas Dosen Lain',
                'sks_snapshot' => 2,
                'status' => 'aktif',
                'revisi' => 1,
                'diaktifkan_at' => now(),
                'diselesaikan_at' => null,
                'diarsipkan_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 43,
                'rombel_id' => 31,
                'detail_paket_id' => 3,
                'kode' => 'PAI-1A-MK03',
                'nama_mk_snapshot' => 'Kelas Selesai',
                'sks_snapshot' => 2,
                'status' => 'selesai',
                'revisi' => 2,
                'diaktifkan_at' => '2026-01-01 08:00:00',
                'diselesaikan_at' => '2026-06-01 08:00:00',
                'diarsipkan_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 44,
                'rombel_id' => 33,
                'detail_paket_id' => 4,
                'kode' => 'PAI-LAMA-MK04',
                'nama_mk_snapshot' => 'Kelas Periode Lama',
                'sks_snapshot' => 2,
                'status' => 'aktif',
                'revisi' => 1,
                'diaktifkan_at' => '2025-01-01 08:00:00',
                'diselesaikan_at' => null,
                'diarsipkan_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('pengajar_kelas')->insert([
            [
                'id' => 51,
                'kelas_kuliah_id' => 41,
                'dosen_id' => 11,
                'peran' => 'koordinator',
                'aktif' => true,
                'revisi' => 1,
                'diaktifkan_at' => now(),
                'dinonaktifkan_at' => null,
                'koordinator_aktif' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 52,
                'kelas_kuliah_id' => 42,
                'dosen_id' => 12,
                'peran' => 'koordinator',
                'aktif' => true,
                'revisi' => 1,
                'diaktifkan_at' => now(),
                'dinonaktifkan_at' => null,
                'koordinator_aktif' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 53,
                'kelas_kuliah_id' => 43,
                'dosen_id' => 11,
                'peran' => 'pengajar',
                'aktif' => true,
                'revisi' => 1,
                'diaktifkan_at' => now(),
                'dinonaktifkan_at' => null,
                'koordinator_aktif' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 54,
                'kelas_kuliah_id' => 44,
                'dosen_id' => 11,
                'peran' => 'pengajar',
                'aktif' => true,
                'revisi' => 1,
                'diaktifkan_at' => now(),
                'dinonaktifkan_at' => null,
                'koordinator_aktif' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 55,
                'kelas_kuliah_id' => 42,
                'dosen_id' => 11,
                'peran' => 'pengajar',
                'aktif' => false,
                'revisi' => 2,
                'diaktifkan_at' => '2026-01-01 08:00:00',
                'dinonaktifkan_at' => '2026-02-01 08:00:00',
                'koordinator_aktif' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_dosen_aktif_dapat_masuk(): void
    {
        $akses = app(AksesKelasDosen::class);

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

    public function test_dosen_hanya_melihat_kelas_penugasannya(): void
    {
        $user = User::findOrFail(1);

        $ids = app(AksesKelasDosen::class)
            ->batasi(KelasKuliah::query(), $user)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertSame([41, 43, 44], $ids);
        $this->assertNotContains(42, $ids);
    }

    public function test_penugasan_nonaktif_tidak_memberi_akses(): void
    {
        $akses = app(AksesKelasDosen::class);
        $user = User::findOrFail(1);

        $this->assertFalse(
            $akses->lihat(
                $user,
                KelasKuliah::findOrFail(42)
            )
        );
    }

    public function test_hanya_kelas_dan_periode_aktif_dapat_dikelola(): void
    {
        $akses = app(AksesKelasDosen::class);
        $user = User::findOrFail(1);

        $this->assertTrue(
            $akses->kelola(
                $user,
                KelasKuliah::findOrFail(41)
            )
        );

        $this->assertFalse(
            $akses->kelola(
                $user,
                KelasKuliah::findOrFail(43)
            )
        );

        $this->assertFalse(
            $akses->kelola(
                $user,
                KelasKuliah::findOrFail(44)
            )
        );
    }

    public function test_pencabutan_role_langsung_menghentikan_akses(): void
    {
        $user = User::findOrFail(1);
        $akses = app(AksesKelasDosen::class);

        $this->assertTrue($akses->masuk($user));

        DB::table('user_roles')
            ->where('user_id', 1)
            ->delete();

        $this->assertFalse($akses->masuk($user));

        $this->assertSame(
            0,
            $akses
                ->batasi(KelasKuliah::query(), $user)
                ->count()
        );
    }

    public function test_status_profil_dosen_dibaca_ulang(): void
    {
        $user = User::findOrFail(1);
        $akses = app(AksesKelasDosen::class);

        $this->assertTrue($akses->masuk($user));

        DB::table('dosen')
            ->where('user_id', 1)
            ->update(['status' => 'nonaktif']);

        $this->assertFalse($akses->masuk($user));

        $this->assertSame(
            0,
            $akses
                ->batasi(KelasKuliah::query(), $user)
                ->count()
        );
    }

    private function buatDataRingkasanPresensi(): void
    {
        $sekarang = now();

        DB::table('presensi_pertemuan')->insert([
            [
                'id' => 701,
                'pertemuan_id' => 801,
                'kelas_kuliah_id' => 41,
                'status' => 'ditutup',
                'jumlah_peserta' => 2,
                'dibuka_oleh' => 1,
                'dibuka_at' => $sekarang->copy()->subHour(),
                'ditutup_oleh' => 1,
                'ditutup_at' => $sekarang,
                'revisi' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 702,
                'pertemuan_id' => 802,
                'kelas_kuliah_id' => 41,
                'status' => 'ditutup',
                'jumlah_peserta' => 1,
                'dibuka_oleh' => 1,
                'dibuka_at' => $sekarang->copy()->subHour(),
                'ditutup_oleh' => 1,
                'ditutup_at' => $sekarang,
                'revisi' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 703,
                'pertemuan_id' => 803,
                'kelas_kuliah_id' => 41,
                'status' => 'terbuka',
                'jumlah_peserta' => 1,
                'dibuka_oleh' => 1,
                'dibuka_at' => $sekarang,
                'ditutup_oleh' => null,
                'ditutup_at' => null,
                'revisi' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 704,
                'pertemuan_id' => 804,
                'kelas_kuliah_id' => 42,
                'status' => 'ditutup',
                'jumlah_peserta' => 1,
                'dibuka_oleh' => 2,
                'dibuka_at' => $sekarang->copy()->subHour(),
                'ditutup_oleh' => 2,
                'ditutup_at' => $sekarang,
                'revisi' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);

        DB::table('presensi')->insert([
            [
                'id' => 901,
                'presensi_pertemuan_id' => 701,
                'kelas_kuliah_id' => 41,
                'detail_krs_id' => 601,
                'mahasiswa_id' => 201,
                'peserta_snapshot' => json_encode([
                    'nim' => '20260001',
                    'nama' => 'Peserta Satu',
                ], JSON_THROW_ON_ERROR),
                'status' => 'hadir',
                'catatan' => null,
                'dicatat_oleh' => 1,
                'dicatat_at' => $sekarang,
                'revisi' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 902,
                'presensi_pertemuan_id' => 701,
                'kelas_kuliah_id' => 41,
                'detail_krs_id' => 602,
                'mahasiswa_id' => 202,
                'peserta_snapshot' => json_encode([
                    'nim' => '20260002',
                    'nama' => 'Peserta Dua',
                ], JSON_THROW_ON_ERROR),
                'status' => 'izin',
                'catatan' => null,
                'dicatat_oleh' => 1,
                'dicatat_at' => $sekarang,
                'revisi' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 903,
                'presensi_pertemuan_id' => 702,
                'kelas_kuliah_id' => 41,
                'detail_krs_id' => 601,
                'mahasiswa_id' => 201,
                'peserta_snapshot' => json_encode([
                    'nim' => '20260001',
                    'nama' => 'Peserta Satu',
                ], JSON_THROW_ON_ERROR),
                'status' => 'hadir',
                'catatan' => null,
                'dicatat_oleh' => 1,
                'dicatat_at' => $sekarang,
                'revisi' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 904,
                'presensi_pertemuan_id' => 703,
                'kelas_kuliah_id' => 41,
                'detail_krs_id' => 602,
                'mahasiswa_id' => 202,
                'peserta_snapshot' => json_encode([
                    'nim' => '20260002',
                    'nama' => 'Peserta Dua',
                ], JSON_THROW_ON_ERROR),
                'status' => 'belum_dicatat',
                'catatan' => null,
                'dicatat_oleh' => null,
                'dicatat_at' => null,
                'revisi' => 1,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
            [
                'id' => 905,
                'presensi_pertemuan_id' => 704,
                'kelas_kuliah_id' => 42,
                'detail_krs_id' => 603,
                'mahasiswa_id' => 203,
                'peserta_snapshot' => json_encode([
                    'nim' => '20260003',
                    'nama' => 'Peserta Kelas Lain',
                ], JSON_THROW_ON_ERROR),
                'status' => 'alpa',
                'catatan' => 'Rahasia kelas lain',
                'dicatat_oleh' => 2,
                'dicatat_at' => $sekarang,
                'revisi' => 2,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ],
        ]);
    }

    public function test_ringkasan_presensi_kelas_dihitung_dengan_benar(): void
    {
        $this->buatDataRingkasanPresensi();

        $ringkasan = app(
            \App\Services\RingkasanPresensiKelasDosen::class
        )->ambil(
            \App\Models\User::findOrFail(1),
            \App\Models\KelasKuliah::findOrFail(41)
        );

        $this->assertSame(3, $ringkasan['daftar']['total']);
        $this->assertSame(1, $ringkasan['daftar']['terbuka']);
        $this->assertSame(2, $ringkasan['daftar']['ditutup']);
        $this->assertSame(4, $ringkasan['jumlah_baris']);
        $this->assertSame(
            2,
            $ringkasan['jumlah_peserta_tercatat']
        );
        $this->assertSame(2, $ringkasan['status']['hadir']);
        $this->assertSame(1, $ringkasan['status']['izin']);
        $this->assertSame(
            1,
            $ringkasan['status']['belum_dicatat']
        );
        $this->assertSame(
            50.0,
            $ringkasan['persentase_hadir']
        );
    }

    public function test_ringkasan_tidak_membaca_presensi_kelas_lain(): void
    {
        $this->buatDataRingkasanPresensi();

        $ringkasan = app(
            \App\Services\RingkasanPresensiKelasDosen::class
        )->ambil(
            \App\Models\User::findOrFail(1),
            \App\Models\KelasKuliah::findOrFail(41)
        );

        $this->assertSame(0, $ringkasan['status']['alpa']);
        $this->assertSame(4, $ringkasan['jumlah_baris']);
    }

    public function test_ringkasan_kelas_dosen_lain_ditolak(): void
    {
        $this->buatDataRingkasanPresensi();

        $this->expectException(
            \Illuminate\Database\Eloquent\ModelNotFoundException::class
        );

        app(
            \App\Services\RingkasanPresensiKelasDosen::class
        )->ambil(
            \App\Models\User::findOrFail(1),
            \App\Models\KelasKuliah::findOrFail(42)
        );
    }
}
