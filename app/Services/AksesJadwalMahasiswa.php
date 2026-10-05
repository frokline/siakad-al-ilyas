<?php

namespace App\Services;

use App\Models\JadwalKuliah;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class AksesJadwalMahasiswa
{
    public function __construct(
        private AksesKrsMahasiswa $aksesKrs,
        private AksesMateri $aksesMateri
    ) {
    }

    /**
     * Memastikan pengguna adalah mahasiswa aktif
     * yang mempunyai profil mahasiswa.
     */
    public function masuk(User $user): bool
    {
        return $this->aksesKrs->masuk($user);
    }

    /**
     * Membatasi jadwal hanya dari kelas yang:
     *
     * - tercantum dalam KRS mahasiswa;
     * - KRS sudah disahkan;
     * - detail KRS berstatus aktif;
     * - registrasi dan riwayat studi masih aktif;
     * - jadwal masih berstatus aktif.
     */
    public function batasi(
        Builder $query,
        User $user
    ): Builder {
        if (! $this->masuk($user)) {
            return $query->whereRaw('1 = 0');
        }

        $kelasPeserta = $this->aksesMateri
            ->kelasPeserta($user)
            ->select('kelas_kuliah.id');

        return $query
            ->where('jadwal_kuliah.aktif', true)
            ->whereIn(
                'jadwal_kuliah.kelas_kuliah_id',
                $kelasPeserta
            );
    }

    /**
     * Memastikan satu jadwal boleh dibaca mahasiswa.
     */
    public function lihat(
        User $user,
        JadwalKuliah $jadwal
    ): bool {
        return $this->batasi(
            JadwalKuliah::query(),
            $user
        )
            ->whereKey($jadwal->id)
            ->exists();
    }
}