<?php

namespace App\Policies;

use App\Models\JenisSurat;
use App\Models\User;
use App\Services\AksesJenisSurat;

class JenisSuratPolicy
{
    public function __construct(private readonly AksesJenisSurat $akses) {}
    public function viewAny(User $u): bool
    {
        return $this->akses->lihatMaster($u);
    }
    public function view(User $u, JenisSurat $j): bool
    {
        return $this->akses->lihatMaster($u);
    }
    public function create(User $u): bool
    {
        return $this->akses->kelola($u);
    }
    public function update(User $u, JenisSurat $j): bool
    {
        return $this->akses->kelola($u) && $j->aktif;
    }
    public function nonaktifkan(User $u, JenisSurat $j): bool
    {
        return $this->akses->kelola($u) && $j->aktif;
    }
    public function aktifkan(User $u, JenisSurat $j): bool
    {
        return $this->akses->kelola($u) && ! $j->aktif;
    }
}
