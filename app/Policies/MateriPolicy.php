<?php

namespace App\Policies;

use App\Models\KelasKuliah;
use App\Models\Materi;
use App\Models\User;
use App\Services\AksesMateri;

class MateriPolicy
{
    public function __construct(private readonly AksesMateri $akses) {}
    public function viewAny(User $user): bool
    {
        return $this->akses->masuk($user);
    }
    public function create(User $user, KelasKuliah $kelas): bool
    {
        return $this->akses->kelola($user, $kelas->id) && $this->akses->konteksTulis($kelas->id);
    }
    public function view(User $user, Materi $m): bool
    {
        return $this->akses->lihat($user, $m);
    }
    public function manage(User $user, Materi $m): bool
    {
        return $this->akses->kelola($user, $m->kelas_kuliah_id);
    }
    public function update(User $user, Materi $m): bool
    {
        return $m->status === Materi::DRAF && $this->manage($user, $m) && $this->akses->konteksTulis($m->kelas_kuliah_id);
    }
    public function terbitkan(User $user, Materi $m): bool
    {
        return $this->update($user, $m)
            && KelasKuliah::query()->whereKey($m->kelas_kuliah_id)->where('status', 'aktif')->exists();
    }
    public function tarik(User $user, Materi $m): bool
    {
        return $m->status === Materi::TERBIT && $this->manage($user, $m) && $this->akses->konteksTulis($m->kelas_kuliah_id);
    }
    public function arsipkan(User $user, Materi $m): bool
    {
        // Penarikan akses melalui arsip tetap boleh setelah periode selesai.
        return $m->status !== Materi::ARSIP && $this->manage($user, $m);
    }
    public function pulihkan(User $user, Materi $m): bool
    {
        return $m->status === Materi::ARSIP && $this->manage($user, $m) && $this->akses->konteksTulis($m->kelas_kuliah_id);
    }
}
