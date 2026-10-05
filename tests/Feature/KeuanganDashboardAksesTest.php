<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TujuanPortal;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KeuanganDashboardAksesTest extends TestCase
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
            'database.default' => 'keuangan_dashboard_uji',
            'database.connections.keuangan_dashboard_uji' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge('keuangan_dashboard_uji');

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge('keuangan_dashboard_uji');
        } finally {
            parent::tearDown();
        }
    }

    private function buatTabel(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->string('password_hash');
            $table->string('nama');
            $table->string('telepon')->nullable();
            $table->string('status');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('mahasiswa', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('nim')->unique();
            $table->timestamps();
        });

        Schema::create('dosen', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('kode_dosen')->unique();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('jenis_biaya', function (Blueprint $table): void {
            $table->id();
            $table->boolean('aktif')->default(true);
        });

        Schema::create('tagihan', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('nominal');
            $table->string('status');
        });

        Schema::create('pembayaran', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('nominal_diajukan');
            $table->string('status');
        });
    }

    private function buatData(): void
    {
        $waktu = now();

        DB::table('roles')->insert([
            [
                'id' => 1,
                'kode' => 'admin_keuangan',
                'nama' => 'Admin Keuangan',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 2,
                'kode' => 'admin_akademik',
                'nama' => 'Admin Akademik',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 3,
                'kode' => 'dosen',
                'nama' => 'Dosen',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 4,
                'kode' => 'mahasiswa',
                'nama' => 'Mahasiswa',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
        ]);

        DB::table('users')->insert([
            [
                'id' => 1,
                'username' => 'keuangan',
                'email' => 'keuangan@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Administrator Keuangan',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 2,
                'username' => 'akademik',
                'email' => 'akademik@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Administrator Akademik',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 3,
                'username' => 'dosen',
                'email' => 'dosen@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Dosen Aktif',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 4,
                'username' => 'mahasiswa',
                'email' => 'mahasiswa@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Mahasiswa Aktif',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 5,
                'username' => 'keuangan-nonaktif',
                'email' => 'nonaktif@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Keuangan Nonaktif',
                'telepon' => null,
                'status' => 'nonaktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 6,
                'username' => 'keuangan-xss',
                'email' => 'xss@example.test',
                'password_hash' => 'hash-uji',
                'nama' => '<script>alert("x")</script>',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
        ]);

        DB::table('user_roles')->insert([
            [
                'user_id' => 1,
                'role_id' => 1,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'user_id' => 2,
                'role_id' => 2,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'user_id' => 3,
                'role_id' => 3,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'user_id' => 4,
                'role_id' => 4,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'user_id' => 5,
                'role_id' => 1,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'user_id' => 6,
                'role_id' => 1,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
        ]);

        DB::table('dosen')->insert([
            'id' => 31,
            'user_id' => 3,
            'kode_dosen' => 'DSN-UJI',
            'status' => 'aktif',
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ]);

        DB::table('mahasiswa')->insert([
            'id' => 41,
            'user_id' => 4,
            'nim' => 'MHS-UJI',
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ]);

        DB::table('jenis_biaya')->insert([
            ['id' => 1, 'aktif' => true],
            ['id' => 2, 'aktif' => true],
            ['id' => 3, 'aktif' => false],
        ]);

        DB::table('tagihan')->insert([
            [
                'id' => 1,
                'nominal' => 500000,
                'status' => 'terbit',
            ],
            [
                'id' => 2,
                'nominal' => 750000,
                'status' => 'terbit',
            ],
            [
                'id' => 3,
                'nominal' => 300000,
                'status' => 'draf',
            ],
            [
                'id' => 4,
                'nominal' => 250000,
                'status' => 'dibatalkan',
            ],
        ]);

        DB::table('pembayaran')->insert([
            [
                'id' => 1,
                'nominal_diajukan' => 500000,
                'status' => 'menunggu',
            ],
            [
                'id' => 2,
                'nominal_diajukan' => 750000,
                'status' => 'diterima',
            ],
            [
                'id' => 3,
                'nominal_diajukan' => 300000,
                'status' => 'ditolak',
            ],
            [
                'id' => 4,
                'nominal_diajukan' => 250000,
                'status' => 'dibatalkan',
            ],
        ]);
    }

    public function test_admin_keuangan_dapat_membuka_dashboard(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('keuangan.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin Keuangan')
            ->assertSee('Administrator Keuangan')
            ->assertSee('Menunggu verifikasi');
    }

    public function test_ringkasan_keuangan_dihitung_benar(): void
    {
        $response = $this->actingAs(User::findOrFail(1))
            ->get(route('keuangan.dashboard'));

        $response
            ->assertOk()
            ->assertViewHas('ringkasan', function (array $ringkasan): bool {
                return $ringkasan === [
                    'jenis_biaya_aktif' => 2,
                    'tagihan_draf' => 1,
                    'tagihan_terbit' => 2,
                    'tagihan_dibatalkan' => 1,
                    'nominal_tagihan_terbit' => 1250000,
                    'pembayaran_menunggu' => 1,
                    'pembayaran_diterima' => 1,
                    'pembayaran_ditolak' => 1,
                    'pembayaran_dibatalkan' => 1,
                    'nominal_pembayaran_diterima' => 750000,
                ];
            })
            ->assertSee('Rp1.250.000')
            ->assertSee('Rp750.000');
    }

    public function test_admin_akademik_ditolak(): void
    {
        $this->actingAs(User::findOrFail(2))
            ->get(route('keuangan.dashboard'))
            ->assertForbidden();
    }

    public function test_dosen_ditolak(): void
    {
        $this->actingAs(User::findOrFail(3))
            ->get(route('keuangan.dashboard'))
            ->assertForbidden();
    }

    public function test_mahasiswa_ditolak(): void
    {
        $this->actingAs(User::findOrFail(4))
            ->get(route('keuangan.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_keuangan_nonaktif_ditolak(): void
    {
        $this->actingAs(User::findOrFail(5))
            ->get(route('keuangan.dashboard'))
            ->assertForbidden();
    }

    public function test_pencabutan_role_memblokir_dashboard(): void
    {
        $user = User::findOrFail(1);

        DB::table('user_roles')
            ->where('user_id', $user->id)
            ->delete();

        $this->actingAs($user)
            ->get(route('keuangan.dashboard'))
            ->assertForbidden();
    }

    public function test_nama_admin_keuangan_diamankan_dari_xss(): void
    {
        $this->actingAs(User::findOrFail(6))
            ->get(route('keuangan.dashboard'))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>', false);
    }

    public function test_tujuan_portal_keuangan_menuju_dashboard(): void
    {
        $tujuan = app(TujuanPortal::class)
            ->namaRoute(User::findOrFail(1));

        $this->assertSame(
            'keuangan.dashboard',
            $tujuan
        );
    }
}