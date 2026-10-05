<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Role;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class AksesPembayaran
{
    public function petugas(User $user): bool
    {
        return app(AksesKeuangan::class)->kelola($user);
    }

    public function masuk(User $user): bool
    {
        return app(AksesTagihan::class)->masuk($user);
    }

    public function pemilik(User $user, Tagihan $tagihan): bool
    {
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
            ->whereHas(
                'mahasiswa',
                fn (Builder $mahasiswa): Builder => $mahasiswa
                    ->whereKey($tagihan->mahasiswa_id)
            )
            ->exists();
    }

    public function ajukan(User $user, Tagihan $tagihan): bool
    {
        return $tagihan->status === Tagihan::TERBIT
            && (
                $this->petugas($user)
                || $this->pemilik($user, $tagihan)
            );
    }

    public function batasi(Builder $query, User $user): Builder
    {
        if ($this->petugas($user)) {
            return $query;
        }

        if (! $this->masuk($user)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            'tagihan',
            fn (Builder $tagihan): Builder => $tagihan->whereIn(
                'mahasiswa_id',
                DB::table('mahasiswa')
                    ->select('id')
                    ->where(
                        'user_id',
                        $user->getAuthIdentifier()
                    )
            )
        );
    }

    public function lihat(User $user, Pembayaran $pembayaran): bool
    {
        return $this->batasi(Pembayaran::query(), $user)
            ->whereKey($pembayaran->getKey())
            ->exists();
    }

    public function batal(User $user, Pembayaran $pembayaran): bool
    {
        return $pembayaran->status === Pembayaran::MENUNGGU
            && $this->lihat($user, $pembayaran);
    }
}