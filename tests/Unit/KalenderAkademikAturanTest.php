<?php

namespace Tests\Unit;

use App\Models\KalenderAkademik;
use App\Services\AturanKalender;
use App\Services\GridKalender;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class KalenderAkademikAturanTest extends TestCase
{
    private function input(array $ubah = []): array
    {
        return array_replace([
            'periode_akademik_id' => 1,
            'program_studi_id' => '',
            'judul' => 'Agenda kampus',
            'jenis' => 'lainnya',
            'mulai_lokal' => '2026-09-20T00:00',
            'selesai_lokal' => '2026-09-21T00:00',
            'form_token' => (string) Str::uuid()
        ], $ubah);
    }
    public function test_wita_dikonversi_ke_utc_dan_sasaran_kosong_null(): void
    {
        $v = AturanKalender::isi($this->input(), true);
        $this->assertSame('2026-09-19 16:00:00', $v['isi']['mulai_at']);
        $this->assertSame('2026-09-20 16:00:00', $v['isi']['selesai_at']);
        $this->assertNull($v['isi']['program_studi_id']);
    }
    public function test_waktu_sama_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanKalender::isi($this->input(['selesai_lokal' => '2026-09-20T00:00']), true);
    }
    public function test_tanggal_tidak_valid_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanKalender::isi($this->input(['mulai_lokal' => '2026-02-30T00:00']), true);
    }
    public function test_tahun_di_luar_batas_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanKalender::isi($this->input(['mulai_lokal' => '1999-12-31T00:00']), true);
    }
    public function test_durasi_lebih_dari_366_hari_ditolak(): void
    {
        $this->expectException(ValidationException::class);
        AturanKalender::isi($this->input(['selesai_lokal' => '2028-01-01T00:00']), true);
    }
    public function test_status_dan_pembuat_tidak_dapat_dipalsukan(): void
    {
        $this->expectException(ValidationException::class);
        AturanKalender::isi($this->input(['status' => 'terbit', 'pembuat_id' => 9]), true);
    }
    public function test_grid_tengah_malam_tidak_masuk_hari_berikutnya(): void
    {
        config(['app.timezone' => 'UTC']);
        $k = new KalenderAkademik();
        $k->mulai_at = CarbonImmutable::parse('2026-09-20 00:00', KalenderAkademik::ZONA)->utc();
        $k->selesai_at = CarbonImmutable::parse('2026-09-21 00:00', KalenderAkademik::ZONA)->utc();
        $grid = (new GridKalender())->susun(collect([$k]), CarbonImmutable::parse('2026-09-01', KalenderAkademik::ZONA));
        $hari = collect($grid)->flatMap(fn($pekan) => $pekan);
        $this->assertCount(1, $hari->first(fn($h) => $h['tanggal']->format('Y-m-d') === '2026-09-20')['agenda']);
        $this->assertCount(0, $hari->first(fn($h) => $h['tanggal']->format('Y-m-d') === '2026-09-21')['agenda']);
    }
    public function test_grid_dimulai_senin_dan_berakhir_minggu(): void
    {
        $grid = (new GridKalender())->susun(collect(), CarbonImmutable::parse('2026-09-01', KalenderAkademik::ZONA));
        $this->assertSame(1, $grid[0][0]['tanggal']->dayOfWeek);
        $akhir = $grid[array_key_last($grid)];
        $this->assertCount(7, $akhir);
        $this->assertSame(0, $akhir[6]['tanggal']->dayOfWeek);
    }
}
