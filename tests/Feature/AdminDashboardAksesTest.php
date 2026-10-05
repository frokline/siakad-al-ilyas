<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TujuanPortal;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminDashboardAksesTest extends TestCase
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
            'database.default' => 'admin_dashboard_uji',
            'database.connections.admin_dashboard_uji' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge('admin_dashboard_uji');

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge('admin_dashboard_uji');
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

        Schema::create('program_studi', function (Blueprint $table): void {
            $table->id();
            $table->boolean('aktif')->default(true);
        });

        Schema::create(
            'periode_akademik',
            function (Blueprint $table): void {
                $table->id();
                $table->string('status');
            }
        );

        Schema::create('kurikulum', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
        });

        Schema::create('mata_kuliah', function (Blueprint $table): void {
            $table->id();
            $table->boolean('aktif')->default(true);
        });

        Schema::create(
            'registrasi_semester',
            function (Blueprint $table): void {
                $table->id();
                $table->string('status');
            }
        );

        Schema::create('kelas_kuliah', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
        });

        Schema::create('krs', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
        });

        Schema::create('pertemuan', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
        });
    }

    private function buatData(): void
    {
        $waktu = now();

        DB::table('roles')->insert([
            [
                'id' => 1,
                'kode' => 'admin_akademik',
                'nama' => 'Admin Akademik',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 2,
                'kode' => 'admin_keuangan',
                'nama' => 'Admin Keuangan',
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
                'username' => 'admin-akademik',
                'email' => 'akademik@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Administrator Akademik',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 2,
                'username' => 'admin-keuangan',
                'email' => 'keuangan@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Administrator Keuangan',
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
                'nama' => 'Dosen Pengajar',
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
                'username' => 'admin-nonaktif',
                'email' => 'nonaktif@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Admin Nonaktif',
                'telepon' => null,
                'status' => 'nonaktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 6,
                'username' => 'admin-xss',
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

        DB::table('program_studi')->insert([
            ['id' => 1, 'aktif' => true],
            ['id' => 2, 'aktif' => true],
        ]);

        DB::table('periode_akademik')->insert([
            ['id' => 1, 'status' => 'aktif'],
            ['id' => 2, 'status' => 'arsip'],
        ]);

        DB::table('kurikulum')->insert([
            ['id' => 1, 'status' => 'aktif'],
            ['id' => 2, 'status' => 'draf'],
        ]);

        DB::table('mata_kuliah')->insert([
            ['id' => 1, 'aktif' => true],
            ['id' => 2, 'aktif' => true],
            ['id' => 3, 'aktif' => false],
        ]);

        DB::table('registrasi_semester')->insert([
            ['id' => 1, 'status' => 'aktif'],
            ['id' => 2, 'status' => 'cuti'],
        ]);

        DB::table('kelas_kuliah')->insert([
            ['id' => 1, 'status' => 'aktif'],
            ['id' => 2, 'status' => 'persiapan'],
        ]);

        DB::table('krs')->insert([
            ['id' => 1, 'status' => 'diajukan'],
            ['id' => 2, 'status' => 'draf'],
        ]);

        DB::table('pertemuan')->insert([
            ['id' => 1, 'status' => 'berlangsung'],
            ['id' => 2, 'status' => 'selesai'],
        ]);
    }

    public function test_admin_akademik_dapat_membuka_dashboard(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin Akademik')
            ->assertSee('Administrator Akademik')
            ->assertSee('KRS menunggu pengesahan');
    }

    public function test_ringkasan_database_ditampilkan_benar(): void
    {
        $response = $this->actingAs(User::findOrFail(1))
            ->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertViewHas('ringkasan', function (array $ringkasan): bool {
                return $ringkasan === [
                    'program_studi' => 2,
                    'periode_aktif' => 1,
                    'kurikulum_aktif' => 1,
                    'mata_kuliah_aktif' => 2,
                    'mahasiswa' => 1,
                    'dosen_aktif' => 1,
                    'registrasi_aktif' => 1,
                    'kelas_aktif' => 1,
                    'krs_diajukan' => 1,
                    'pertemuan_berlangsung' => 1,
                ];
            });
    }

    public function test_admin_keuangan_ditolak(): void
    {
        $this->actingAs(User::findOrFail(2))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_dosen_ditolak(): void
    {
        $this->actingAs(User::findOrFail(3))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_mahasiswa_ditolak(): void
    {
        $this->actingAs(User::findOrFail(4))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_nonaktif_ditolak(): void
    {
        $this->actingAs(User::findOrFail(5))
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_pencabutan_role_langsung_memblokir_dashboard(): void
    {
        $user = User::findOrFail(1);

        DB::table('user_roles')
            ->where('user_id', $user->id)
            ->delete();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_nama_admin_diamankan_dari_xss(): void
    {
        $this->actingAs(User::findOrFail(6))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>', false);
    }

    public function test_tujuan_portal_admin_menuju_dashboard(): void
    {
        $tujuan = app(TujuanPortal::class)
            ->namaRoute(User::findOrFail(1));

        $this->assertSame(
            'admin.dashboard',
            $tujuan
        );
    }
}