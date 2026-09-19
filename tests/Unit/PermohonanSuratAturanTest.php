<?php

namespace Tests\Unit;

use App\Services\AturanPermohonanSurat;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PermohonanSuratAturanTest extends TestCase
{
    private function dataAwal(array $ubah = []): array
    {
        return array_replace([
            'registrasi_semester_id' => '1',
            'jenis_surat_id' => '1',
            'versi_jenis' => str_repeat('a', 64),
            'form_token' => (string) Str::uuid(),
            'keperluan' => '  Pengajuan untuk keperluan beasiswa.  ',
            'konfirmasi' => '1'
        ], $ubah);
    }
    public function test_normalisasi_dan_validasi_ganda_stabil(): void
    {
        $v = AturanPermohonanSurat::data($this->dataAwal(), true);
        $this->assertSame(1, $v['registrasi_semester_id']);
        $this->assertSame('Pengajuan untuk keperluan beasiswa.', $v['keperluan']);
        $this->assertSame($v, AturanPermohonanSurat::data($v, true));
    }
    public function test_pemohon_dan_status_tidak_dapat_dipalsukan(): void
    {
        $this->expectException(ValidationException::class);
        AturanPermohonanSurat::data($this->dataAwal(['pemohon_id' => 9, 'status' => 'terbit']), true);
    }
    public function test_karakter_kontrol_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanPermohonanSurat::data($this->dataAwal(['keperluan' => "Untuk beasiswa\x00uji"]), true);
    }
    public function test_nomor_surat_dinormalisasi_dan_dibatasi(): void
    {
        $v = AturanPermohonanSurat::data([
            'tujuan' => 'terbit',
            'versi' => str_repeat('a', 64),
            'catatan' => 'Dokumen sudah diperiksa.',
            'konfirmasi' => '1',
            'nomor_surat' => ' 001/ilyas/2026 ',
            'hasil_berkas_id' => '13'
        ], false);
        $this->assertSame('001/ILYAS/2026', $v['nomor_surat']);
        $this->assertSame(13, $v['hasil_berkas_id']);
    }
    public function test_penerbitan_tanpa_pdf_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanPermohonanSurat::data([
            'tujuan' => 'terbit',
            'versi' => str_repeat('a', 64),
            'catatan' => 'Dokumen sudah diperiksa.',
            'konfirmasi' => '1',
            'nomor_surat' => '001/ILYAS/2026'
        ], false);
    }
    public function test_penolakan_tidak_membawa_data_penerbitan(): void
    {
        $v = AturanPermohonanSurat::data([
            'tujuan' => 'ditolak',
            'versi' => str_repeat('a', 64),
            'catatan' => 'Persyaratan belum lengkap.',
            'konfirmasi' => '1',
            'nomor_surat' => 'X',
            'hasil_berkas_id' => 99
        ], false);
        $this->assertArrayNotHasKey('hasil_berkas_id', $v);
        $this->assertArrayNotHasKey('nomor_surat', $v);
    }
}
