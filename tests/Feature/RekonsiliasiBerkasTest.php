<?php

namespace Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class RekonsiliasiBerkasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped(
                'Aktifkan pdo_sqlite untuk tes database terisolasi.'
            );
        }

        $this->app->detectEnvironment(fn() => 'testing');

        config([
            'database.default' => 'rekonsiliasi_uji',
            'database.connections.rekonsiliasi_uji' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'berkas.batas_menunggu_menit' => 30,
        ]);

        DB::purge('rekonsiliasi_uji');

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        Schema::create('berkas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('diunggah_oleh');
            $table->uuid('upload_token')->unique();
            $table->string('storage_disk', 40);
            $table->string('object_key', 500)->unique();
            $table->string('nama_asli');
            $table->string('label', 200);
            $table->text('keterangan')->nullable();
            $table->string('mime_type', 150);
            $table->string('ekstensi', 10);
            $table->unsignedBigInteger('ukuran_byte');
            $table->char('sha256', 64);
            $table->string('pemeriksaan', 20);
            $table->dateTime('diperiksa_at');
            $table->string('status', 20)->default('menunggu');
            $table->string('pesan_status')->nullable();
            $table->dateTime('tersedia_at')->nullable();
            $table->dateTime('dinonaktifkan_at')->nullable();
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamps();
        });

        Schema::create('audit_log', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('pelaku_id')->nullable();
            $table->string('entitas', 60);
            $table->unsignedBigInteger('entitas_id');
            $table->unsignedInteger('versi_entitas')->nullable();
            $table->string('aksi', 40);
            $table->json('sebelum')->nullable();
            $table->json('sesudah')->nullable();
            $table->text('alasan')->nullable();
            $table->dateTime('waktu');
            $table->timestamps();

            $table->unique(
                ['entitas', 'entitas_id', 'versi_entitas'],
                'audit_entitas_versi_unique'
            );
        });
    }

    protected function tearDown(): void
    {
        try {
            DB::purge('rekonsiliasi_uji');
        } finally {
            parent::tearDown();
        }
    }

    private function buatBerkas(
        string $status = 'menunggu',
        ?string $dibuat = null
    ): int {
        $waktu = $dibuat ?? now()->toDateTimeString();
        $token = (string) Str::uuid();
        $id = DB::table('berkas')->insertGetId([
            'diunggah_oleh' => 1,
            'upload_token' => $token,
            'storage_disk' => 'berkas_local',
            'object_key' => 'siakad/berkas/2026/10/' . $token . '.pdf',
            'nama_asli' => 'dokumen.pdf',
            'label' => 'Dokumen pengujian',
            'keterangan' => null,
            'mime_type' => 'application/pdf',
            'ekstensi' => 'pdf',
            'ukuran_byte' => 100,
            'sha256' => hash('sha256', 'dokumen-uji'),
            'pemeriksaan' => 'format',
            'diperiksa_at' => $waktu,
            'status' => $status,
            'pesan_status' => null,
            'tersedia_at' => $status === 'tersedia' ? $waktu : null,
            'dinonaktifkan_at' => null,
            'revisi' => 1,
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ]);

        return (int) $id;
    }

    public function test_dry_run_tidak_mengubah_data(): void
    {
        $id = $this->buatBerkas(
            'menunggu',
            now()->subMinutes(45)->toDateTimeString()
        );

        $kode = Artisan::call('berkas:rekonsiliasi', [
            '--dry-run' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(Command::SUCCESS, $kode);
        $this->assertStringContainsString('Kandidat kedaluwarsa: 1', $output);
        $this->assertStringContainsString("ID berkas: {$id}", $output);
        $this->assertDatabaseHas('berkas', [
            'id' => $id,
            'status' => 'menunggu',
            'revisi' => 1,
        ]);
        $this->assertDatabaseCount('audit_log', 0);
    }

    public function test_berkas_kedaluwarsa_ditolak_dan_audit_dicatat(): void
    {
        $id = $this->buatBerkas(
            'menunggu',
            now()->subMinutes(45)->toDateTimeString()
        );

        $kode = Artisan::call('berkas:rekonsiliasi');

        $this->assertSame(Command::SUCCESS, $kode);

        $this->assertDatabaseHas('berkas', [
            'id' => $id,
            'status' => 'ditolak',
            'revisi' => 2,
            'pesan_status' =>
            'Unggahan melewati batas waktu pemrosesan tanpa penyelesaian.',
        ]);

        $this->assertDatabaseHas('audit_log', [
            'entitas' => 'berkas',
            'entitas_id' => $id,
            'versi_entitas' => 2,
            'aksi' => 'rekonsiliasi_kedaluwarsa',
            'pelaku_id' => null,
        ]);
    }

    public function test_berkas_baru_dan_tersedia_tidak_diubah(): void
    {
        $baru = $this->buatBerkas('menunggu');
        $tersedia = $this->buatBerkas('tersedia');

        $kode = Artisan::call('berkas:rekonsiliasi');

        $this->assertSame(Command::SUCCESS, $kode);

        $this->assertDatabaseHas('berkas', [
            'id' => $baru,
            'status' => 'menunggu',
            'revisi' => 1,
        ]);

        $this->assertDatabaseHas('berkas', [
            'id' => $tersedia,
            'status' => 'tersedia',
            'revisi' => 1,
        ]);

        $this->assertDatabaseCount('audit_log', 0);
    }


    public function test_berkas_yang_belum_mencapai_batas_waktu_tidak_diubah(): void
    {
        $id = $this->buatBerkas(
            'menunggu',
            now()->subMinutes(29)->toDateTimeString()
        );

        $kode = Artisan::call('berkas:rekonsiliasi');

        $this->assertSame(Command::SUCCESS, $kode);

        $this->assertDatabaseHas('berkas', [
            'id' => $id,
            'status' => 'menunggu',
            'revisi' => 1,
        ]);

        $this->assertDatabaseCount('audit_log', 0);
    }

    public function test_menjalankan_rekonsiliasi_dua_kali_tidak_menggandakan_audit(): void
    {
        $id = $this->buatBerkas(
            'menunggu',
            now()->subMinutes(45)->toDateTimeString()
        );

        $kodePertama = Artisan::call('berkas:rekonsiliasi');

        $this->assertSame(Command::SUCCESS, $kodePertama);

        $kodeKedua = Artisan::call('berkas:rekonsiliasi');

        $this->assertSame(Command::SUCCESS, $kodeKedua);

        $this->assertDatabaseHas('berkas', [
            'id' => $id,
            'status' => 'ditolak',
            'revisi' => 2,
        ]);

        $this->assertSame(
            1,
            DB::table('audit_log')
                ->where('entitas', 'berkas')
                ->where('entitas_id', $id)
                ->where('aksi', 'rekonsiliasi_kedaluwarsa')
                ->count()
        );
    }


    public function test_kegagalan_audit_membatalkan_perubahan_berkas(): void
    {
        $id = $this->buatBerkas(
            'menunggu',
            now()->subMinutes(45)->toDateTimeString()
        );

        // Sengaja membuat konflik versi audit agar penyimpanan audit gagal.
        DB::table('audit_log')->insert([
            'pelaku_id' => null,
            'entitas' => 'berkas',
            'entitas_id' => $id,
            'versi_entitas' => 2,
            'aksi' => 'audit_pengujian',
            'sebelum' => null,
            'sesudah' => null,
            'alasan' => 'Data pemicu konflik untuk pengujian.',
            'waktu' => now()->toDateTimeString(),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $kode = Artisan::call('berkas:rekonsiliasi');

        $this->assertSame(Command::FAILURE, $kode);

        // Perubahan status dan revisi harus dibatalkan oleh transaksi.
        $this->assertDatabaseHas('berkas', [
            'id' => $id,
            'status' => 'menunggu',
            'revisi' => 1,
        ]);

        // Hanya audit pemicu konflik yang boleh tersisa.
        $this->assertSame(
            1,
            DB::table('audit_log')
                ->where('entitas', 'berkas')
                ->where('entitas_id', $id)
                ->count()
        );
    }
}
