<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final class PolaJadwal
{
    public static function tanggalPertama(int $hari, string $mulai, string $selesai): ?CarbonImmutable
    {
        if ($hari < 1 || $hari > 7) {
            throw new InvalidArgumentException('Hari harus 1 sampai 7.');
        }
        $awal = self::tanggal($mulai);
        $akhir = self::tanggal($selesai);
        if ($awal->gt($akhir)) {
            return null;
        }
        $selisih = ($hari - $awal->dayOfWeekIso + 7) % 7;
        $pertama = $awal->addDays($selisih);

        return $pertama->lte($akhir) ? $pertama : null;
    }

    /** Mengembalikan tanggal benturan pertama; waktu memakai interval [mulai, selesai). */
    public static function benturanPertama(array $a, array $b): ?CarbonImmutable
    {
        if (
            (int) $a['hari'] !== (int) $b['hari']
            || $a['jam_mulai'] >= $b['jam_selesai']
            || $b['jam_mulai'] >= $a['jam_selesai']
        ) {
            return null;
        }

        return self::tanggalPertama(
            (int) $a['hari'],
            max($a['berlaku_mulai'], $b['berlaku_mulai']),
            min($a['berlaku_selesai'], $b['berlaku_selesai'])
        );
    }

    private static function tanggal(string $nilai): CarbonImmutable
    {
        if (! preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $nilai)) {
            throw new InvalidArgumentException('Tanggal harus berformat Y-m-d.');
        }
        $tanggal = CarbonImmutable::createFromFormat('!Y-m-d', $nilai, 'UTC');
        if ($tanggal === null || $tanggal === false || $tanggal->format('Y-m-d') !== $nilai) {
            throw new InvalidArgumentException('Tanggal tidak valid.');
        }
        return $tanggal;
    }
}
