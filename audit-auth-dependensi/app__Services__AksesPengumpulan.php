<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\KelasKuliah;
use App\Models\Pengumpulan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class AksesPengumpulan
{
    public function __construct(private readonly AksesMateri $akademik, private readonly AksesKegiatan $kegiatan) {}
    public function masuk(User $u): bool
    {
        return $this->akademik->masuk($u);
    }
    public function mahasiswa(User $u): bool
    {
        return $this->akademik->mahasiswa($u);
    }
    public function kelola(User $u, Kegiatan $k): bool
    {
        return $this->akademik->kelola($u, $k->kelas_kuliah_id);
    }
    public function peserta(int $kelasId): QueryBuilder
    {
        return DB::table('detail_krs as d')->join('krs as k', 'k.id', '=', 'd.krs_id')
            ->join('registrasi_semester as r', 'r.id', '=', 'k.registrasi_semester_id')
            ->join('riwayat_studi as h', 'h.id', '=', 'r.riwayat_studi_id')
            ->join('mahasiswa as m', 'm.id', '=', 'h.mahasiswa_id')->join('users as u', 'u.id', '=', 'm.user_id')
            ->join('kelas_kuliah as c', 'c.id', '=', 'd.kelas_kuliah_id')->join('rombel as b', 'b.id', '=', 'c.rombel_id')
            ->where('d.kelas_kuliah_id', $kelasId)->where('d.status', 'aktif')->where('k.status', 'disahkan')
            ->where('r.status', 'aktif')->where('h.status', 'aktif')->where('u.status', 'aktif')
            ->whereColumn('r.rombel_id', 'b.id')->whereColumn('r.periode_akademik_id', 'b.periode_akademik_id')
            ->whereExists(function (QueryBuilder $q): void {
                $q->selectRaw('1')->from('user_roles as ur')->join('roles as ro', 'ro.id', '=', 'ur.role_id')
                    ->whereColumn('ur.user_id', 'u.id')->where('ro.kode', 'mahasiswa');
            });
    }
    public function detail(User $u, Kegiatan $k): ?int
    {
        if (! $this->mahasiswa($u)) {
            return null;
        }
        $ids = $this->peserta($k->kelas_kuliah_id)->where('u.id', $u->id)->orderBy('d.id')->limit(2)->pluck('d.id');
        return $ids->count() === 1 ? (int) $ids->first() : null;
    }
    public function ruangSaya(User $u, Kegiatan $k): bool
    {
        if (! $this->mahasiswa($u)) {
            return false;
        }
        return Pengumpulan::query()->where('pemilik_id', $u->id)->where('kegiatan_id', $k->id)->exists()
            || ($this->detail($u, $k) !== null && $this->kegiatan->lihat($u, $k));
    }
    public function bolehTulis(User $u, Kegiatan $k): bool
    {
        $k = Kegiatan::query()->find($k->id);
        if (! $k || ! $k->jendelaTerbuka() || $this->detail($u, $k) === null || ! $this->kegiatan->bacaIsi($u, $k)) {
            return false;
        }
        if ($k->pertemuan_id !== null && ! $k->pertemuan()->where('status', '!=', 'batal')->exists()) {
            return false;
        }
        return KelasKuliah::query()->whereKey($k->kelas_kuliah_id)->where('status', 'aktif')
            ->whereHas('rombel.periodeAkademik', fn(Builder $q) => $q->where('status', 'aktif'))->exists();
    }
    public function batasi(Builder $q, User $u): Builder
    {
        $pemilik = $this->mahasiswa($u);
        $kelas = $this->akademik->kelasKelola($u)->select('kelas_kuliah.id');
        return $q->where(function (Builder $s) use ($u, $pemilik, $kelas): void {
            $s->where(function (Builder $own) use ($u, $pemilik): void {
                $own->where('pengumpulan.pemilik_id', $u->id);
                if (! $pemilik) {
                    $own->whereRaw('1 = 0');
                }
            })->orWhere(function (Builder $staff) use ($kelas): void {
                $staff->where('pengumpulan.status', Pengumpulan::DIKIRIM)
                    ->whereHas('kegiatan', fn(Builder $k) => $k->whereIn('kelas_kuliah_id', $kelas));
            });
        });
    }
    public function lihat(User $u, Pengumpulan $p): bool
    {
        return $this->batasi(Pengumpulan::query(), $u)->whereKey($p->id)->exists();
    }
    public function pemilik(User $u, Pengumpulan $p): bool
    {
        return $this->mahasiswa($u) && Pengumpulan::query()->whereKey($p->id)->where('pemilik_id', $u->id)->exists();
    }
}
