<?php

namespace App\Services;

use App\Models\{Notifikasi, User};
use Illuminate\Database\Eloquent\Builder;

final class AksesNotifikasi
{
    public function __construct(private SumberNotifikasi $sumber) {}
    public function masuk(User $u): bool
    {
        return $this->sumber->masuk($u);
    }
    public function batasi(Builder $q, User $u): Builder
    {
        $q->where('notifikasi.penerima_id', $u->id);
        if (! $this->masuk($u)) {
            return $q->whereRaw('1 = 0');
        }
        return $q->where(function (Builder $b) use ($u): void {
            $b->whereRaw('1 = 0');
            foreach ($this->sumber->aktif() as $jenis) {
                $def = $this->sumber->definisi($jenis);
                $b->orWhere(fn(Builder $s) => $s->where('notifikasi.jenis', $jenis)->where('notifikasi.sumber_tabel', $def['tabel'])
                    ->whereIn('notifikasi.sumber_id', $this->sumber->query($jenis, $u)->select($def['tabel'] . '.id')));
            }
        });
    }
    public function lihat(User $u, Notifikasi $n): bool
    {
        return $this->batasi(Notifikasi::query(), $u)->whereKey($n->id)->exists();
    }
}
