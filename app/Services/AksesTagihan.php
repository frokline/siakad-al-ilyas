<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class AksesTagihan
{
    public function petugas(User $user): bool
    {
        return app(AksesKeuangan::class)->kelola($user);
    }

    public function masuk(User $user): bool
    {
        if ($this->petugas($user)) {
            return true;
        }

        return User::query()
            ->whereKey($user->getAuthIdentifier())
            ->where('status', User::STATUS_AKTIF)
            ->whereHas(
                'roles',
                fn (Builder $roles): Builder => $roles->where(
                    'roles.kode',
                    Role::MAHASISWA
                )
            )
            ->whereHas('mahasiswa')
            ->exists();
    }

    public function batasi(Builder $query, User $user): Builder
    {
        if ($this->petugas($user)) {
            return $query;
        }

        if (! $this->masuk($user)) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereIn(
                'tagihan.mahasiswa_id',
                DB::table('mahasiswa')
                    ->select('id')
                    ->where('user_id', $user->getAuthIdentifier())
            )
            ->where(function (Builder $tagihan): void {
                $tagihan
                    ->where('tagihan.status', Tagihan::TERBIT)
                    ->orWhere(
                        fn (Builder $dibatalkan): Builder => $dibatalkan
                            ->where(
                                'tagihan.status',
                                Tagihan::DIBATALKAN
                            )
                            ->whereNotNull('tagihan.diterbitkan_at')
                    );
            });
    }

    public function lihat(User $user, Tagihan $tagihan): bool
    {
        return $this->batasi(Tagihan::query(), $user)
            ->whereKey($tagihan->getKey())
            ->exists();
    }
}