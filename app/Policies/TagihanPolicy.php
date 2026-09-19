<?php

namespace App\Policies;

use App\Models\Tagihan;
use App\Models\User;
use App\Services\AksesTagihan;

class TagihanPolicy
{
    public function __construct(private AksesTagihan $akses) {}
    public function viewAny(User $u): bool
    {
        return $this->akses->masuk($u);
    }
    public function view(User $u, Tagihan $t): bool
    {
        return $this->akses->lihat($u, $t);
    }
    public function create(User $u): bool
    {
        return $this->akses->petugas($u);
    }
    public function update(User $u, Tagihan $t): bool
    {
        return $this->akses->petugas($u) && $t->status === Tagihan::DRAF;
    }
    public function terbitkan(User $u, Tagihan $t): bool
    {
        return $this->update($u, $t);
    }
    public function batalkan(User $u, Tagihan $t): bool
    {
        return $this->akses->petugas($u) && $t->status !== Tagihan::DIBATALKAN;
    }
    public function bukaDraf(User $u, Tagihan $t): bool
    {
        return $this->akses->petugas($u) && $t->status === Tagihan::DIBATALKAN;
    }
    public function audit(User $u, Tagihan $t): bool
    {
        return $this->akses->petugas($u);
    }
}
