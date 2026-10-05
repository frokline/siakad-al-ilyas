<?php

namespace App\Services;

use App\Models\DetailKrs;
use App\Models\KelasKuliah;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class AksesPesertaKelasDosen
{
    public function __construct(
        private readonly AksesKelasDosen $aksesKelas
    ) {
    }

    /**
     * Memastikan akun mempunyai akses portal dosen.
     */
    public function masuk(User $user): bool
    {
        return $this->aksesKelas->masuk($user);
    }

    /**
     * Memastikan kelas berada dalam penugasan dosen.
     */
    public function lihatKelas(
        User $user,
        KelasKuliah $kelas
    ): bool {
        return $this->aksesKelas->lihat($user, $kelas);
    }

    /**
     * Membatasi query pada peserta disahkan dari kelas
     * yang boleh dilihat dosen.
     */
    public function batasi(
        Builder $query,
        User $user,
        KelasKuliah $kelas
    ): Builder {
        if (! $this->lihatKelas($user, $kelas)) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where(
                'detail_krs.kelas_kuliah_id',
                $kelas->getKey()
            )
            ->disahkan();
    }

    /**
     * Membatasi query pada peserta yang masih aktif.
     */
    public function batasiAktif(
        Builder $query,
        User $user,
        KelasKuliah $kelas
    ): Builder {
        if (! $this->lihatKelas($user, $kelas)) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where(
                'detail_krs.kelas_kuliah_id',
                $kelas->getKey()
            )
            ->pesertaAktif();
    }

    /**
     * Memastikan satu peserta termasuk dalam kelas dosen.
     */
    public function lihatPeserta(
        User $user,
        KelasKuliah $kelas,
        DetailKrs $peserta
    ): bool {
        return $this->batasi(
            DetailKrs::query(),
            $user,
            $kelas
        )
            ->whereKey($peserta->getKey())
            ->exists();
    }

    /**
     * Menghitung seluruh riwayat peserta yang disahkan.
     */
    public function jumlahRiwayat(
        User $user,
        KelasKuliah $kelas
    ): int {
        return $this->batasi(
            DetailKrs::query(),
            $user,
            $kelas
        )->count();
    }

    /**
     * Menghitung peserta yang masih aktif.
     */
    public function jumlahAktif(
        User $user,
        KelasKuliah $kelas
    ): int {
        return $this->batasiAktif(
            DetailKrs::query(),
            $user,
            $kelas
        )->count();
    }
}