<?php

namespace App\Services;

use App\Models\KalenderAkademik;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class GridKalender
{
    public function susun(Collection $agenda, CarbonImmutable $bulan): array
    {
        $awal = $bulan->startOfMonth()->startOfWeek(1);
        $akhir = $bulan->endOfMonth()->endOfWeek(0)->startOfDay();
        $hari = [];
        for ($d = $awal; $d->lteTo($akhir); $d = $d->addDay()) {
            $besok = $d->addDay();
            $items = $agenda->filter(fn(KalenderAkademik $k) => $k->mulai_at->lessThan($besok) && $k->selesai_at->greaterThan($d))->values();
            $hari[] = ['tanggal' => $d, 'bulan_ini' => $d->format('Y-m') === $bulan->format('Y-m'), 'agenda' => $items];
        }
        return array_chunk($hari, 7);
    }
}
