<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AksesKeuangan
{
    public function lihatMaster(User $user): bool
    {
        return $this->memiliki($user, ['admin_keuangan', 'admin_akademik']);
    }
    public function kelola(User $user): bool
    {
        return $this->memiliki($user, ['admin_keuangan']);
    }
    private function memiliki(User $user, array $kode): bool
    {
        // Selalu baca status/peran terbaru, bukan relasi yang mungkin tersimpan di sesi/cache model.
        return User::query()->whereKey($user->id)->where('status', 'aktif')
            ->whereHas('roles', fn(Builder $q) => $q->whereIn('roles.kode', $kode))->exists();
    }
}
