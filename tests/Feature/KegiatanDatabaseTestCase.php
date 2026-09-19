<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Policies\KegiatanPolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

abstract class KegiatanDatabaseTestCase extends TestCase
{
    // Tidak memakai RefreshDatabase dan tidak menjalankan migrate:fresh.
    // Seluruh fixture ada di SQLite :memory: khusus; tidak menyentuh siakad_ilyas.
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Aktifkan pdo_sqlite untuk tes basis data terisolasi.');
        }
        $this->app->detectEnvironment(fn() => 'testing');
        config(['database.default' => 'kegiatan_uji', 'database.connections.kegiatan_uji' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ], 'kegiatan.maks_lampiran_instruksi' => 10, 'siakad.timezone' => 'Asia/Makassar']);
        DB::purge('kegiatan_uji');
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Gate::policy(Kegiatan::class, KegiatanPolicy::class);
        // Fixture minimal untuk kontrak modul, bukan pengganti skema produksi.
        Schema::create('users', function (Blueprint $t): void {
            $t->id();
            $t->string('nama');
            $t->string('status');
        });
        Schema::create('roles', function (Blueprint $t): void {
            $t->id();
            $t->string('kode');
        });
        Schema::create('user_roles', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('role_id');
            $t->timestamps();
        });
        Schema::create('dosen', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('status');
        });
        Schema::create('mahasiswa', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('user_id');
        });
        Schema::create('periode_akademik', function (Blueprint $t): void {
            $t->id();
            $t->string('status');
        });
        Schema::create('rombel', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('periode_akademik_id');
        });
        Schema::create('kelas_kuliah', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('rombel_id');
            $t->string('kode');
            $t->string('nama_mk_snapshot');
            $t->string('status');
        });
        Schema::create('pengajar_kelas', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('kelas_kuliah_id');
            $t->unsignedBigInteger('dosen_id');
            $t->boolean('aktif');
        });
        Schema::create('pertemuan', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('kelas_kuliah_id');
            $t->string('status');
            $t->unsignedSmallInteger('nomor');
            $t->string('topik');
            $t->unique(['id', 'kelas_kuliah_id']);
        });
        Schema::create('riwayat_studi', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('mahasiswa_id');
            $t->string('status');
        });
        Schema::create('registrasi_semester', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('riwayat_studi_id');
            $t->unsignedBigInteger('rombel_id');
            $t->unsignedBigInteger('periode_akademik_id');
            $t->string('status');
        });
        Schema::create('krs', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('registrasi_semester_id');
            $t->string('status');
        });
        Schema::create('detail_krs', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('krs_id');
            $t->unsignedBigInteger('kelas_kuliah_id');
            $t->string('status');
        });
        Schema::create('berkas', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('diunggah_oleh');
            $t->string('status');
            $t->string('pemeriksaan');
            $t->unsignedInteger('revisi');
        });
        Schema::create('audit_log', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('pelaku_id')->nullable();
            $t->string('entitas');
            $t->unsignedBigInteger('entitas_id');
            $t->unsignedInteger('versi_entitas');
            $t->string('aksi');
            $t->json('sebelum')->nullable();
            $t->json('sesudah');
            $t->text('alasan')->nullable();
            $t->dateTime('waktu');
            $t->timestamp('created_at')->nullable();
        });
        $paths = glob(database_path('migrations/*_create_kegiatan_dan_kegiatan_berkas_tables.php'));
        $this->assertCount(1, $paths, 'Harus ada tepat satu migration kegiatan dari panduan.');
        (require $paths[0])->up();
        $this->fixture();
    }
    protected function tearDown(): void
    {
        try {
            DB::purge('kegiatan_uji');
        } finally {
            parent::tearDown();
        }
    }
    private function fixture(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'nama' => 'Admin', 'status' => 'aktif'],
            ['id' => 2, 'nama' => 'Dosen A', 'status' => 'aktif'],
            ['id' => 3, 'nama' => 'Mahasiswa A', 'status' => 'aktif'],
            ['id' => 4, 'nama' => 'Dosen B', 'status' => 'aktif'],
            ['id' => 5, 'nama' => 'Keuangan', 'status' => 'aktif'],
        ]);
        DB::table('roles')->insert([
            ['id' => 1, 'kode' => 'admin_akademik'],
            ['id' => 2, 'kode' => 'dosen'],
            ['id' => 3, 'kode' => 'mahasiswa'],
            ['id' => 4, 'kode' => 'admin_keuangan']
        ]);
        foreach ([1 => 1, 2 => 2, 3 => 3, 4 => 2, 5 => 4] as $user => $role) {
            DB::table('user_roles')->insert(['user_id' => $user, 'role_id' => $role]);
        }
        DB::table('dosen')->insert([['id' => 20, 'user_id' => 2, 'status' => 'aktif'], ['id' => 40, 'user_id' => 4, 'status' => 'aktif']]);
        DB::table('mahasiswa')->insert(['id' => 30, 'user_id' => 3]);
        DB::table('periode_akademik')->insert(['id' => 1, 'status' => 'aktif']);
        DB::table('rombel')->insert(['id' => 1, 'periode_akademik_id' => 1]);
        DB::table('kelas_kuliah')->insert([
            ['id' => 100, 'rombel_id' => 1, 'kode' => 'PAI-A', 'nama_mk_snapshot' => 'Ilmu Hadis', 'status' => 'aktif'],
            ['id' => 101, 'rombel_id' => 1, 'kode' => 'PAI-B', 'nama_mk_snapshot' => 'Fiqh', 'status' => 'aktif'],
        ]);
        DB::table('pengajar_kelas')->insert([
            ['kelas_kuliah_id' => 100, 'dosen_id' => 20, 'aktif' => true],
            ['kelas_kuliah_id' => 101, 'dosen_id' => 40, 'aktif' => true]
        ]);
        DB::table('pertemuan')->insert([
            ['id' => 1, 'kelas_kuliah_id' => 100, 'status' => 'terjadwal', 'nomor' => 1, 'topik' => 'Pembelajaran kelas A'],
            ['id' => 2, 'kelas_kuliah_id' => 101, 'status' => 'terjadwal', 'nomor' => 1, 'topik' => 'Pembelajaran kelas B']
        ]);
        DB::table('riwayat_studi')->insert(['id' => 1, 'mahasiswa_id' => 30, 'status' => 'aktif']);
        DB::table('registrasi_semester')->insert(['id' => 1, 'riwayat_studi_id' => 1, 'rombel_id' => 1, 'periode_akademik_id' => 1, 'status' => 'aktif']);
        DB::table('krs')->insert(['id' => 1, 'registrasi_semester_id' => 1, 'status' => 'disahkan']);
        DB::table('detail_krs')->insert(['id' => 1, 'krs_id' => 1, 'kelas_kuliah_id' => 100, 'status' => 'aktif']);
        DB::table('berkas')->insert([
            ['id' => 1, 'diunggah_oleh' => 2, 'status' => 'tersedia', 'pemeriksaan' => 'format', 'revisi' => 1],
            ['id' => 2, 'diunggah_oleh' => 4, 'status' => 'tersedia', 'pemeriksaan' => 'format', 'revisi' => 1]
        ]);
    }
}
