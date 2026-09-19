<?php

namespace App\Services;

use App\Models\KalenderAkademik;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class AksesKalender
{
    public function masuk(User $u): bool
    {
        return app(AksesBerkas::class)->masuk($u);
    }
    public function kelola(User $u): bool
    {
        return app(AksesJenisSurat::class)->kelola($u);
    }
    public function batasi(Builder $q, User $u): Builder
    {
        if ($this->kelola($u)) {
            return $q;
        }
        if (! $this->masuk($u)) {
            return $q->whereRaw('1 = 0');
        }
        // Agenda terbit adalah informasi akademik bersama; prodi merupakan filter, bukan ACL.
        // Pembatalan agenda yang pernah terbit tetap terlihat agar pengguna mengetahui perubahan.
        return $q->whereIn('status', ['terbit', 'batal'])->whereNotNull('diterbitkan_at')->where('diterbitkan_at', '<=', now('UTC'));
    }
    public function lihat(User $u, KalenderAkademik $k): bool
    {
        return $this->batasi(KalenderAkademik::query(), $u)->whereKey($k->id)->exists();
    }
}
