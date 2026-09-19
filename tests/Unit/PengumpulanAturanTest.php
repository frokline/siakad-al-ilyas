<?php

namespace Tests\Unit;

use App\Models\Berkas;
use App\Models\Kegiatan;
use App\Services\AturanJawaban;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PengumpulanAturanTest extends TestCase
{
    public function test_teks_dinormalisasi_tanpa_mengeksekusi_html(): void
    {
        $this->assertSame("<script>
    alert(1)
</script>\nbaris", AturanJawaban::teks(" <script>
    alert(1)
</script>\r\nbaris "));
        $this->assertNull(AturanJawaban::teks(' '));
    }
    public function test_karakter_kontrol_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanJawaban::teks("jawaban\0tersembunyi");
    }
    public function test_teks_terlalu_panjang_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanJawaban::teks(str_repeat('a', 10001));
    }
    public function test_id_berkas_diurutkan(): void
    {
        $this->assertSame([2, 11], AturanJawaban::ids(['11', '2']));
    }
    public function test_id_ganda_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanJawaban::ids(['11', 11]);
    }
    public function test_id_bukan_angka_utuh_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanJawaban::ids(['1e2']);
    }
    public function test_pemindaian_produksi_tidak_boleh_dilewati(): void
    {
        $this->app->detectEnvironment(fn() => 'production');
        $b = new Berkas();
        $b->forceFill([
            'diunggah_oleh' => 3,
            'status' => 'tersedia',
            'ukuran_byte' => 12,
            'ekstensi' => 'pdf',
            'mime_type' => 'application/pdf',
            'sha256' => str_repeat('a', 64),
            'pemeriksaan' => 'format'
        ]);
        $k = new Kegiatan();
        $k->forceFill(['maks_ukuran_byte' => 1048576, 'ekstensi_diizinkan' => ['pdf']]);
        $this->expectException(ValidationException::class);
        AturanJawaban::berkas($b, $k, 3);
    }
}
