<?php

namespace App\Services;

use App\Actions\KelolaNotifikasi;
use App\Models\Kegiatan;

final class NotifikasiTugasBaru
{
    public function __construct(
        private readonly AksesPengumpulan $aksesPengumpulan,
        private readonly KelolaNotifikasi $kelolaNotifikasi
    ) {
    }

    /**
     * Mengirim notifikasi ke mahasiswa aktif yang benar-benar
     * terdaftar pada kelas kegiatan.
     */
    public function kirim(Kegiatan $kegiatan): int
    {
        $penerima = $this->aksesPengumpulan
            ->peserta((int) $kegiatan->kelas_kuliah_id)
            ->select('u.id')
            ->distinct()
            ->orderBy('u.id')
            ->pluck('u.id');

        $jumlah = 0;

        foreach ($penerima as $userId) {
            if ($this->kelolaNotifikasi->kirim(
                (int) $userId,
                'kegiatan',
                (int) $kegiatan->id
            )) {
                $jumlah++;
            }
        }

        return $jumlah;
    }
}