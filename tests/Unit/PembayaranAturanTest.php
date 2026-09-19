<?php

namespace Tests\Unit;

use App\Services\AturanPembayaran;
use App\Services\TujuanPembayaran;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PembayaranAturanTest extends TestCase
{
    private function input(array $ubah = []): array
    {
        return array_replace([
            'form_token' => (string) Str::uuid(),
            'versi_tagihan' => str_repeat('a', 64),
            'versi_tujuan' => str_repeat('b', 64),
            'bukti_berkas_id' => '11',
            'nominal_diajukan' => '250000',
            'tanggal_transfer' => '2020-01-01',
            'konfirmasi' => 1
        ], $ubah);
    }
    public function test_normalisasi_nominal_dan_referensi(): void
    {
        $v = AturanPembayaran::data($this->input(['referensi_bank' => '  ']));
        $this->assertSame('250000.00', $v['nominal_diajukan']);
        $this->assertNull($v['referensi_bank']);
        $this->assertSame(11, $v['bukti_berkas_id']);
    }
    public function test_transfer_masa_depan_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanPembayaran::data($this->input(['tanggal_transfer' => now('Asia/Makassar')->addDay()->format('Y-m-d')]));
    }
    public function test_status_suntikan_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanPembayaran::data($this->input(['status' => 'diterima']));
    }
    public function test_konfirmasi_wajib(): void
    {
        $this->expectException(ValidationException::class);
        AturanPembayaran::data($this->input(['konfirmasi' => 0]));
    }
    public function test_rekening_kosong_memblokir_pengajuan(): void
    {
        config(['pembayaran.tujuan_transfer' => '']);
        $this->expectException(ValidationException::class);
        TujuanPembayaran::teks();
    }
    public function test_versi_rekening_berubah_ketika_rekening_diganti(): void
    {
        config(['pembayaran.tujuan_transfer' => 'BANK UJI / 000 / UJI']);
        $v = TujuanPembayaran::versi();
        config(['pembayaran.tujuan_transfer' => 'BANK UJI / 111 / UJI']);
        $this->assertNotSame($v, TujuanPembayaran::versi());
    }
}
