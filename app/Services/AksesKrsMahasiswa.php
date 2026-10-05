<?php

namespace App\Services;

use App\Models\Krs;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class AksesKrsMahasiswa
{
    /**
     * Memastikan pengguna aktif, memiliki role mahasiswa,
     * dan mempunyai profil mahasiswa.
     */
    public function masuk(User $user): bool
    {
        if (! $user->exists) {
            return false;
        }

        return User::query()
            ->whereKey($user->id)
            ->where('status', User::STATUS_AKTIF)
            ->whereHas(
                'roles',
                fn(Builder $query): Builder => $query->where(
                    'roles.kode',
                    Role::MAHASISWA
                )
            )
            ->whereHas('mahasiswa')
            ->exists();
    }

    /**
     * Membatasi query agar hanya menghasilkan KRS
     * milik mahasiswa yang sedang masuk.
     */
    public function batasi(Builder $query, User $user): Builder
    {
        if (! $this->masuk($user)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            'registrasiSemester.riwayatStudi.mahasiswa',
            fn(Builder $mahasiswa): Builder => $mahasiswa->where(
                'user_id',
                $user->id
            )
        );
    }

    /**
     * Memeriksa apakah mahasiswa boleh melihat satu KRS.
     */
    public function lihat(User $user, Krs $krs): bool
    {
        return $this->batasi(
            Krs::query(),
            $user
        )
            ->whereKey($krs->id)
            ->exists();
    }

    /**
     * Mahasiswa hanya boleh mencetak KRS yang sudah disahkan.
     */
    public function cetak(User $user, Krs $krs): bool
    {
        return $krs->status === Krs::DISAHKAN
            && $this->lihat($user, $krs);
    }
}