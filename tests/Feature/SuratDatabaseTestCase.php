<?php

namespace Tests\Feature;

use App\Actions\KelolaJenisSurat;
use App\Actions\KelolaPermohonanSurat;
use App\Models\Berkas;
use App\Models\JenisSurat;
use App\Models\PermohonanSurat;
use App\Models\User;
use App\Policies\PermohonanSuratPolicy;
use App\Services\AksesSurat;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Menggunakan fixture JenisSuratDatabaseTestCase dari paket Jenis Surat, bukan database utama.
abstract class SuratDatabaseTestCase extends JenisSuratDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Carbon::setTestNow(CarbonImmutable::parse('2026-09-18 02:00:00', 'UTC'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-18 02:00:00', 'UTC'));
        config(['app.timezone' => 'UTC', 'berkas.disk_diizinkan' => ['berkas_local', 'berkas_s3']]);
        Storage::fake('berkas_local');
        Schema::table('mahasiswa', fn(Blueprint $t) => $t->string('nim')->nullable());
        DB::table('mahasiswa')->insert([['id' => 1, 'user_id' => 4, 'nim' => 'M001'], ['id' => 2, 'user_id' => 3, 'nim' => 'M002']]);
        DB::table('user_roles')->insert(['user_id' => 3, 'role_id' => 4]); // Mahasiswa lain, sekaligus dosen.
        Schema::create('periode_akademik', function (Blueprint $t): void {
            $t->id();
            $t->string('status');
        });
        Schema::create('riwayat_studi', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('mahasiswa_id');
            $t->string('status');
        });
        Schema::create('registrasi_semester', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('riwayat_studi_id');
            $t->unsignedBigInteger('periode_akademik_id');
            $t->unsignedInteger('semester_studi');
            $t->string('status');
            $t->unsignedInteger('revisi');
        });
        DB::table('periode_akademik')->insert(['id' => 1, 'status' => 'aktif']);
        DB::table('riwayat_studi')->insert([['id' => 1, 'mahasiswa_id' => 1, 'status' => 'aktif'], ['id' => 2, 'mahasiswa_id' => 2, 'status' => 'aktif']]);
        DB::table('registrasi_semester')->insert([
            ['id' => 1, 'riwayat_studi_id' => 1, 'periode_akademik_id' => 1, 'semester_studi' => 1, 'status' => 'aktif', 'revisi' => 1],
            ['id' => 2, 'riwayat_studi_id' => 2, 'periode_akademik_id' => 1, 'semester_studi' => 1, 'status' => 'aktif', 'revisi' => 1],
        ]);
        Schema::create('berkas', function (Blueprint $b): void {
            $b->id();
            $b->unsignedBigInteger('diunggah_oleh');
            $b->string('nama_asli');
            $b->string('label');
            $b->string('mime_type');
            $b->string('ekstensi');
            $b->unsignedBigInteger('ukuran_byte');
            $b->char('sha256', 64);
            $b->string('storage_disk');
            $b->string('object_key');
            $b->string('pemeriksaan');
            $b->string('status');
            $b->unsignedInteger('revisi');
        });
        foreach ([11 => 4, 12 => 3, 13 => 1, 14 => 6, 15 => 4, 16 => 1] as $id => $owner) {
            // Fixture byte untuk uji integritas/akses; parser PDF dan antivirus diuji di modul Berkas.
            $isi = "%PDF-1.4\nfixture-surat-{$id}\n%%EOF";
            $key = 'siakad/berkas/2026/09/' . Str::uuid() . '.pdf';
            Storage::disk('berkas_local')->put($key, $isi);
            DB::table('berkas')->insert([
                'id' => $id,
                'diunggah_oleh' => $owner,
                'nama_asli' => 'surat-' . $id . '.pdf',
                'label' => 'Dokumen uji ' . $id,
                'mime_type' => 'application/pdf',
                'ekstensi' => 'pdf',
                'ukuran_byte' => strlen($isi),
                'sha256' => hash('sha256', $isi),
                'storage_disk' => 'berkas_local',
                'object_key' => $key,
                'pemeriksaan' => 'format',
                'status' => 'tersedia',
                'revisi' => 2
            ]);
        }
        Schema::create('pembayaran', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('bukti_berkas_id');
        });
        foreach (['materi_berkas', 'kegiatan_berkas', 'pengumpulan_berkas'] as $nama) {
            Schema::create($nama, function (Blueprint $t): void {
                $t->id();
                $t->unsignedBigInteger('berkas_id');
            });
        }
        app(KelolaJenisSurat::class)->buat(1, ['kode' => 'AKTIF_KULIAH', 'nama' => 'Surat Aktif Kuliah', 'syarat' => 'Tujuan penggunaan surat.', 'form_token' => (string) Str::uuid()]);
        $paths = glob(database_path('migrations/*_create_permohonan_surat_dan_riwayat_surat_tables.php'));
        $this->assertCount(1, $paths);
        (require $paths[0])->up();
        Gate::policy(PermohonanSurat::class, PermohonanSuratPolicy::class);
        Gate::define('akses-surat', fn(User $u): bool => app(AksesSurat::class)->masuk($u));
    }
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }
    protected function dataSurat(array $ubah = []): array
    {
        return array_replace([
            'registrasi_semester_id' => 1,
            'jenis_surat_id' => 1,
            'versi_jenis' => JenisSurat::findOrFail(1)->versiForm(),
            'keperluan' => 'Melengkapi persyaratan administrasi beasiswa.',
            'lampiran_berkas_id' => null,
            'form_token' => (string) Str::uuid(),
            'konfirmasi' => '1'
        ], $ubah);
    }
    protected function ajukanSurat(array $ubah = [], int $user = 4): PermohonanSurat
    {
        return app(KelolaPermohonanSurat::class)->ajukan($user, $this->dataSurat($ubah));
    }
    protected function dataTindakan(PermohonanSurat $p, string $tujuan, array $ubah = []): array
    {
        $v = ['tujuan' => $tujuan, 'versi' => $p->versiForm(), 'catatan' => 'Tindakan akademik setelah pemeriksaan dokumen.', 'konfirmasi' => '1'];
        if ($tujuan === 'terbit') {
            $v += ['nomor_surat' => '001/ILYAS/IX/2026', 'hasil_berkas_id' => 13];
        }
        return array_replace($v, $ubah);
    }
    protected function tindakanSurat(PermohonanSurat $p, string $tujuan, array $ubah = [], int $user = 1): PermohonanSurat
    {
        return app(KelolaPermohonanSurat::class)->tindakan($user, $p, $this->dataTindakan($p, $tujuan, $ubah));
    }
    protected function terbitkanSurat(): PermohonanSurat
    {
        return $this->tindakanSurat($this->tindakanSurat($this->ajukanSurat(), 'diproses'), 'terbit');
    }
}
