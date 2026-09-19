<?php

namespace App\Policies;

use App\Models\KalenderAkademik;
use App\Models\User;
use App\Services\AksesKalender;

class KalenderAkademikPolicy
{
    public function __construct(private readonly AksesKalender $akses) {}
    public function viewAny(User $u): bool
    {
        return $this->akses->masuk($u);
    }
    public function view(User $u, KalenderAkademik $k): bool
    {
        return $this->akses->lihat($u, $k);
    }
    public function create(User $u): bool
    {
        return $this->akses->kelola($u);
    }
    public function update(User $u, KalenderAkademik $k): bool
    {
        return $this->akses->kelola($u) && $k->status !== 'batal';
    }
    public function terbitkan(User $u, KalenderAkademik $k): bool
    {
        return $this->akses->kelola($u) && $k->status === 'draf';
    }
    public function batalkan(User $u, KalenderAkademik $k): bool
    {
        return $this->akses->kelola($u) && $k->status !== 'batal';
    }
    public function audit(User $u, KalenderAkademik $k): bool
    {
        return $this->akses->kelola($u);
    }
}
