<?php

namespace Tests\Unit;

use App\Services\AturanJenisSurat;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class JenisSuratAturanTest extends TestCase
{
    public function test_kode_dinormalisasi_dan_syarat_kosong_menjadi_null(): void
    {
        $data = AturanJenisSurat::validasiIsi(['kode' => ' aktif_kuliah ', 'nama' => ' Surat Aktif Kuliah ', 'syarat' => '  '], true);
        $this->assertSame('AKTIF_KULIAH', $data['kode']);
        $this->assertSame('Surat Aktif Kuliah', $data['nama']);
        $this->assertNull($data['syarat']);
    }
    public function test_kode_dengan_spasi_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanJenisSurat::validasiIsi(['kode' => 'SURAT AKTIF', 'nama' => 'Surat Aktif Kuliah'], true);
    }
    public function test_nama_dengan_karakter_kontrol_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanJenisSurat::validasiIsi(['kode' => 'AKTIF_KULIAH', 'nama' => "Surat\nAktif Kuliah"], true);
    }
    public function test_kode_tidak_dapat_dikirim_pada_perubahan(): void
    {
        $this->expectException(ValidationException::class);
        AturanJenisSurat::validasiIsi(['kode' => 'LAIN', 'nama' => 'Surat Lain'], false);
    }
}
