<?php

namespace App\Services;

use App\Models\PermohonanSurat;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class AksesSurat
{
    public function petugas(User $u): bool
    {
        return app(AksesJenisSurat::class)->kelola($u);
    }
    public function mahasiswa(User $u): bool
    {
        return User::query()->whereKey($u->id)->where('status', 'aktif')->has('mahasiswa')
            ->whereHas('roles', fn(Builder $q) => $q->where('roles.kode', 'mahasiswa'))->exists();
    }
    public function masuk(User $u): bool
    {
        return $this->petugas($u) || $this->mahasiswa($u);
    }
    public function pemilik(User $u, PermohonanSurat $p): bool
    {
        return $this->mahasiswa($u) && $p->pemohon_id === (int) $u->id
            && DB::table('mahasiswa')->where('id', $p->mahasiswa_id)->where('user_id', $u->id)->exists();
    }
    public function batasi(Builder $q, User $u): Builder
    {
        if ($this->petugas($u)) {
            return $q;
        }
        if (! $this->mahasiswa($u)) {
            return $q->whereRaw('1 = 0');
        }
        return $q->where('pemohon_id', $u->id)->whereIn('mahasiswa_id', DB::table('mahasiswa')->select('id')->where('user_id', $u->id));
    }
    public function lihat(User $u, PermohonanSurat $p): bool
    {
        return $this->petugas($u) || $this->pemilik($u, $p);
    }
    public function tindakan(User $u, PermohonanSurat $p, string $tujuan): bool
    {
        if (! in_array($tujuan, PermohonanSurat::TRANSISI[$p->status] ?? [], true)) {
            return false;
        }
        if ($tujuan === 'dibatalkan' && $p->status === 'diajukan' && $this->pemilik($u, $p)) {
            return true;
        }
        return $this->petugas($u) && $p->pemohon_id !== (int) $u->id;
    }
}
