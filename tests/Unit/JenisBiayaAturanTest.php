<?php

namespace Tests\Unit;

use App\Services\AturanJenisBiaya;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class JenisBiayaAturanTest extends TestCase
{
    public function test_kode_dinormalisasi_dan_keterangan_kosong_menjadi_null(): void
    {
        $data = AturanJenisBiaya::validasiIsi(['kode' => ' spp ', 'nama' => ' SPP Bulanan ', 'keterangan' => '  '], true);
        $this->assertSame('SPP', $data['kode']);
        $this->assertSame('SPP Bulanan', $data['nama']);
        $this->assertNull($data['keterangan']);
    }
    public function test_kode_dengan_spasi_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanJenisBiaya::validasiIsi(['kode' => 'SPP BULANAN', 'nama' => 'SPP Bulanan'], true);
    }
    public function test_nama_dengan_karakter_kontrol_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanJenisBiaya::validasiIsi(['kode' => 'SPP', 'nama' => "SPP\nBulanan"], true);
    }
    public function test_kode_tidak_dapat_dikirim_pada_perubahan(): void
    {
        $this->expectException(ValidationException::class);
        AturanJenisBiaya::validasiIsi(['kode' => 'LAIN', 'nama' => 'Biaya Lain'], false);
    }
}
