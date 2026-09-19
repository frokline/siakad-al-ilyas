<?php

namespace Tests\Feature;

use App\Actions\KelolaJenisSurat;
use App\Models\JenisSurat;
use App\Models\User;
use App\Policies\JenisSuratPolicy;
use App\Services\AksesJenisSurat;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

abstract class JenisSuratDatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Aktifkan pdo_sqlite untuk tes terisolasi.');
        }
        $this->app->detectEnvironment(fn() => 'testing');
        config(['database.default' => 'jenis_surat_uji', 'database.connections.jenis_surat_uji' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ], 'session.driver' => 'array', 'cache.default' => 'array', 'cache.limiter' => 'array', 'siakad.timezone' => 'Asia/Makassar']);
        DB::purge('jenis_surat_uji');
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        // Fixture minimal. Tidak memakai RefreshDatabase atau migrate:fresh pada proyek.
        Schema::create('users', function (Blueprint $t): void {
            $t->id();
            $t->string('nama');
            $t->string('username');
            $t->string('status');
            $t->string('password_hash');
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
        // Diperlukan oleh pemeriksaan menu login Berkas yang sudah ada.
        Schema::create('dosen', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('status');
        });
        Schema::create('mahasiswa', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('user_id');
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
            $t->unique(['entitas', 'entitas_id', 'versi_entitas']);
        });
        $hash = password_hash('Hanya-Untuk-Fixture-Jenis-Biaya', PASSWORD_BCRYPT);
        foreach ([1 => 'Akademik A', 2 => 'Keuangan', 3 => 'Dosen', 4 => 'Mahasiswa', 5 => 'Akademik nonaktif', 6 => 'Akademik B'] as $id => $nama) {
            DB::table('users')->insert(['id' => $id, 'nama' => $nama, 'username' => 'uji-' . $id, 'status' => $id === 5 ? 'nonaktif' : 'aktif', 'password_hash' => $hash]);
        }
        foreach ([1 => 'admin_akademik', 2 => 'admin_keuangan', 3 => 'dosen', 4 => 'mahasiswa'] as $id => $kode) {
            DB::table('roles')->insert(['id' => $id, 'kode' => $kode]);
        }
        foreach ([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 1, 6 => 1] as $user => $role) {
            DB::table('user_roles')->insert(['user_id' => $user, 'role_id' => $role]);
        }
        $paths = glob(database_path('migrations/*_create_jenis_surat_table.php'));
        $this->assertCount(1, $paths, 'Harus ada tepat satu migration jenis_surat.');
        (require $paths[0])->up();
        Gate::policy(JenisSurat::class, JenisSuratPolicy::class);
        Gate::define('akses-jenis-surat', fn(User $u): bool => app(AksesJenisSurat::class)->lihatMaster($u));
    }
    protected function tearDown(): void
    {
        try {
            DB::purge('jenis_surat_uji');
        } finally {
            parent::tearDown();
        }
    }
    protected function data(array $ganti = []): array
    {
        return array_replace(['kode' => 'AKTIF_KULIAH', 'nama' => 'Surat Aktif Kuliah', 'syarat' => 'Cantumkan tujuan penggunaan surat.', 'form_token' => (string) Str::uuid()], $ganti);
    }
    protected function buat(array $ganti = []): JenisSurat
    {
        return app(KelolaJenisSurat::class)->buat(1, $this->data($ganti));
    }
    protected function ubahStatus(JenisSurat $j, bool $aktif): JenisSurat
    {
        return app(KelolaJenisSurat::class)->status(1, $j, $aktif, ['versi' => $j->versiForm(), 'alasan' => 'Perubahan status untuk pengujian.', 'konfirmasi' => '1']);
    }
}
