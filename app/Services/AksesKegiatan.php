<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AksesKegiatan
{
    public function __construct(private readonly AksesMateri $akademik) {}
    public function masuk(User $u): bool
    {
        return $this->akademik->masuk($u);
    }
    public function pengelola(User $u): bool
    {
        return $this->akademik->pengelola($u);
    }
    public function kelasKelola(User $u): Builder
    {
        return $this->akademik->kelasKelola($u);
    }
    public function kelola(User $u, int $id): bool
    {
        return $this->akademik->kelola($u, $id);
    }
    public function konteksTulis(int $id): bool
    {
        return $this->akademik->konteksTulis($id);
    }
    public function batasi(Builder $q, User $u): Builder
    {
        $kelola = $this->akademik->kelasKelola($u)->select('kelas_kuliah.id');
        $peserta = $this->akademik->kelasPeserta($u)->select('kelas_kuliah.id');
        return $q->where(function (Builder $b) use ($kelola, $peserta): void {
            $b->whereIn('kegiatan.kelas_kuliah_id', $kelola)->orWhere(function (Builder $s) use ($peserta): void {
                $s->whereIn('kegiatan.kelas_kuliah_id', $peserta)->whereIn('kegiatan.status', [Kegiatan::TERBIT, Kegiatan::DITUTUP])
                    ->whereNotNull('kegiatan.terbit_at')->where('kegiatan.terbit_at', '<=', now('UTC'))
                    ->where(fn(Builder $p) => $p->whereNull('kegiatan.pertemuan_id')
                        ->orWhereHas('pertemuan', fn(Builder $r) => $r->where('status', '!=', 'batal')));
            });
        });
    }
    public function lihat(User $u, Kegiatan $k): bool
    {
        return $this->batasi(Kegiatan::query(), $u)->whereKey($k->id)->exists();
    }
    public function bacaIsi(User $u, Kegiatan $k): bool
    {
        if ($this->kelola($u, $k->kelas_kuliah_id)) {
            return true;
        }
        return $this->batasi(Kegiatan::query(), $u)->whereKey($k->id)->where('buka_at', '<=', now('UTC'))->exists();
    }
}
