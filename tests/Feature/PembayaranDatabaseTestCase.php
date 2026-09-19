<?php

namespace Tests\Feature;

use App\Actions\KelolaPembayaran;
use App\Models\Berkas;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Policies\PembayaranPolicy;
use App\Services\AksesPembayaran;
use App\Services\TujuanPembayaran;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Memakai fixture SQLite :memory: dari panduan Tagihan, tidak database proyek.
abstract class PembayaranDatabaseTestCase extends TagihanDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Carbon::setTestNow(CarbonImmutable::parse('2026-09-18 02:00:00', 'UTC'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-18 02:00:00', 'UTC'));
        config([
            'app.timezone' => 'UTC',
            'pembayaran.tujuan_transfer' => 'BANK UJI / 000000 / INSTITUSI UJI',
            'berkas.disk_diizinkan' => ['berkas_local', 'berkas_s3']
        ]);
        Storage::fake('berkas_local');
        // Fixture metadata. Parser format/antivirus diuji pada BerkasKeamananTest.
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
        foreach ([11 => 4, 12 => 3, 13 => 1, 14 => 4] as $id => $userId) {
            $isi = "%PDF-1.4\nfixture-bukti-" . ($id === 14 ? 11 : $id) . "\n%%EOF";
            $key = 'siakad/berkas/2026/09/' . Str::uuid() . '.pdf';
            Storage::disk('berkas_local')->put($key, $isi);
            DB::table('berkas')->insert([
                'id' => $id,
                'diunggah_oleh' => $userId,
                'nama_asli' => 'bukti-' . $id . '.pdf',
                'label' => 'Bukti uji ' . $id,
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
        $paths = glob(database_path('migrations/*_create_pembayaran_table.php'));
        $this->assertCount(1, $paths);
        (require $paths[0])->up();
        Gate::policy(Pembayaran::class, PembayaranPolicy::class);
        Gate::define('akses-pembayaran', fn(User $u): bool => app(AksesPembayaran::class)->masuk($u));
    }
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }
    protected function tagihanTerbit(array $ubah = []): Tagihan
    {
        return $this->transisi($this->buat($ubah), 'terbitkan');
    }
    protected function dataBayar(Tagihan $t, array $ubah = []): array
    {
        return array_replace([
            'form_token' => (string) Str::uuid(),
            'versi_tagihan' => $t->versiForm(),
            'versi_tujuan' => TujuanPembayaran::versi(),
            'bukti_berkas_id' => 11,
            'nominal_diajukan' => $t->nominal,
            'tanggal_transfer' => '2026-09-17',
            'referensi_bank' => 'REF-UJI',
            'konfirmasi' => '1'
        ], $ubah);
    }
    protected function ajukan(Tagihan $t, array $ubah = [], int $user = 4): Pembayaran
    {
        return app(KelolaPembayaran::class)->ajukan($user, $t, $this->dataBayar($t, $ubah));
    }
    protected function batal(Pembayaran $p, int $user = 4): Pembayaran
    {
        return app(KelolaPembayaran::class)->batalkan(
            $user,
            $p,
            ['versi' => $p->versiForm(), 'alasan' => 'Pembatalan pengajuan untuk pengujian.', 'konfirmasi' => '1']
        );
    }
}
