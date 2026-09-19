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

abstract class TagihanDatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Aktifkan pdo_sqlite untuk tes terisolasi.');
        }
        $this->app->detectEnvironment(fn() => 'testing');
        config(['database.default' => 'tagihan_uji', 'database.connections.tagihan_uji' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ], 'session.driver' => 'array', 'cache.default' => 'array', 'cache.limiter' => 'array', 'siakad.timezone' => 'Asia/Makassar']);
        DB::purge('tagihan_uji');
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
            $t->string('nim');
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
        Schema::create('riwayat_studi', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('mahasiswa_id');
        });
        Schema::create('registrasi_semester', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('riwayat_studi_id');
            $t->unsignedBigInteger('periode_akademik_id');
            $t->unsignedInteger('semester_studi');
            $t->string('status');
        });
        DB::table('mahasiswa')->insert([['id' => 1, 'user_id' => 4, 'nim' => 'M001'], ['id' => 2, 'user_id' => 3, 'nim' => 'M002']]);
        DB::table('riwayat_studi')->insert([['id' => 1, 'mahasiswa_id' => 1], ['id' => 2, 'mahasiswa_id' => 2]]);
        DB::table('registrasi_semester')->insert([
            ['id' => 1, 'riwayat_studi_id' => 1, 'periode_akademik_id' => 1, 'semester_studi' => 1, 'status' => 'aktif'],
            ['id' => 2, 'riwayat_studi_id' => 2, 'periode_akademik_id' => 1, 'semester_studi' => 1, 'status' => 'terdaftar'],
            ['id' => 3, 'riwayat_studi_id' => 1, 'periode_akademik_id' => 2, 'semester_studi' => 2, 'status' => 'aktif'],
        ]);
        app(KelolaJenisBiaya::class)->buat(1, ['kode' => 'SPP', 'nama' => 'SPP Bulanan', 'form_token' => (string) Str::uuid()]);
        $paths = glob(database_path('migrations/*_create_tagihan_table.php'));
        $this->assertCount(1, $paths);
        (require $paths[0])->up();
        Gate::policy(\App\Models\Tagihan::class, \App\Policies\TagihanPolicy::class);
        Gate::define('akses-tagihan', fn(User $u): bool => app(\App\Services\AksesTagihan::class)->masuk($u));
    }
    protected function tearDown(): void
    {
        try {
            DB::purge('tagihan_uji');
        } finally {
            parent::tearDown();
        }
    }
    protected function data(array $ubah = []): array
    {
        return array_replace([
            'registrasi_semester_id' => 1,
            'jenis_biaya_id' => 1,
            'tahun_tagihan' => 2026,
            'bulan_tagihan' => 9,
            'nominal' => '250000.00',
            'jatuh_tempo' => '2026-09-30',
            'catatan' => null,
            'form_token' => (string) Str::uuid(),
            'alasan' => 'Pencatatan SPP bulan September.'
        ], $ubah);
    }
    protected function buat(array $ubah = []): \App\Models\Tagihan
    {
        return app(\App\Actions\KelolaTagihan::class)->buat(1, $this->data($ubah));
    }
    protected function transisi(\App\Models\Tagihan $t, string $aksi): \App\Models\Tagihan
    {
        return app(\App\Actions\KelolaTagihan::class)->transisi(
            1,
            $t,
            $aksi,
            ['versi' => $t->versiForm(), 'alasan' => 'Tindakan keuangan untuk pengujian.']
        );
    }
}
