<?php

namespace Tests\Feature;

use App\Actions\KelolaPresensi;
use App\Models\Pertemuan;
use App\Models\Presensi;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PresensiBatchKeamananTest extends TestCase
{
    private string $connection = 'presensi_batch_uji';

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
            $table->string('status');
            $table->timestamps();
        });

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
                $table->unsignedBigInteger('ditutup_oleh')->nullable();
                $table->dateTime('ditutup_at')->nullable();
                $table->unsignedInteger('revisi')->default(1);
                $table->timestamps();
            }
        );

        Schema::create('presensi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('presensi_pertemuan_id');
            $table->unsignedBigInteger('kelas_kuliah_id');
            $table->unsignedBigInteger('detail_krs_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->json('peserta_snapshot');
            $table->string('status');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->dateTime('dicatat_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();
        });

        Schema::create('audit_log', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pelaku_id');
            $table->string('entitas');
            $table->unsignedBigInteger('entitas_id');
            $table->unsignedInteger('versi_entitas');
            $table->string('aksi');
            $table->json('sebelum')->nullable();
            $table->json('sesudah')->nullable();
            $table->text('alasan')->nullable();
            $table->dateTime('waktu');
            $table->timestamp('created_at')->nullable();
        });
    }

    private function buatData(): void
    {
        $now = now()->toDateTimeString();

        DB::table('users')->insert([
            'id' => 1,
            'username' => 'admin-uji',
            'nama' => 'Admin Pengujian',
            'status' => 'aktif',
            'password_hash' => 'hash-uji',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('roles')->insert([
            'id' => 1,
            'kode' => 'admin_akademik',
            'nama' => 'Admin Akademik',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('user_roles')->insert([
            'id' => 1,
            'user_id' => 1,
            'role_id' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('periode_akademik')->insert([
            'id' => 1,
            'kode' => '2026-GANJIL',
            'status' => 'aktif',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('rombel')->insert([
            'id' => 1,
            'periode_akademik_id' => 1,
            'kode' => 'UJI-1A',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('kelas_kuliah')->insert([
            'id' => 1,
            'rombel_id' => 1,
            'detail_paket_id' => 1,
            'kode' => 'UJI-MK01',
            'nama_mk_snapshot' => 'Mata Kuliah Uji',
            'sks_snapshot' => 3,
            'status' => 'aktif',
            'revisi' => 1,
            'diaktifkan_at' => $now,
            'diselesaikan_at' => null,
            'diarsipkan_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('pengajar_kelas')->insert([
            'id' => 1,
            'kelas_kuliah_id' => 1,
            'dosen_id' => 1,
            'peran' => 'koordinator',
            'aktif' => true,
            'revisi' => 1,
            'diaktifkan_at' => $now,
            'dinonaktifkan_at' => null,
            'koordinator_aktif' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Dua pertemuan berbeda, tetapi berada di kelas yang sama.
        DB::table('pertemuan')->insert([
            [
                'id' => 101,
                'kelas_kuliah_id' => 1,
                'status' => 'berlangsung',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 102,
                'kelas_kuliah_id' => 1,
                'status' => 'berlangsung',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('presensi_pertemuan')->insert([
            [
                'id' => 201,
                'pertemuan_id' => 101,
                'kelas_kuliah_id' => 1,
                'status' => 'terbuka',
                'jumlah_peserta' => 1,
                'dibuka_oleh' => 1,
                'dibuka_at' => $now,
                'ditutup_oleh' => null,
                'ditutup_at' => null,
                'revisi' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 202,
                'pertemuan_id' => 102,
                'kelas_kuliah_id' => 1,
                'status' => 'terbuka',
                'jumlah_peserta' => 1,
                'dibuka_oleh' => 1,
                'dibuka_at' => $now,
                'ditutup_oleh' => null,
                'ditutup_at' => null,
                'revisi' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('presensi')->insert([
            [
                'id' => 301,
                'presensi_pertemuan_id' => 201,
                'kelas_kuliah_id' => 1,
                'detail_krs_id' => 401,
                'mahasiswa_id' => 501,
                'peserta_snapshot' => json_encode([
                    'nim' => 'UJI001',
                    'nama' => 'Mahasiswa Pertemuan A',
                ]),
                'status' => 'belum_dicatat',
                'catatan' => null,
                'dicatat_oleh' => null,
                'dicatat_at' => null,
                'revisi' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 302,
                'presensi_pertemuan_id' => 202,
                'kelas_kuliah_id' => 1,
                'detail_krs_id' => 402,
                'mahasiswa_id' => 502,
                'peserta_snapshot' => json_encode([
                    'nim' => 'UJI002',
                    'nama' => 'Mahasiswa Pertemuan B',
                ]),
                'status' => 'belum_dicatat',
                'catatan' => null,
                'dicatat_oleh' => null,
                'dicatat_at' => null,
                'revisi' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function test_batch_menolak_id_presensi_dari_pertemuan_lain(): void
    {
        $pertemuanA = Pertemuan::query()->findOrFail(101);
        $presensiB = Presensi::query()->findOrFail(302);

        // Gunakan token versi valid agar test benar-benar menguji lingkup pertemuan.
        $tokenB = $presensiB->versiForm();

        $sebelum = DB::table('presensi')->where('id', 302)->first();
        $auditSebelum = DB::table('audit_log')
            ->where('entitas', 'presensi')
            ->where('entitas_id', 302)
            ->count();

        $hasil = app(KelolaPresensi::class)->catatBatch(
            1,
            $pertemuanA,
            [[
                'id' => 302,
                'status' => 'hadir',
                'catatan' => null,
                'versi_presensi' => $tokenB,
            ]]
        );

        $sesudah = DB::table('presensi')->where('id', 302)->first();
        $auditSesudah = DB::table('audit_log')
            ->where('entitas', 'presensi')
            ->where('entitas_id', 302)
            ->count();

        $this->assertSame(1, $hasil['failed']);
        $this->assertSame(0, $hasil['saved']);
        $this->assertSame('not_found', $hasil['results'][0]['code']);
        $this->assertFalse($hasil['results'][0]['success']);
        $this->assertTrue($hasil['results'][0]['needs_refresh']);

        $this->assertSame($sebelum->status, $sesudah->status);
        $this->assertSame((int) $sebelum->revisi, (int) $sesudah->revisi);
        $this->assertSame($sebelum->catatan, $sesudah->catatan);
        $this->assertSame($auditSebelum, $auditSesudah);
    }

    public function test_batch_dapat_mencatat_peserta_dalam_pertemuan_yang_sesuai(): void
    {
        $pertemuanA = Pertemuan::query()->findOrFail(101);
        $presensiA = Presensi::query()->findOrFail(301);

        $hasil = app(KelolaPresensi::class)->catatBatch(
            1,
            $pertemuanA,
            [[
                'id' => 301,
                'status' => 'hadir',
                'catatan' => null,
                'versi_presensi' => $presensiA->versiForm(),
            ]]
        );

        $sesudah = DB::table('presensi')->where('id', 301)->first();

        $this->assertSame(1, $hasil['saved']);
        $this->assertSame(0, $hasil['failed']);
        $this->assertSame('saved', $hasil['results'][0]['code']);
        $this->assertSame('hadir', $sesudah->status);
        $this->assertSame(2, (int) $sesudah->revisi);

        $this->assertSame(
            1,
            DB::table('audit_log')
                ->where('entitas', 'presensi')
                ->where('entitas_id', 301)
                ->count()
        );
    }
}
