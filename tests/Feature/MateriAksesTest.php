<?php

namespace Tests\Feature;

use App\Actions\KelolaMateri;
use App\Models\Materi;
use App\Models\User;
use App\Policies\MateriPolicy;
use App\Services\AksesMateri;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MateriAksesTest extends TestCase
{
    // Tidak memakai RefreshDatabase dan tidak menjalankan migrate:fresh.
    // Seluruh fixture ada di SQLite :memory: khusus; tidak menyentuh siakad_ilyas.
    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped(
            'Materi telah digabung ke modul kegiatan/pembelajaran.'
        );
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Aktifkan pdo_sqlite untuk tes basis data terisolasi.');
        }
        $this->app->detectEnvironment(fn() => 'testing');
        config(['database.default' => 'materi_uji', 'database.connections.materi_uji' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ], 'materi.maks_lampiran' => 10]);
        DB::purge('materi_uji');
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Gate::policy(Materi::class, MateriPolicy::class);
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
        $paths = glob(database_path('migrations/*_create_materi_dan_materi_berkas_tables.php'));
        $this->assertCount(1, $paths, 'Harus ada tepat satu migration materi dari panduan.');
        (require $paths[0])->up();
        $this->fixture();
    }
    protected function tearDown(): void
    {
        try {
            DB::purge('materi_uji');
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
            ['id' => 1, 'kelas_kuliah_id' => 100, 'status' => 'terjadwal'],
            ['id' => 2, 'kelas_kuliah_id' => 101, 'status' => 'terjadwal']
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
    private function data(array $tambahan = []): array
    {
        return array_replace([
            'kelas_kuliah_id' => 100,
            'pertemuan_id' => 1,
            'form_token' => (string) Str::uuid(),
            'judul' => 'Materi pertama',
            'isi' => 'Uraian pembelajaran.',
            'tautan_eksternal' => null,
            'berkas_ids' => ['1'],
            'alasan' => 'Perubahan untuk pengujian.'
        ], $tambahan);
    }
    private function buat(): Materi
    {
        return app(KelolaMateri::class)->buat(2, $this->data());
    }
    private function terbit(): Materi
    {
        $m = $this->buat();
        return app(KelolaMateri::class)->status('terbitkan', 2, $m, ['versi' => $m->versiForm(), 'alasan' => 'Materi siap dibaca peserta.']);
    }
    public function test_dosen_hanya_mengelola_kelas_penugasannya(): void
    {
        $a = app(AksesMateri::class);
        $u = User::findOrFail(2);
        $this->assertTrue($a->kelola($u, 100));
        $this->assertFalse($a->kelola($u, 101));
    }
    public function test_mahasiswa_hanya_melihat_materi_terbit(): void
    {
        $m = $this->buat();
        $u = User::findOrFail(3);
        $a = app(AksesMateri::class);
        $this->assertFalse($a->lihat($u, $m));
        $m = app(KelolaMateri::class)->status('terbitkan', 2, $m, ['versi' => $m->versiForm(), 'alasan' => 'Siap diterbitkan sekarang.']);
        $this->assertTrue($a->lihat($u, $m));
        $m = app(KelolaMateri::class)->status('arsipkan', 2, $m, ['versi' => $m->versiForm(), 'alasan' => 'Materi sudah tidak dipakai.']);
        $this->assertFalse($a->lihat($u, $m));
    }
    public function test_pencabutan_krs_mencabut_akses(): void
    {
        $m = $this->terbit();
        $u = User::findOrFail(3);
        $a = app(AksesMateri::class);
        $this->assertTrue($a->lihat($u, $m));
        DB::table('krs')->where('id', 1)->update(['status' => 'dibatalkan']);
        $this->assertFalse($a->lihat($u, $m));
    }
    public function test_akun_nonaktif_dan_role_keuangan_ditolak(): void
    {
        $a = app(AksesMateri::class);
        $this->assertFalse($a->masuk(User::findOrFail(5)));
        $u = User::findOrFail(2);
        DB::table('users')->where('id', 2)->update(['status' => 'nonaktif']);
        $this->assertFalse($a->masuk($u));
    }
    public function test_pertemuan_batal_menyembunyikan_materi_dari_mahasiswa(): void
    {
        $m = $this->terbit();
        DB::table('pertemuan')->where('id', 1)->update(['status' => 'batal']);
        $this->assertFalse(app(AksesMateri::class)->lihat(User::findOrFail(3), $m));
        $this->assertTrue(app(AksesMateri::class)->lihat(User::findOrFail(2), $m));
    }
    public function test_materi_kelas_lain_tidak_masuk_daftar_mahasiswa(): void
    {
        $m = app(KelolaMateri::class)->buat(4, $this->data(['kelas_kuliah_id' => 101, 'pertemuan_id' => 2, 'berkas_ids' => ['2']]));
        app(KelolaMateri::class)->status('terbitkan', 4, $m, ['versi' => $m->versiForm(), 'alasan' => 'Terbitkan untuk kelas B.']);
        $this->assertSame(0, app(AksesMateri::class)->batasi(Materi::query(), User::findOrFail(3))->count());
    }
    public function test_lampiran_milik_orang_lain_ditolak_dan_transaksi_dibatalkan(): void
    {
        try {
            app(KelolaMateri::class)->buat(2, $this->data(['berkas_ids' => ['2']]));
            $this->fail('Seharusnya ditolak.');
        } catch (ValidationException) {
            $this->assertSame(0, DB::table('materi')->count());
            $this->assertSame(0, DB::table('audit_log')->count());
        }
    }
    public function test_pengiriman_form_sama_tidak_menggandakan_materi(): void
    {
        $d = $this->data();
        $a = app(KelolaMateri::class);
        $m = $a->buat(2, $d);
        $ulang = $a->buat(2, $d);
        $this->assertSame($m->id, $ulang->id);
        $this->assertSame(1, DB::table('materi')->count());
        $this->assertSame(1, DB::table('materi_berkas')->count());
        $this->assertSame(1, DB::table('audit_log')->count());
    }
    public function test_pertemuan_beda_kelas_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        app(KelolaMateri::class)->buat(2, $this->data(['pertemuan_id' => 2]));
    }
    public function test_form_lama_tidak_menimpa_revisi_baru(): void
    {
        $m = $this->buat();
        $lama = $m->versiForm();
        $baru = app(KelolaMateri::class)->ubah(2, $m, $this->data(['versi' => $lama, 'judul' => 'Judul baru']));
        $this->assertSame(2, $baru->revisi);
        $this->expectException(ValidationException::class);
        app(KelolaMateri::class)->ubah(2, $m, $this->data(['versi' => $lama]));
    }
    public function test_materi_terbit_tidak_bisa_diedit_langsung(): void
    {
        $m = $this->terbit();
        $this->expectException(AuthorizationException::class);
        app(KelolaMateri::class)->ubah(2, $m, $this->data(['versi' => $m->versiForm()]));
    }
    public function test_melepas_lampiran_mempertahankan_objek_dan_riwayat(): void
    {
        $m = $this->buat();
        $m = app(KelolaMateri::class)->ubah(2, $m, $this->data(['versi' => $m->versiForm(), 'berkas_ids' => []]));
        $this->assertSame(0, $m->lampiran()->count());
        $this->assertSame(1, $m->semuaLampiran()->count());
        $this->assertSame(2, DB::table('berkas')->count());
        $this->assertSame(2, DB::table('audit_log')->count());
    }
}
