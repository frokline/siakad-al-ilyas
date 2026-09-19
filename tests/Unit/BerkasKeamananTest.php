<?php

namespace Tests\Unit;

use App\Models\Berkas;
use App\Models\User;
use App\Policies\BerkasPolicy;
use App\Services\AksesBerkas;
use App\Services\PemeriksaBerkas;
use App\Services\PenyimpananBerkas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BerkasKeamananTest extends TestCase
{
    private array $sementara = [];
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWZkAAAAASUVORK5CYII=';

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->detectEnvironment(fn() => 'testing');
        config(['berkas.scanner' => 'none', 'berkas.maks_kib' => 20480, 'berkas.maks_piksel' => 20000000]);
    }
    protected function tearDown(): void
    {
        foreach ($this->sementara as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        parent::tearDown();
    }
    private function unggahan(string $nama, string $isi): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'uji_berkas_');
        $this->sementara[] = $path;
        file_put_contents($path, $isi);
        return new UploadedFile($path, $nama, null, UPLOAD_ERR_OK, true);
    }
    public function test_png_diperiksa_dari_isi_dan_hashnya_sesuai(): void
    {
        $bytes = base64_decode(self::PNG, true);
        $hasil = app(PemeriksaBerkas::class)->periksa($this->unggahan('gambar.png', $bytes));
        $this->assertSame('image/png', $hasil['mime_type']);
        $this->assertSame(strlen($bytes), $hasil['ukuran_byte']);
        $this->assertSame(hash('sha256', $bytes), $hasil['sha256']);
        $this->assertSame('format', $hasil['pemeriksaan']);
    }
    public function test_svg_bernama_png_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        app(PemeriksaBerkas::class)->periksa($this->unggahan('gambar.png', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'));
    }
    public function test_kode_php_bernama_pdf_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        app(PemeriksaBerkas::class)->periksa($this->unggahan('dokumen.pdf', '<?php echo "uji";'));
    }
    public function test_isi_png_dengan_ekstensi_php_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        app(PemeriksaBerkas::class)->periksa($this->unggahan('gambar.php', base64_decode(self::PNG, true)));
    }
    public function test_produksi_tidak_melewati_pemindai_yang_belum_dipasang(): void
    {
        $this->app->detectEnvironment(fn() => 'production');
        $this->expectException(ValidationException::class);
        app(PemeriksaBerkas::class)->periksa($this->unggahan('gambar.png', base64_decode(self::PNG, true)));
    }
    public function test_izin_portal_tidak_memberi_akses_ke_berkas_orang_lain(): void
    {
        // Tanpa database: izin masuk diasumsikan benar, aturan kepemilikan tetap harus menolak.
        $akses = \Mockery::mock(AksesBerkas::class);
        $akses->shouldReceive('masuk')->andReturn(true);
        $this->app->instance(AksesBerkas::class, $akses);
        $user = new User();
        $user->id = 10;
        $file = new Berkas();
        $file->diunggah_oleh = 20;
        $file->status = Berkas::TERSEDIA;
        $policy = new BerkasPolicy();
        $this->assertFalse($policy->view($user, $file));
        $this->assertFalse($policy->download($user, $file));
        $this->assertFalse($policy->nonaktifkan($user, $file));
    }
    public function test_pemilik_tidak_bisa_mengunduh_berkas_yang_belum_tersedia(): void
    {
        $akses = \Mockery::mock(AksesBerkas::class);
        $akses->shouldReceive('masuk')->andReturn(true);
        $this->app->instance(AksesBerkas::class, $akses);
        $user = new User();
        $user->id = 10;
        $file = new Berkas();
        $file->diunggah_oleh = 10;
        $policy = new BerkasPolicy();
        foreach ([Berkas::MENUNGGU, Berkas::DITOLAK, Berkas::DIHAPUS] as $status) {
            $file->status = $status;
            $this->assertFalse($policy->download($user, $file));
        }
        $file->status = Berkas::TERSEDIA;
        $this->assertTrue($policy->download($user, $file));
    }
    public function test_objek_berubah_dengan_ukuran_sama_tetap_ditolak(): void
    {
        Storage::fake('berkas_local');
        $file = new Berkas();
        $file->storage_disk = 'berkas_local';
        $file->object_key = 'siakad/berkas/2026/09/11111111-1111-4111-8111-111111111111.pdf';
        $file->ukuran_byte = 3;
        $file->sha256 = hash('sha256', 'ABC');
        $file->pemeriksaan = 'format';
        Storage::disk('berkas_local')->put($file->object_key, 'XYZ');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Integritas objek tidak sesuai.');
        app(PenyimpananBerkas::class)->buka($file);
    }
}
