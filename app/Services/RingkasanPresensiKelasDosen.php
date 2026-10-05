<?php

namespace App\Services;

use App\Models\KelasKuliah;
use App\Models\Presensi;
use App\Models\PresensiPertemuan;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RingkasanPresensiKelasDosen
{
    public function __construct(
        private readonly AksesKelasDosen $aksesKelas
    ) {
    }

    /**
     * Menghasilkan ringkasan seluruh presensi pada satu kelas
     * yang memang boleh dilihat oleh dosen.
     *
     * @return array{
     *     daftar: array{
     *         total: int,
     *         terbuka: int,
     *         ditutup: int
     *     },
     *     status: array<string, int>,
     *     jumlah_baris: int,
     *     jumlah_peserta_tercatat: int,
     *     persentase_hadir: float
     * }
     */
    public function ambil(
        User $user,
        KelasKuliah $kelas
    ): array {
        if (! $this->aksesKelas->lihat($user, $kelas)) {
            throw (new ModelNotFoundException())
                ->setModel(KelasKuliah::class, [
                    $kelas->getKey(),
                ]);
        }

        $daftarDasar = PresensiPertemuan::query()
            ->where(
                'presensi_pertemuan.kelas_kuliah_id',
                $kelas->getKey()
            );

        $jumlahDaftar = (clone $daftarDasar)->count();

        $daftarTerbuka = (clone $daftarDasar)
            ->where(
                'presensi_pertemuan.status',
                PresensiPertemuan::TERBUKA
            )
            ->count();

        $daftarDitutup = (clone $daftarDasar)
            ->where(
                'presensi_pertemuan.status',
                PresensiPertemuan::DITUTUP
            )
            ->count();

        $barisDasar = Presensi::query()
            ->where(
                'presensi.kelas_kuliah_id',
                $kelas->getKey()
            )
            ->whereHas(
                'daftar',
                function ($daftar) use ($kelas): void {
                    $daftar->where(
                        'kelas_kuliah_id',
                        $kelas->getKey()
                    );
                }
            );

        $jumlahStatus = [];

        foreach (array_keys(Presensi::STATUS) as $status) {
            $jumlahStatus[$status] = (clone $barisDasar)
                ->where('presensi.status', $status)
                ->count();
        }

        $jumlahBaris = array_sum($jumlahStatus);

        $jumlahPesertaTercatat = (clone $barisDasar)
            ->distinct()
            ->count('presensi.detail_krs_id');

        $persentaseHadir = $jumlahBaris > 0
            ? round(
                (
                    $jumlahStatus['hadir']
                    / $jumlahBaris
                ) * 100,
                2
            )
            : 0.0;

        return [
            'daftar' => [
                'total' => $jumlahDaftar,
                'terbuka' => $daftarTerbuka,
                'ditutup' => $daftarDitutup,
            ],
            'status' => $jumlahStatus,
            'jumlah_baris' => $jumlahBaris,
            'jumlah_peserta_tercatat' =>
                $jumlahPesertaTercatat,
            'persentase_hadir' => $persentaseHadir,
        ];
    }
}