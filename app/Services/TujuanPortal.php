<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Role;
use App\Models\User;

final class TujuanPortal
{
    public function namaRoute(User $user): ?string
    {
        $user = User::query()
            ->with([
                'roles:id,kode',
                'dosen:id,user_id,status',
                'mahasiswa:id,user_id',
            ])
            ->whereKey($user->getAuthIdentifier())
            ->where('status', User::STATUS_AKTIF)
            ->first();

        if (! $user) {
            return null;
        }

        $peran = $user->roles->pluck('kode');

        if ($peran->contains(Role::ADMIN_AKADEMIK)) {
            return 'admin.dashboard';
        }

        if ($peran->contains(Role::ADMIN_KEUANGAN)) {
            return 'keuangan.dashboard';
        }

        if (
            $peran->contains(Role::DOSEN)
            && $user->dosen
            && $user->dosen->status === Dosen::AKTIF
        ) {
            return 'portal.dosen.kelas.index';
        }

        if (
            $peran->contains(Role::MAHASISWA)
            && $user->mahasiswa
        ) {
            return 'portal.mahasiswa.index';
        }

        return null;
    }

    public function dapatMasuk(User $user): bool
    {
        return $this->namaRoute($user) !== null;
    }
}