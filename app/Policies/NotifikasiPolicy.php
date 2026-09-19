<?php

namespace App\Policies;

use App\Models\{Notifikasi, User};
use App\Services\AksesNotifikasi;

class NotifikasiPolicy
{
    public function __construct(private AksesNotifikasi $akses) {}
    public function viewAny(User $u): bool
    {
        return $this->akses->masuk($u);
    }
    public function update(User $u, Notifikasi $n): bool
    {
        return $this->akses->lihat($u, $n);
    }
    // Tidak ada bypass admin: kotak masuk tetap milik penerima.
}
