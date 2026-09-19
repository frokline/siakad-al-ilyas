<?php

namespace App\Policies;

use App\Models\PermohonanSurat;
use App\Models\User;
use App\Services\AksesSurat;

class PermohonanSuratPolicy
{
    public function __construct(private readonly AksesSurat $akses) {}
    public function viewAny(User $u): bool
    {
        return $this->akses->masuk($u);
    }
    public function create(User $u): bool
    {
        return $this->akses->mahasiswa($u);
    }
    public function view(User $u, PermohonanSurat $p): bool
    {
        return $this->akses->lihat($u, $p);
    }
    public function tindakan(User $u, PermohonanSurat $p, string $tujuan): bool
    {
        return $this->akses->tindakan($u, $p, $tujuan);
    }
    public function download(User $u, PermohonanSurat $p, string $bagian): bool
    {
        return $this->view($u, $p) && ($bagian === 'lampiran' ? $p->lampiran_berkas_id !== null : ($bagian === 'hasil' && $p->status === 'terbit'));
    }
}
