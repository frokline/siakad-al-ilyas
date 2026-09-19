<?php

namespace Tests\Feature;

use App\Actions\KelolaKegiatan;
use App\Actions\KelolaPengumpulan;
use App\Models\Kegiatan;
use App\Models\Pengumpulan;
use App\Models\User;
use App\Policies\PengumpulanPolicy;
use App\Services\AksesPengumpulan;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Memperluas fixture SQLite :memory: dari panduan Kegiatan. Tidak menyentuh DB proyek.
abstract class PengumpulanDatabaseTestCase extends KegiatanDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->jam('2030-01-10 02:00:00');
        config([
            'session.driver' => 'array',
            'cache.default' => 'array',
            'cache.limiter' => 'array',
            'pengumpulan.maks_karakter_jawaban' => 10000,
            'pengumpulan.masa_tautan_menit' => 2,
            'app.timezone' => 'UTC',
            'berkas.disk_diizinkan' => ['berkas_local', 'berkas_s3']
        ]);
        Schema::table('users', function (Blueprint $t): void {
            $t->string('password_hash')->nullable();
            $t->string('username')->nullable();
        });
        Schema::table('mahasiswa', function (Blueprint $t): void {
            $t->string('nim')->default('MHS-30');
        });
        Schema::table('berkas', function (Blueprint $t): void {
            $t->string('nama_asli')->nullable();
            $t->string('label')->nullable();
            $t->string('mime_type')->nullable();
            $t->string('ekstensi')->nullable();
            $t->unsignedBigInteger('ukuran_byte')->nullable();
            $t->char('sha256', 64)->nullable();
            $t->string('storage_disk')->nullable();
            $t->string('object_key')->nullable();
        });
        DB::table('users')->insert(['id' => 6, 'nama' => 'Mahasiswa B', 'username' => 'mahasiswa-b', 'status' => 'aktif']);
        DB::table('users')->update(['password_hash' => password_hash('HanyaUntukFixture-Uji-2030', PASSWORD_BCRYPT)]);
        DB::table('user_roles')->insert(['user_id' => 6, 'role_id' => 3]);
        DB::table('mahasiswa')->insert(['id' => 60, 'user_id' => 6, 'nim' => 'MHS-60']);
        DB::table('riwayat_studi')->insert(['id' => 2, 'mahasiswa_id' => 60, 'status' => 'aktif']);
        DB::table('registrasi_semester')->insert(['id' => 2, 'riwayat_studi_id' => 2, 'rombel_id' => 1, 'periode_akademik_id' => 1, 'status' => 'aktif']);
        DB::table('krs')->insert(['id' => 2, 'registrasi_semester_id' => 2, 'status' => 'disahkan']);
        DB::table('detail_krs')->insert(['id' => 2, 'krs_id' => 2, 'kelas_kuliah_id' => 100, 'status' => 'aktif']);
        foreach ([11 => 3, 12 => 6, 13 => 3] as $id => $pemilik) {
            DB::table('berkas')->insert([
                'id' => $id,
                'diunggah_oleh' => $pemilik,
                'status' => 'tersedia',
                'pemeriksaan' => 'format',
                'revisi' => 1,
                'nama_asli' => 'jawaban-' . $id . '.pdf',
                'label' => 'Jawaban ' . $id,
                'mime_type' => 'application/pdf',
                'ekstensi' => 'pdf',
                'ukuran_byte' => 12,
                'sha256' => hash('sha256', 'fixture-' . $id),
                'storage_disk' => 'berkas_local',
                'object_key' => 'siakad/berkas/2030/01/' . Str::uuid() . '.pdf'
            ]);
        }
        $paths = glob(database_path('migrations/*_create_pengumpulan_dan_pengumpulan_berkas_tables.php'));
        $this->assertCount(1, $paths, 'Harus ada tepat satu migration Pengumpulan dari panduan.');
        (require $paths[0])->up();
        Gate::policy(Pengumpulan::class, PengumpulanPolicy::class);
        Gate::define('akses-pengumpulan', fn(User $u): bool => app(AksesPengumpulan::class)->masuk($u));
    }
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }
    protected function jam(string $utc): void
    {
        $waktu = CarbonImmutable::parse($utc, 'UTC');
        Carbon::setTestNow($waktu);
        CarbonImmutable::setTestNow($waktu);
    }
    protected function kegiatan(array $ganti = []): Kegiatan
    {
        $a = app(KelolaKegiatan::class);
        $k = $a->buat(2, array_replace([
            'kelas_kuliah_id' => 100,
            'pertemuan_id' => 1,
            'form_token' => (string) Str::uuid(),
            'jenis' => 'tugas',
            'judul' => 'Latihan pengumpulan',
            'instruksi' => 'Instruksi tugas untuk pengujian.',
            'buka_lokal' => '2030-01-10T10:00',
            'tenggat_lokal' => '2030-01-10T12:00',
            'maks_mb' => 10,
            'maks_berkas' => 2,
            'ekstensi_diizinkan' => ['pdf'],
            'berkas_ids' => []
        ], $ganti));
        return $a->status('terbitkan', 2, $k, ['versi' => $k->versiForm(), 'alasan' => 'Menerbitkan kegiatan pengujian.']);
    }
    protected function draf(Kegiatan $k, int $dasar = 0, ?string $token = null): Pengumpulan
    {
        return app(KelolaPengumpulan::class)->buat(3, $k, ['token_draf' => $token ?? (string) Str::uuid(), 'dasar_versi' => $dasar]);
    }
    protected function isi(Pengumpulan $p, ?string $teks = 'Jawaban mahasiswa A', array $ids = ['11']): Pengumpulan
    {
        return app(KelolaPengumpulan::class)->ubah(3, $p, ['versi_form' => $p->versiForm(), 'jawaban_teks' => $teks, 'berkas_ids' => $ids]);
    }
    protected function kirim(Pengumpulan $p): Pengumpulan
    {
        return app(KelolaPengumpulan::class)->kirim(3, $p, ['versi_form' => $p->versiForm(), 'kunci_kirim' => $p->kunci_kirim]);
    }
    protected function auditJumlah(): int
    {
        return DB::table('audit_log')->where('entitas', 'pengumpulan')->count();
    }
}
