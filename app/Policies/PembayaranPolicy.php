<?php

namespace App\Policies;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AksesPembayaran;

class PembayaranPolicy
{
    public function __construct(private AksesPembayaran $akses) {}
    public function viewAny(User $u): bool
    {
        return $this->akses->masuk($u);
    }
    public function view(User $u, Pembayaran $p): bool
    {
        return $this->akses->lihat($u, $p);
    }
    public function create(User $u, Tagihan $t): bool
    {
        return $this->akses->ajukan($u, $t);
    }
    public function cancel(User $u, Pembayaran $p): bool
    {
        return $this->akses->batal($u, $p);
    }
    public function download(User $u, Pembayaran $p): bool
    {
        return $this->akses->lihat($u, $p);
    }
    public function audit(User $u, Pembayaran $p): bool
    {
        return $this->akses->petugas($u);
    }
}
