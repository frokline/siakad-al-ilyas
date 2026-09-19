<?php

namespace Tests\Unit;

use App\Rules\TautanPertemuanAman;
use App\Support\PolaJadwal;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PolaJadwalTest extends TestCase
{
    private function pola(array $ubah = []): array
    {
        return array_merge([
            'hari' => 1,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:30:00',
            'berlaku_mulai' => '2026-09-01',
            'berlaku_selesai' => '2026-09-30',
        ], $ubah);
    }

    public function test_menemukan_benturan_mingguan_pada_tanggal_nyata(): void
    {
        $a = $this->pola();
        $b = $this->pola(['jam_mulai' => '09:00:00', 'jam_selesai' => '10:00:00']);
        self::assertSame('2026-09-07', PolaJadwal::benturanPertama($a, $b)?->toDateString());
        self::assertSame('2026-09-07', PolaJadwal::benturanPertama($b, $a)?->toDateString());
    }

    public function test_jam_bersebelahan_tidak_bentrok(): void
    {
        $a = $this->pola();
        $b = $this->pola(['jam_mulai' => '09:30:00', 'jam_selesai' => '10:30:00']);
        self::assertNull(PolaJadwal::benturanPertama($a, $b));
        self::assertNull(PolaJadwal::benturanPertama($b, $a));
    }

    public function test_jam_sama_pada_hari_berbeda_tidak_bentrok(): void
    {
        self::assertNull(PolaJadwal::benturanPertama($this->pola(), $this->pola(['hari' => 2])));
    }

    public function test_irisan_tanggal_tanpa_hari_yang_sama_tidak_bentrok(): void
    {
        $a = $this->pola(['berlaku_mulai' => '2026-09-07', 'berlaku_selesai' => '2026-09-13']);
        $b = $this->pola(['berlaku_mulai' => '2026-09-08', 'berlaku_selesai' => '2026-09-14']);
        self::assertSame('2026-09-07', PolaJadwal::tanggalPertama(1, $a['berlaku_mulai'], $a['berlaku_selesai'])?->toDateString());
        self::assertSame('2026-09-14', PolaJadwal::tanggalPertama(1, $b['berlaku_mulai'], $b['berlaku_selesai'])?->toDateString());
        self::assertNull(PolaJadwal::benturanPertama($a, $b));
    }

    public function test_batas_tanggal_terakhir_termasuk_dalam_pola(): void
    {
        $a = $this->pola(['berlaku_selesai' => '2026-09-14']);
        $b = $this->pola(['berlaku_mulai' => '2026-09-14']);
        self::assertSame('2026-09-14', PolaJadwal::benturanPertama($a, $b)?->toDateString());
    }

    public function test_rentang_terpisah_tidak_bentrok(): void
    {
        $a = $this->pola(['berlaku_selesai' => '2026-09-07']);
        $b = $this->pola(['berlaku_mulai' => '2026-09-14']);
        self::assertNull(PolaJadwal::benturanPertama($a, $b));
    }

    public function test_hari_tunggal_dan_tahun_kabisat(): void
    {
        self::assertSame('2028-02-29', PolaJadwal::tanggalPertama(2, '2028-02-29', '2028-02-29')?->toDateString());
        self::assertNull(PolaJadwal::tanggalPertama(1, '2028-02-29', '2028-02-29'));
    }

    public function test_hari_di_luar_rentang_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PolaJadwal::tanggalPertama(8, '2026-09-01', '2026-09-30');
    }

    public function test_tanggal_yang_tidak_ada_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PolaJadwal::tanggalPertama(1, '2026-02-30', '2026-03-10');
    }

    public function test_tautan_https_valid_diterima(): void
    {
        self::assertTrue(TautanPertemuanAman::sesuai('https://meet.example.org/kelas?kode=abc123'));
    }

    public function test_tautan_berbahaya_atau_tidak_valid_ditolak(): void
    {
        foreach (
            [
                'javascript:alert(1)',
                'data:text/html,test',
                '//meet.example.org/kelas',
                'http://meet.example.org/kelas',
                'https://nama:rahasia@meet.example.org/kelas',
                "https://meet.example.org/kelas\n",
                'https://meet.example.org\\@evil.example',
                'https://meet.example.org/ruang kelas',
                '',
                null,
                [],
            ] as $tautan
        ) {
            self::assertFalse(TautanPertemuanAman::sesuai($tautan));
        }
    }
}
