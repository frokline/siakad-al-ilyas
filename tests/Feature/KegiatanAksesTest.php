<?php

namespace Tests\Feature;

use App\Actions\KelolaKegiatan;
use App\Http\Controllers\KegiatanController;
use App\Models\Kegiatan;
use App\Models\User;
use App\Services\AksesKegiatan;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KegiatanAksesTest extends KegiatanDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(CarbonImmutable::parse('2030-01-10 02:00:00', 'UTC'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2030-01-10 02:00:00', 'UTC'));
    }
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }
    private function data(array $ganti = []): array
    {
        return array_replace([
            'kelas_kuliah_id' => 100,
            'pertemuan_id' => 1,
            'form_token' => (string) Str::uuid(),
            'jenis' => 'tugas',
            'judul' => 'Tugas pertama',
            'instruksi' => 'RAHASIA_SOAL untuk peserta yang berhak.',
            'buka_lokal' => '2030-01-10T10:00',
            'tenggat_lokal' => '2030-01-10T12:00',
            'maks_mb' => 10,
            'maks_berkas' => 3,
            'ekstensi_diizinkan' => ['pdf'],
            'berkas_ids' => ['1'],
            'alasan' => 'Perubahan untuk pengujian.'
        ], $ganti);
    }
    private function buat(array $ganti = []): Kegiatan
    {
        return app(KelolaKegiatan::class)->buat(2, $this->data($ganti));
    }
    private function ubahStatusKegiatan(Kegiatan $k, string $aksi, array $ganti = []): Kegiatan
    {
        return app(KelolaKegiatan::class)->status(
            $aksi,
            2,
            $k,
            array_replace(['versi' => $k->versiForm(), 'alasan' => 'Perubahan status untuk uji.'], $ganti)
        );
    }
    public function test_dosen_tidak_mengelola_kelas_lain(): void
    {
        $a = app(AksesKegiatan::class);
        $u = User::findOrFail(2);
        $this->assertTrue($a->kelola($u, 100));
        $this->assertFalse($a->kelola($u, 101));
    }
    public function test_draf_dan_arsip_disembunyikan_dari_mahasiswa(): void
    {
        $k = $this->buat();
        $a = app(AksesKegiatan::class);
        $u = User::findOrFail(3);
        $this->assertFalse($a->lihat($u, $k));
        $k = $this->ubahStatusKegiatan($k, 'terbitkan');
        $this->assertTrue($a->lihat($u, $k));
        $k = $this->ubahStatusKegiatan($k, 'arsipkan');
        $this->assertFalse($a->lihat($u, $k));
    }
    public function test_instruksi_belum_bisa_dibaca_sebelum_waktu_mulai(): void
    {
        $k = $this->ubahStatusKegiatan($this->buat(['buka_lokal' => '2030-01-10T11:00']), 'terbitkan');
        $a = app(AksesKegiatan::class);
        $u = User::findOrFail(3);
        $this->assertTrue($a->lihat($u, $k));
        $this->assertFalse($a->bacaIsi($u, $k));
        $this->assertTrue($a->bacaIsi(User::findOrFail(2), $k));
    }
    public function test_html_sebelum_mulai_tidak_memuat_instruksi_atau_lampiran(): void
    {
        $k = $this->ubahStatusKegiatan($this->buat(['buka_lokal' => '2030-01-10T11:00']), 'terbitkan');
        $this->actingAs(User::findOrFail(3), 'web');
        $view = app(KegiatanController::class)->show(Request::create('/kegiatan/' . $k->id), $k->fresh());
        $html = $view->render();
        $this->assertStringNotContainsString('RAHASIA_SOAL', $html);
        $this->assertStringNotContainsString('/lampiran/', $html);
        $this->assertStringContainsString('Instruksi dan lampiran tersedia mulai', $html);
    }
    public function test_endpoint_lampiran_ditolak_sebelum_mulai(): void
    {
        $k = $this->ubahStatusKegiatan($this->buat(['buka_lokal' => '2030-01-10T11:00']), 'terbitkan');
        $this->actingAs(User::findOrFail(3), 'web');
        $this->expectException(AuthorizationException::class);
        app(KegiatanController::class)->tautan(Request::create('/kegiatan/' . $k->id), $k, $k->lampiran()->firstOrFail());
    }
    public function test_krs_dicabut_langsung_mencabut_akses(): void
    {
        $k = $this->ubahStatusKegiatan($this->buat(), 'terbitkan');
        $a = app(AksesKegiatan::class);
        $u = User::findOrFail(3);
        $this->assertTrue($a->bacaIsi($u, $k));
        DB::table('krs')->where('id', 1)->update(['status' => 'dibatalkan']);
        $this->assertFalse($a->lihat($u, $k));
        $this->assertFalse($a->bacaIsi($u, $k));
    }
    public function test_file_milik_akun_lain_ditolak_dan_transaksi_batal(): void
    {
        try {
            $this->buat(['berkas_ids' => ['2']]);
            $this->fail('Seharusnya ditolak.');
        } catch (ValidationException) {
            $this->assertSame(0, DB::table('kegiatan')->count());
            $this->assertSame(0, DB::table('audit_log')->count());
        }
    }
    public function test_pengiriman_form_sama_tidak_membuat_duplikat(): void
    {
        $d = $this->data();
        $a = app(KelolaKegiatan::class);
        $awal = $a->buat(2, $d);
        $ulang = $a->buat(2, $d);
        $this->assertSame($awal->id, $ulang->id);
        $this->assertSame(1, DB::table('kegiatan')->count());
        $this->assertSame(1, DB::table('kegiatan_berkas')->count());
        $this->assertSame(1, DB::table('audit_log')->count());
    }
    public function test_pertemuan_beda_kelas_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        $this->buat(['pertemuan_id' => 2]);
    }
    public function test_tenggat_sebelum_mulai_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        $this->buat(['tenggat_lokal' => '2030-01-10T09:00']);
    }
    public function test_kegiatan_terbit_tidak_bisa_diedit(): void
    {
        $k = $this->ubahStatusKegiatan($this->buat(), 'terbitkan');
        $this->expectException(AuthorizationException::class);
        app(KelolaKegiatan::class)->ubah(2, $k, $this->data(['versi' => $k->versiForm()]));
    }
    public function test_tenggat_tidak_boleh_diperpendek(): void
    {
        $k = $this->ubahStatusKegiatan($this->buat(), 'terbitkan');
        $this->expectException(ValidationException::class);
        $this->ubahStatusKegiatan($k, 'perpanjang', ['tenggat_baru' => '2030-01-10T11:00']);
    }
    public function test_perpanjangan_tidak_membuka_status_ditutup(): void
    {
        $k = $this->ubahStatusKegiatan($this->ubahStatusKegiatan($this->buat(), 'terbitkan'), 'tutup');
        $k = $this->ubahStatusKegiatan($k, 'perpanjang', ['tenggat_baru' => '2030-01-10T13:00']);
        $this->assertSame(Kegiatan::DITUTUP, $k->status);
        $this->assertFalse($k->jendelaTerbuka());
        $k = $this->ubahStatusKegiatan($k, 'bukaKembali');
        $this->assertTrue($k->jendelaTerbuka());
    }
    public function test_pemulihan_pascaterbit_tidak_kembali_ke_draf(): void
    {
        $k = $this->ubahStatusKegiatan($this->ubahStatusKegiatan($this->buat(), 'terbitkan'), 'arsipkan');
        $k = $this->ubahStatusKegiatan($k, 'pulihkan');
        $this->assertSame(Kegiatan::DITUTUP, $k->status);
        $this->assertNotNull($k->terbit_at);
    }
    public function test_revisi_lama_tidak_menimpa_draf_baru(): void
    {
        $k = $this->buat();
        $versi = $k->versiForm();
        app(KelolaKegiatan::class)->ubah(2, $k, $this->data(['versi' => $versi, 'judul' => 'Judul diperbarui']));
        $this->expectException(ValidationException::class);
        app(KelolaKegiatan::class)->ubah(2, $k, $this->data(['versi' => $versi]));
    }
}
