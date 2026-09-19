<?php

namespace App\Policies;

use App\Models\Berkas;
use App\Models\User;
use App\Services\AksesBerkas;

class BerkasPolicy
{
    public function viewAny(User $user): bool
    {
        return app(AksesBerkas::class)->masuk($user);
    }
    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }
    public function view(User $user, Berkas $file): bool
    {
        return $this->viewAny($user) && $file->diunggah_oleh === $user->id;
    }
    public function update(User $user, Berkas $file): bool
    {
        return $this->view($user, $file) && $file->status === Berkas::TERSEDIA;
    }
    public function download(User $user, Berkas $file): bool
    {
        return $this->update($user, $file);
    }
    public function nonaktifkan(User $user, Berkas $file): bool
    {
        return $this->update($user, $file);
    }
    public function pulihkan(User $user, Berkas $file): bool
    {
        return $this->view($user, $file) && $file->status === Berkas::DIHAPUS;
    }
}
