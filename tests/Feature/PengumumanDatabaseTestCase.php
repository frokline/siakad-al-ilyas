<?php

namespace Tests\Feature;

use App\Actions\KelolaPengumuman;
use App\Models\Pengumuman;
use App\Models\User;
use App\Policies\PengumumanPolicy;
use App\Services\AksesPengumuman;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class PengumumanDatabaseTestCase extends JenisSuratDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(Carbon::parse('2026-09-18 00:00:00', 'UTC'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-18 00:00:00', 'UTC'));
        Schema::create('program_studi', function (Blueprint $t): void {
            $t->id();
            $t->string('kode');
            $t->string('nama');
            $t->boolean('aktif');
        });
        Schema::create('kurikulum', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('program_studi_id');
        });
        Schema::create('paket_semester', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('kurikulum_id');
        });
        Schema::create('periode_akademik', function (Blueprint $t): void {
            $t->id();
            $t->string('status');
        });
        Schema::create('rombel', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('periode_akademik_id');
            $t->unsignedBigInteger('paket_semester_id');
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
        Schema::create('riwayat_studi', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('mahasiswa_id');
            $t->unsignedBigInteger('kurikulum_id');
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
        DB::table('periode_akademik')->insert(['id' => 1, 'status' => 'aktif']);
        foreach ([1, 2] as $id) {
            DB::table('program_studi')->insert(['id' => $id, 'kode' => 'P' . $id, 'nama' => 'Prodi ' . $id, 'aktif' => true]);
            DB::table('kurikulum')->insert(['id' => $id, 'program_studi_id' => $id]);
            DB::table('paket_semester')->insert(['id' => $id, 'kurikulum_id' => $id]);
            DB::table('rombel')->insert(['id' => $id, 'periode_akademik_id' => 1, 'paket_semester_id' => $id]);
            DB::table('kelas_kuliah')->insert(['id' => $id, 'rombel_id' => $id, 'kode' => 'K' . $id, 'nama_mk_snapshot' => 'Kuliah ' . $id, 'status' => 'aktif']);
        }
        foreach ([7 => 'Dua peran', 8 => 'Mahasiswa lain'] as $id => $nama) {
            DB::table('users')->insert(['id' => $id, 'nama' => $nama, 'username' => 'uji-' . $id, 'status' => 'aktif', 'password_hash' => password_hash('Fixture-only', PASSWORD_BCRYPT)]);
            DB::table('user_roles')->insert(['user_id' => $id, 'role_id' => 4]);
        }
        DB::table('user_roles')->insert(['user_id' => 7, 'role_id' => 3]);
        foreach ([1 => 3, 2 => 7] as $id => $user) {
            DB::table('dosen')->insert(['id' => $id, 'user_id' => $user, 'status' => 'aktif']);
            DB::table('pengajar_kelas')->insert(['id' => $id, 'kelas_kuliah_id' => 1, 'dosen_id' => $id, 'aktif' => true]);
        }
        foreach ([1 => [4, 1], 2 => [7, 2], 3 => [8, 2]] as $id => [$user, $kelas]) {
            DB::table('mahasiswa')->insert(['id' => $id, 'user_id' => $user]);
            DB::table('riwayat_studi')->insert(['id' => $id, 'mahasiswa_id' => $id, 'kurikulum_id' => $kelas, 'status' => 'aktif']);
            DB::table('registrasi_semester')->insert(['id' => $id, 'riwayat_studi_id' => $id, 'rombel_id' => $kelas, 'periode_akademik_id' => 1, 'status' => 'aktif']);
            DB::table('krs')->insert(['id' => $id, 'registrasi_semester_id' => $id, 'status' => 'disahkan']);
            DB::table('detail_krs')->insert(['id' => $id, 'krs_id' => $id, 'kelas_kuliah_id' => $kelas, 'status' => 'aktif']);
        }
        $paths = glob(database_path('migrations/*_create_pengumuman_dan_sasaran_tables.php'));
        $this->assertCount(1, $paths);
        (require $paths[0])->up();
        Gate::policy(Pengumuman::class, PengumumanPolicy::class);
        Gate::define('akses-pengumuman', fn(User $u): bool => app(AksesPengumuman::class)->masuk($u));
    }
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }
    protected function target(string $lingkup = 'kampus', ?int $induk = null, ?int $role = null): array
    {
        return [
            'lingkup' => $lingkup,
            'program_studi_id' => $lingkup === 'prodi' ? $induk : null,
            'kelas_kuliah_id' => $lingkup === 'kelas' ? $induk : null,
            'role_id' => $role
        ];
    }
    protected function inputPengumuman(array $ganti = []): array
    {
        return array_replace([
            'judul' => 'Informasi perkuliahan',
            'isi' => 'Informasi resmi untuk warga kampus.',
            'berakhir_lokal' => null,
            'sasaran' => [$this->target()],
            'form_token' => (string) Str::uuid()
        ], $ganti);
    }
    protected function draf(array $ganti = [], int $aktor = 1): Pengumuman
    {
        return app(KelolaPengumuman::class)->buat($aktor, $this->inputPengumuman($ganti));
    }
    protected function terbit(Pengumuman $p, int $aktor = 1): Pengumuman
    {
        return $this->aksiPengumuman($p, 'terbit', $aktor);
    }
    protected function aksiPengumuman(Pengumuman $p, string $aksi, int $aktor = 1): Pengumuman
    {
        return app(KelolaPengumuman::class)->tindakan($aktor, $p, ['aksi' => $aksi, 'versi' => $p->versiForm(), 'konfirmasi' => '1', 'alasan' => 'Tindakan resmi untuk pengujian.']);
    }
    protected function terlihat(int $user, Pengumuman $p): bool
    {
        return app(AksesPengumuman::class)->bacaan(Pengumuman::query(), User::query()->findOrFail($user))->whereKey($p->id)->exists();
    }
}
