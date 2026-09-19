<?php

namespace Tests\Feature;

use App\Actions\KelolaJenisBiaya;
use App\Models\JenisBiaya;
use App\Models\User;
use App\Policies\JenisBiayaPolicy;
use App\Services\AksesKeuangan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

abstract class JenisBiayaDatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Aktifkan pdo_sqlite untuk tes terisolasi.');
        }
        $this->app->detectEnvironment(fn() => 'testing');
        config(['database.default' => 'jenis_biaya_uji', 'database.connections.jenis_biaya_uji' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ], 'session.driver' => 'array', 'cache.default' => 'array', 'cache.limiter' => 'array', 'siakad.timezone' => 'Asia/Makassar']);
        DB::purge('jenis_biaya_uji');
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
        foreach ([1 => 'Keuangan A', 2 => 'Akademik', 3 => 'Dosen', 4 => 'Mahasiswa', 5 => 'Keuangan nonaktif', 6 => 'Keuangan B'] as $id => $nama) {
            DB::table('users')->insert(['id' => $id, 'nama' => $nama, 'username' => 'uji-' . $id, 'status' => $id === 5 ? 'nonaktif' : 'aktif', 'password_hash' => $hash]);
        }
        foreach ([1 => 'admin_keuangan', 2 => 'admin_akademik', 3 => 'dosen', 4 => 'mahasiswa'] as $id => $kode) {
            DB::table('roles')->insert(['id' => $id, 'kode' => $kode]);
        }
        foreach ([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 1, 6 => 1] as $user => $role) {
            DB::table('user_roles')->insert(['user_id' => $user, 'role_id' => $role]);
        }
        $paths = glob(database_path('migrations/*_create_jenis_biaya_table.php'));
        $this->assertCount(1, $paths, 'Harus ada tepat satu migration jenis_biaya.');
        (require $paths[0])->up();
        Gate::policy(JenisBiaya::class, JenisBiayaPolicy::class);
        Gate::define('akses-jenis-biaya', fn(User $u): bool => app(AksesKeuangan::class)->lihatMaster($u));
    }
    protected function tearDown(): void
    {
        try {
            DB::purge('jenis_biaya_uji');
        } finally {
            parent::tearDown();
        }
    }
    protected function data(array $ganti = []): array
    {
        return array_replace(['kode' => 'SPP', 'nama' => 'SPP Bulanan', 'keterangan' => 'Biaya pendidikan per bulan.', 'form_token' => (string) Str::uuid()], $ganti);
    }
    protected function buat(array $ganti = []): JenisBiaya
    {
        return app(KelolaJenisBiaya::class)->buat(1, $this->data($ganti));
    }
    protected function ubahStatus(
        JenisBiaya $jenisBiaya,
        bool $aktif
    ): JenisBiaya {
        return app(KelolaJenisBiaya::class)->status(
            1,
            $jenisBiaya,
            $aktif,
            [
                'versi' => $jenisBiaya->versiForm(),
                'alasan' => 'Perubahan status untuk pengujian.',
            ]
        );
    }
}
