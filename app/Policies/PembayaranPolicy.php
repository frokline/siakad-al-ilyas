<?php

namespace App\Policies;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AksesPembayaran;

class PembayaranPolicy
{
    public function __construct(
        private AksesPembayaran $akses
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->akses->masuk($user);
    }

    public function view(User $user, Pembayaran $pembayaran): bool
    {
        return $this->akses->lihat($user, $pembayaran);
    }

    public function create(User $user, Tagihan $tagihan): bool
    {
        return $this->akses->ajukan($user, $tagihan);
    }

    public function cancel(User $user, Pembayaran $pembayaran): bool
    {
        return $this->akses->batal($user, $pembayaran);
    }

    public function download(User $user, Pembayaran $pembayaran): bool
    {
        return $this->akses->lihat($user, $pembayaran);
    }

    public function audit(User $user, Pembayaran $pembayaran): bool
    {
        return $this->akses->petugas($user);
    }

    /**
     * Hanya Admin Keuangan aktif yang dapat memverifikasi
     * pembayaran yang masih menunggu.
     */
    public function verify(
        User $user,
        Pembayaran $pembayaran
    ): bool {
        return $pembayaran->status === Pembayaran::MENUNGGU
            && $this->akses->petugas($user);
    }
}
