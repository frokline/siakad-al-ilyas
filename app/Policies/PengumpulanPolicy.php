<?php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\Pengumpulan;
use App\Models\User;
use App\Services\AksesPengumpulan;

class PengumpulanPolicy
{
    public function __construct(private readonly AksesPengumpulan $akses) {}
    public function viewAny(User $u): bool
    {
        return $this->akses->masuk($u);
    }
    public function view(User $u, Pengumpulan $p): bool
    {
        return $this->akses->lihat($u, $p);
    }
    public function ruangSaya(User $u, Kegiatan $k): bool
    {
        return $this->akses->ruangSaya($u, $k);
    }
    public function rekap(User $u, Kegiatan $k): bool
    {
        return $this->akses->kelola($u, $k);
    }
    public function create(User $u, Kegiatan $k): bool
    {
        return $this->akses->bolehTulis($u, $k);
    }
    public function update(User $u, Pengumpulan $p): bool
    {
        return $this->akses->pemilik($u, $p) && $p->status === Pengumpulan::DRAF && $this->akses->bolehTulis($u, $p->kegiatan);
    }
    // Mengizinkan pengulangan POST untuk menerima bukti kiriman yang sama. Aksi mengecek waktu secara atomik.
    public function kirim(User $u, Pengumpulan $p): bool
    {
        return $this->akses->pemilik($u, $p);
    }
}
