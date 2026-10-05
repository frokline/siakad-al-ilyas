<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TujuanPortal;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PortalMahasiswaAksesTest extends TestCase
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
            'database.default' => 'portal_mahasiswa_uji',
            'database.connections.portal_mahasiswa_uji' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge('portal_mahasiswa_uji');

        $this->buatTabel();
        $this->buatData();
    }

    protected function tearDown(): void
    {
        try {
            DB::purge('portal_mahasiswa_uji');
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
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        Schema::create('program_studi', function (Blueprint $table): void {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('jenjang');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('periode_akademik', function (Blueprint $table): void {
            $table->id();
            $table->string('kode')->unique();
            $table->unsignedSmallInteger('tahun_mulai');
            $table->string('jenis');
            $table->date('mulai');
            $table->date('selesai');
            $table->dateTime('krs_mulai');
            $table->dateTime('krs_selesai');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('kurikulum', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_studi_id');
            $table->string('kode')->unique();
            $table->string('nama');
            $table->unsignedSmallInteger('tahun_berlaku');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('dosen', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('kode_dosen')->unique();
            $table->string('nidn')->nullable();
            $table->string('gelar')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('riwayat_studi', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('kurikulum_id');
            $table->unsignedSmallInteger('angkatan');
            $table->unsignedBigInteger('periode_mulai_id');
            $table->unsignedBigInteger('periode_akhir_id')->nullable();
            $table->unsignedBigInteger('dosen_pa_id')->nullable();
            $table->string('status');
            $table->unsignedTinyInteger('aktif_guard')->nullable();
            $table->timestamps();
        });

        Schema::create('paket_semester', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kurikulum_id');
            $table->unsignedTinyInteger('semester_studi');
            $table->unsignedInteger('versi');
            $table->string('nama');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('rombel', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('periode_akademik_id');
            $table->unsignedBigInteger('paket_semester_id');
            $table->string('kode');
            $table->unsignedInteger('kapasitas');
            $table->timestamps();
        });

        Schema::create(
            'registrasi_semester',
            function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('riwayat_studi_id');
                $table->unsignedBigInteger('rombel_id');
                $table->unsignedBigInteger('periode_akademik_id');
                $table->unsignedTinyInteger('semester_studi');
                $table->string('status');
                $table->text('alasan_status')->nullable();
                $table->dateTime('penempatan_dikunci_at')->nullable();
                $table->unsignedInteger('revisi')->default(1);
                $table->timestamps();
            }
        );
    }

    private function buatData(): void
    {
        $waktu = now();

        DB::table('roles')->insert([
            [
                'id' => 1,
                'kode' => 'mahasiswa',
                'nama' => 'Mahasiswa',
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
        ]);

        DB::table('users')->insert([
            [
                'id' => 1,
                'username' => 'mahasiswa',
                'email' => 'mahasiswa@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Ahmad Mahasiswa',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 2,
                'username' => 'admin',
                'email' => 'admin@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Admin Akademik',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 3,
                'username' => 'mahasiswa-nonaktif',
                'email' => 'nonaktif@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Mahasiswa Nonaktif',
                'telepon' => null,
                'status' => 'nonaktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 4,
                'username' => 'tanpa-profil',
                'email' => 'tanpa.profil@example.test',
                'password_hash' => 'hash-uji',
                'nama' => 'Mahasiswa Tanpa Profil',
                'telepon' => null,
                'status' => 'aktif',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 5,
                'username' => 'mahasiswa-xss',
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
                'role_id' => 1,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'user_id' => 4,
                'role_id' => 1,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'user_id' => 5,
                'role_id' => 1,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
        ]);

        DB::table('mahasiswa')->insert([
            [
                'id' => 11,
                'user_id' => 1,
                'nim' => '20260001',
                'tempat_lahir' => null,
                'tanggal_lahir' => null,
                'jenis_kelamin' => 'L',
                'alamat' => null,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 13,
                'user_id' => 3,
                'nim' => '20260003',
                'tempat_lahir' => null,
                'tanggal_lahir' => null,
                'jenis_kelamin' => 'L',
                'alamat' => null,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
            [
                'id' => 15,
                'user_id' => 5,
                'nim' => '20260005',
                'tempat_lahir' => null,
                'tanggal_lahir' => null,
                'jenis_kelamin' => 'P',
                'alamat' => null,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ],
        ]);
    }

    public function test_mahasiswa_dapat_membuka_dashboardnya(): void
    {
        $this->actingAs(User::findOrFail(1))
            ->get(route('portal.mahasiswa.index'))
            ->assertOk()
            ->assertSee('Selamat datang, Ahmad Mahasiswa')
            ->assertSee('20260001')
            ->assertSee('Belum ada registrasi semester aktif.');
    }

    public function test_admin_tidak_dapat_membuka_dashboard_mahasiswa(): void
    {
        $this->actingAs(User::findOrFail(2))
            ->get(route('portal.mahasiswa.index'))
            ->assertForbidden();
    }

    public function test_akun_nonaktif_tidak_dapat_membuka_dashboard(): void
    {
        $this->actingAs(User::findOrFail(3))
            ->get(route('portal.mahasiswa.index'))
            ->assertForbidden();
    }

    public function test_role_mahasiswa_tanpa_profil_ditolak(): void
    {
        $this->actingAs(User::findOrFail(4))
            ->get(route('portal.mahasiswa.index'))
            ->assertForbidden();
    }

    public function test_pencabutan_role_langsung_memblokir_dashboard(): void
    {
        $user = User::findOrFail(1);

        DB::table('user_roles')
            ->where('user_id', $user->id)
            ->delete();

        $this->actingAs($user)
            ->get(route('portal.mahasiswa.index'))
            ->assertForbidden();
    }

    public function test_nama_mahasiswa_diamankan_dari_xss(): void
    {
        $this->actingAs(User::findOrFail(5))
            ->get(route('portal.mahasiswa.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>', false);
    }

    public function test_tujuan_portal_mahasiswa_menuju_dashboard(): void
    {
        $tujuan = app(TujuanPortal::class)
            ->namaRoute(User::findOrFail(1));

        $this->assertSame(
            'portal.mahasiswa.index',
            $tujuan
        );
    }
}