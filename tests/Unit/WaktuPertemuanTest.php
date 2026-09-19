<?php

namespace Tests\Unit;

use App\Support\WaktuPertemuan;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WaktuPertemuanTest extends TestCase
{
    public function test_waktu_lokal_disimpan_sebagai_utc(): void
    {
        [$mulai, $selesai] = WaktuPertemuan::dariForm('2026-09-14', '08:00', '09:30', 'Asia/Makassar');
        self::assertSame('2026-09-14 00:00:00', $mulai->format('Y-m-d H:i:s'));
        self::assertSame('2026-09-14 01:30:00', $selesai->format('Y-m-d H:i:s'));
    }

    public function test_sesi_tidak_boleh_melewati_tengah_malam(): void
    {
        $this->expectException(InvalidArgumentException::class);
        WaktuPertemuan::dariForm('2026-09-14', '23:00', '00:30', 'Asia/Makassar');
    }

    public function test_tanggal_invalid_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        WaktuPertemuan::dariForm('2026-02-30', '08:00', '09:00', 'Asia/Makassar');
    }

    public function test_pola_tanggal_menentukan_hari_lokal(): void
    {
        $hasil = WaktuPertemuan::polaTanggal(
            CarbonImmutable::parse('2026-09-14 00:00:00', 'UTC'),
            CarbonImmutable::parse('2026-09-14 01:30:00', 'UTC'),
            'Asia/Makassar'
        );
        self::assertSame(1, $hasil['hari']);
        self::assertSame('08:00:00', $hasil['jam_mulai']);
        self::assertSame('09:30:00', $hasil['jam_selesai']);
        self::assertSame('2026-09-14', $hasil['berlaku_mulai']);
    }

    public function test_zona_waktu_tidak_dikenal_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        WaktuPertemuan::dariForm('2026-09-14', '08:00', '09:00', 'Zona/TidakAda');
    }
}
