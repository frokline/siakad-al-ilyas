<?php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\KelasKuliah;
use App\Models\User;
use App\Services\AksesKegiatan;

class KegiatanPolicy
{
    public function __construct(private readonly AksesKegiatan $akses) {}
    public function viewAny(User $u): bool
    {
        return $this->akses->masuk($u);
    }
    public function create(User $u, KelasKuliah $kelas): bool
    {
        return $this->akses->kelola($u, $kelas->id) && $this->akses->konteksTulis($kelas->id);
    }
    public function view(User $u, Kegiatan $k): bool
    {
        return $this->akses->lihat($u, $k);
    }
    public function bacaIsi(User $u, Kegiatan $k): bool
    {
        return $this->akses->bacaIsi($u, $k);
    }
    public function manage(User $u, Kegiatan $k): bool
    {
        return $this->akses->kelola($u, $k->kelas_kuliah_id);
    }
    public function update(User $u, Kegiatan $k): bool
    {
        return $k->status === Kegiatan::DRAF && $k->terbit_at === null && $this->manage($u, $k) && $this->akses->konteksTulis($k->kelas_kuliah_id);
    }
    private function aktif(User $u, Kegiatan $k): bool
    {
        return $this->manage($u, $k) && $this->akses->konteksTulis($k->kelas_kuliah_id)
            && KelasKuliah::query()->whereKey($k->kelas_kuliah_id)->where('status', 'aktif')->exists();
    }
    public function terbitkan(User $u, Kegiatan $k): bool
    {
        return $this->update($u, $k) && $this->aktif($u, $k);
    }
    public function tutup(User $u, Kegiatan $k): bool
    {
        return $k->status === Kegiatan::TERBIT && $this->manage($u, $k);
    }
    public function bukaKembali(User $u, Kegiatan $k): bool
    {
        return $k->status === Kegiatan::DITUTUP && $this->aktif($u, $k);
    }
    public function perpanjang(User $u, Kegiatan $k): bool
    {
        return in_array($k->status, [Kegiatan::TERBIT, Kegiatan::DITUTUP], true) && $this->aktif($u, $k);
    }
    public function arsipkan(User $u, Kegiatan $k): bool
    {
        return $k->status !== Kegiatan::ARSIP && $this->manage($u, $k);
    }
    public function pulihkan(User $u, Kegiatan $k): bool
    {
        return $k->status === Kegiatan::ARSIP && $this->manage($u, $k) && $this->akses->konteksTulis($k->kelas_kuliah_id);
    }
}
