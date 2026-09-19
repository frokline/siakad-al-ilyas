<?php

namespace App\Policies;

use App\Models\JenisBiaya;
use App\Models\User;
use App\Services\AksesKeuangan;

class JenisBiayaPolicy
{
    public function __construct(private readonly AksesKeuangan $akses) {}
    public function viewAny(User $u): bool
    {
        return $this->akses->lihatMaster($u);
    }
    public function view(User $u, JenisBiaya $j): bool
    {
        return $this->akses->lihatMaster($u);
    }
    public function create(User $u): bool
    {
        return $this->akses->kelola($u);
    }
    public function update(User $u, JenisBiaya $j): bool
    {
        return $this->akses->kelola($u) && $j->aktif;
    }
    public function nonaktifkan(User $u, JenisBiaya $j): bool
    {
        return $this->akses->kelola($u) && $j->aktif;
    }
    public function aktifkan(User $u, JenisBiaya $j): bool
    {
        return $this->akses->kelola($u) && ! $j->aktif;
    }
}
