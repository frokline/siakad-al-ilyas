<?php

namespace Tests\Unit;

use App\Models\Kegiatan;
use App\Services\WaktuKegiatan;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class KegiatanAturanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['siakad.timezone' => 'Asia/Makassar']);
    }
    public function test_waktu_wita_dikonversi_ke_utc(): void
    {
        $waktu = WaktuKegiatan::dariForm('2030-01-10T10:00', 'buka_lokal');
        $this->assertSame('2030-01-10 02:00:00', $waktu->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $waktu->timezoneName);
    }
    public function test_tanggal_kalender_tidak_valid_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        WaktuKegiatan::dariForm('2030-02-30T10:00', 'buka_lokal');
    }
    public function test_waktu_dengan_zona_sisipan_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        WaktuKegiatan::dariForm('2030-01-10T10:00+07:00', 'buka_lokal');
    }
    public function test_batas_mulai_inklusif_dan_tenggat_eksklusif(): void
    {
        $k = new Kegiatan();
        $k->status = Kegiatan::TERBIT;
        $k->terbit_at = '2030-01-09 02:00:00';
        $k->buka_at = '2030-01-10 02:00:00';
        $k->tenggat_at = '2030-01-10 04:00:00';
        $this->assertFalse($k->jendelaTerbuka(CarbonImmutable::parse('2030-01-10 01:59:59', 'UTC')));
        $this->assertTrue($k->jendelaTerbuka(CarbonImmutable::parse('2030-01-10 02:00:00', 'UTC')));
        $this->assertTrue($k->jendelaTerbuka(CarbonImmutable::parse('2030-01-10 03:59:59', 'UTC')));
        $this->assertFalse($k->jendelaTerbuka(CarbonImmutable::parse('2030-01-10 04:00:00', 'UTC')));
        $k->status = Kegiatan::DITUTUP;
        $this->assertFalse($k->jendelaTerbuka(CarbonImmutable::parse('2030-01-10 03:00:00', 'UTC')));
    }
    public function test_revisi_mengubah_token_form(): void
    {
        $k = new Kegiatan();
        $k->id = 1;
        $k->revisi = 1;
        $token = $k->versiForm();
        $k->revisi = 2;
        $this->assertSame(64, strlen($token));
        $this->assertNotSame($token, $k->versiForm());
    }
}
