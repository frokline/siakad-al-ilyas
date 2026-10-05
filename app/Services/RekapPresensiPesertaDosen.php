<?php

namespace App\Services;

use App\Models\DetailKrs;
use App\Models\KelasKuliah;
use App\Models\Presensi;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

final class RekapPresensiPesertaDosen
{
    public function __construct(
        private readonly AksesPesertaKelasDosen $aksesPeserta
    ) {
    }

    /**
     * Mengambil rekap dan riwayat presensi seorang peserta.
     *
     * @return array{
     *     jumlah: array<string, int>,
     *     total: int,
     *     total_tercatat: int,
     *     persentase_hadir: float,
     *     riwayat: Collection
     * }
     */
    public function ambil(
        User $user,
        KelasKuliah $kelas,
        DetailKrs $peserta
    ): array {
        if (
            ! $this->aksesPeserta->lihatPeserta(
                $user,
                $kelas,
                $peserta
            )
        ) {
            throw (new ModelNotFoundException())
                ->setModel(DetailKrs::class, [
                    $peserta->getKey(),
                ]);
        }

        $riwayat = Presensi::query()
            ->where(
                'presensi.kelas_kuliah_id',
                $kelas->getKey()
            )
            ->where(
                'presensi.detail_krs_id',
                $peserta->getKey()
            )
            ->whereHas(
                'daftar',
                function ($daftar) use ($kelas): void {
                    $daftar->where(
                        'kelas_kuliah_id',
                        $kelas->getKey()
                    );
                }
            )
            ->with([
                'daftar.pertemuan',
            ])
            ->orderBy('presensi.id')
            ->get()
            ->sortBy(
                function (Presensi $presensi): array {
                    return [
                        $presensi->daftar?->pertemuan?->nomor
                            ?? PHP_INT_MAX,
                        $presensi->getKey(),
                    ];
                }
            )
            ->values();

        $jumlah = [];

        foreach (array_keys(Presensi::STATUS) as $status) {
            $jumlah[$status] = 0;
        }

        foreach ($riwayat as $presensi) {
            if (array_key_exists($presensi->status, $jumlah)) {
                $jumlah[$presensi->status]++;
            }
        }

        $total = $riwayat->count();
        $totalTercatat = $total - $jumlah[Presensi::BELUM];

        $persentaseHadir = $total > 0
            ? round(
                ($jumlah['hadir'] / $total) * 100,
                2
            )
            : 0.0;

        return [
            'jumlah' => $jumlah,
            'total' => $total,
            'total_tercatat' => $totalTercatat,
            'persentase_hadir' => $persentaseHadir,
            'riwayat' => $riwayat,
        ];
    }
}