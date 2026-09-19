<?php

namespace Tests\Feature;

use App\Actions\KelolaKalenderAkademik;
use App\Models\KalenderAkademik;
use App\Models\User;
use App\Policies\KalenderAkademikPolicy;
use App\Services\AksesKalender;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Fixture Jenis Surat memakai SQLite :memory:, bukan database siakad_ilyas.
abstract class KalenderDatabaseTestCase extends JenisSuratDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(CarbonImmutable::parse('2026-09-18 02:00:00', 'UTC'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-18 02:00:00', 'UTC'));
        Schema::create('periode_akademik', function (Blueprint $t): void {
            $t->id();
            $t->string('kode');
            $t->string('status');
            $t->date('mulai');
            $t->date('selesai');
            $t->dateTime('krs_mulai')->nullable();
            $t->dateTime('krs_selesai')->nullable();
        });
        Schema::create('program_studi', function (Blueprint $t): void {
            $t->id();
            $t->string('kode');
            $t->string('nama');
            $t->boolean('aktif');
        });
        DB::table('periode_akademik')->insert([
            ['id' => 1, 'kode' => '2026-GANJIL', 'status' => 'aktif', 'mulai' => '2026-09-01', 'selesai' => '2027-02-28', 'krs_mulai' => '2026-08-01 00:00:00', 'krs_selesai' => '2026-08-20 00:00:00'],
            ['id' => 2, 'kode' => '2027-GENAP', 'status' => 'persiapan', 'mulai' => '2027-03-01', 'selesai' => '2027-08-31', 'krs_mulai' => null, 'krs_selesai' => null],
        ]);
        DB::table('program_studi')->insert([['id' => 1, 'kode' => 'PAI', 'nama' => 'Prodi Satu', 'aktif' => true], ['id' => 2, 'kode' => 'ILMU', 'nama' => 'Prodi Dua', 'aktif' => true]]);
        DB::table('dosen')->insert(['id' => 1, 'user_id' => 3, 'status' => 'aktif']);
        DB::table('mahasiswa')->insert(['id' => 1, 'user_id' => 4]);
        $paths = glob(database_path('migrations/*_create_kalender_akademik_table.php'));
        $this->assertCount(1, $paths);
        (require $paths[0])->up();
        Gate::policy(KalenderAkademik::class, KalenderAkademikPolicy::class);
        Gate::define('akses-kalender', fn(User $u): bool => app(AksesKalender::class)->masuk($u));
    }
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }
    protected function dataAgenda(array $ubah = []): array
    {
        return array_replace([
            'periode_akademik_id' => 1,
            'program_studi_id' => null,
            'judul' => 'Pembukaan kuliah semester ganjil',
            'keterangan' => 'Informasi agenda resmi.',
            'jenis' => 'kuliah',
            'mulai_lokal' => '2026-09-20T08:00',
            'selesai_lokal' => '2026-09-20T10:00',
            'form_token' => (string) Str::uuid()
        ], $ubah);
    }
    protected function buatAgenda(array $ubah = []): KalenderAkademik
    {
        return app(KelolaKalenderAkademik::class)->buat(1, $this->dataAgenda($ubah));
    }
    protected function tindakanAgenda(KalenderAkademik $k, string $aksi, array $ubah = [], int $userId = 1): KalenderAkademik
    {
        return app(KelolaKalenderAkademik::class)->tindakan($userId, $k, array_replace(['aksi' => $aksi, 'versi' => $k->versiForm(), 'alasan' => 'Perubahan agenda untuk pengujian.', 'konfirmasi' => '1'], $ubah));
    }
    protected function dataEdit(KalenderAkademik $k, array $ubah = []): array
    {
        return array_replace([
            'periode_akademik_id' => $k->periode_akademik_id,
            'program_studi_id' => $k->program_studi_id,
            'judul' => $k->judul,
            'keterangan' => $k->keterangan,
            'jenis' => $k->jenis,
            'mulai_lokal' => $k->mulai_at->setTimezone(KalenderAkademik::ZONA)->format('Y-m-d\TH:i'),
            'selesai_lokal' => $k->selesai_at->setTimezone(KalenderAkademik::ZONA)->format('Y-m-d\TH:i'),
            'versi' => $k->versiForm(),
            'alasan' => 'Koreksi agenda untuk pengujian.'
        ], $ubah);
    }
}
