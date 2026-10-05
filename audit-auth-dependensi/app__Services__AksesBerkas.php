<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AksesBerkas
{
    public function masuk(User $user): bool
    {
        // Kode role mengikuti tabel roles proyek; akun dan profil selalu diperiksa di server.
        return User::query()->whereKey($user->id)->where('status', 'aktif')
            ->where(function (Builder $q): void {
                $q->whereHas('roles', fn(Builder $r) => $r->whereIn('roles.kode', ['admin_akademik', 'admin_keuangan']))
                    ->orWhere(function (Builder $d): void {
                        $d->whereHas('roles', fn(Builder $r) => $r->where('roles.kode', 'dosen'))
                            ->whereHas('dosen', fn(Builder $p) => $p->where('status', 'aktif'));
                    })->orWhere(function (Builder $m): void {
                        $m->whereHas('roles', fn(Builder $r) => $r->where('roles.kode', 'mahasiswa'))->has('mahasiswa');
                    });
            })->exists();
    }
}
