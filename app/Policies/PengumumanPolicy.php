<?php

namespace App\Policies;

use App\Models\Pengumuman;
use App\Models\User;
use App\Services\AksesPengumuman;

class PengumumanPolicy
{
    public function __construct(private AksesPengumuman $akses) {}
    public function viewAny(User $u): bool
    {
        return $this->akses->masuk($u);
    }
    public function create(User $u): bool
    {
        return $this->akses->menulis($u);
    }
    public function view(User $u, Pengumuman $p): bool
    {
        return $this->akses->membaca($u, $p);
    }
    public function audit(User $u, Pengumuman $p): bool
    {
        return $this->akses->mengelola($u, $p);
    }
    public function update(User $u, Pengumuman $p): bool
    {
        return $p->status === 'draf' && $this->audit($u, $p);
    }
    public function terbit(User $u, Pengumuman $p): bool
    {
        return $this->update($u, $p);
    }
    public function arsip(User $u, Pengumuman $p): bool
    {
        return $p->status !== 'arsip' && $this->audit($u, $p);
    }
}
