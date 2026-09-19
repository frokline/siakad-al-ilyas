<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class WaktuPertemuan
{
    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    public static function dariForm(string $tanggal, string $mulai, string $selesai, string $zona): array
    {
        if (
            ! in_array($zona, DateTimeZone::listIdentifiers(), true)
            || ! preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $tanggal)
            || ! preg_match('/\A(?:[01][0-9]|2[0-3]):[0-5][0-9]\z/', $mulai)
            || ! preg_match('/\A(?:[01][0-9]|2[0-3]):[0-5][0-9]\z/', $selesai)
            || $mulai >= $selesai
        ) {
            throw new InvalidArgumentException('Tanggal/jam tidak valid. Sesi harus selesai pada hari yang sama.');
        }
        $hasil = [];
        foreach ([$mulai, $selesai] as $jam) {
            $teks = $tanggal . ' ' . $jam;
            $waktu = CarbonImmutable::createFromFormat('!Y-m-d H:i', $teks, $zona);
            if (! $waktu || $waktu->format('Y-m-d H:i') !== $teks) {
                throw new InvalidArgumentException('Tanggal/jam tidak ada pada zona waktu kampus.');
            }
            $hasil[] = $waktu->setTimezone('UTC');
        }
        return $hasil;
    }

    public static function polaTanggal(CarbonImmutable $mulai, CarbonImmutable $selesai, string $zona): array
    {
        $awal = $mulai->setTimezone($zona);
        $akhir = $selesai->setTimezone($zona);
        if ($awal->toDateString() !== $akhir->toDateString() || ! $awal->lt($akhir)) {
            throw new InvalidArgumentException('Rentang harus berurutan dalam satu tanggal lokal.');
        }
        return [
            'hari' => $awal->dayOfWeekIso,
            'jam_mulai' => $awal->format('H:i:s'),
            'jam_selesai' => $akhir->format('H:i:s'),
            'berlaku_mulai' => $awal->toDateString(),
            'berlaku_selesai' => $awal->toDateString(),
        ];
    }
}
