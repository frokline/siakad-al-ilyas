<?php

namespace App\Services;

use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class AksesTagihan
{
    public function petugas(User $u): bool
    {
        return app(AksesKeuangan::class)->kelola($u);
    }
    public function masuk(User $u): bool
    {
        return $this->petugas($u) || (User::query()->whereKey($u->id)->where('status', 'aktif')->exists()
            && DB::table('mahasiswa')->where('user_id', $u->id)->exists());
    }
    public function batasi(Builder $q, User $u): Builder
    {
        if ($this->petugas($u)) {
            return $q;
        }
        if (! $this->masuk($u)) {
            return $q->whereRaw('1 = 0');
        }
        return $q->whereIn('tagihan.mahasiswa_id', DB::table('mahasiswa')->select('id')->where('user_id', $u->id))
            ->where(function (Builder $b): void {
                $b->where('tagihan.status', Tagihan::TERBIT)
                    ->orWhere(fn(Builder $c) => $c->where('tagihan.status', Tagihan::DIBATALKAN)->whereNotNull('tagihan.diterbitkan_at'));
            });
    }
    public function lihat(User $u, Tagihan $t): bool
    {
        return $this->batasi(Tagihan::query(), $u)->whereKey($t->id)->exists();
    }
}
