<?php

namespace App\Services;

use App\Models\KelasKuliah;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AksesMateri
{
    public function role(User $user, string $kode): bool
    {
        return User::query()->whereKey($user->id)->where('status', 'aktif')
            ->whereHas('roles', fn(Builder $q) => $q->where('roles.kode', $kode))->exists();
    }
    public function admin(User $user): bool
    {
        return $this->role($user, 'admin_akademik');
    }
    public function dosen(User $user): bool
    {
        return $this->role($user, 'dosen') && DB::table('dosen')->where('user_id', $user->id)->where('status', 'aktif')->exists();
    }
    public function mahasiswa(User $user): bool
    {
        return $this->role($user, 'mahasiswa') && DB::table('mahasiswa')->where('user_id', $user->id)->exists();
    }
    public function masuk(User $user): bool
    {
        return $this->admin($user) || $this->dosen($user) || $this->mahasiswa($user);
    }
    public function pengelola(User $user): bool
    {
        return $this->admin($user) || $this->dosen($user);
    }

    public function kelasKelola(User $user): Builder
    {
        $q = KelasKuliah::query();
        if ($this->admin($user)) {
            return $q;
        }
        if (! $this->dosen($user)) {
            return $q->whereRaw('1 = 0');
        }
        return $q->whereIn('kelas_kuliah.id', DB::table('pengajar_kelas as p')
            ->join('dosen as d', 'd.id', '=', 'p.dosen_id')->where('p.aktif', true)
            ->where('d.user_id', $user->id)->where('d.status', 'aktif')->select('p.kelas_kuliah_id'));
    }

    public function kelasPeserta(User $user): Builder
    {
        $q = KelasKuliah::query()->whereIn('kelas_kuliah.status', ['aktif', 'selesai']);
        if (! $this->mahasiswa($user)) {
            return $q->whereRaw('1 = 0');
        }
        return $q->whereIn('kelas_kuliah.id', DB::table('detail_krs as d')
            ->join('krs as k', 'k.id', '=', 'd.krs_id')
            ->join('registrasi_semester as r', 'r.id', '=', 'k.registrasi_semester_id')
            ->join('riwayat_studi as h', 'h.id', '=', 'r.riwayat_studi_id')
            ->join('mahasiswa as m', 'm.id', '=', 'h.mahasiswa_id')
            ->join('kelas_kuliah as c', 'c.id', '=', 'd.kelas_kuliah_id')
            ->join('rombel as b', 'b.id', '=', 'c.rombel_id')
            ->where('m.user_id', $user->id)->where('d.status', 'aktif')->where('k.status', 'disahkan')
            ->where('r.status', 'aktif')->where('h.status', 'aktif')
            ->whereColumn('r.rombel_id', 'b.id')->whereColumn('r.periode_akademik_id', 'b.periode_akademik_id')
            ->select('d.kelas_kuliah_id'));
    }

    public function kelola(User $user, int $kelasId): bool
    {
        return $this->kelasKelola($user)->whereKey($kelasId)->exists();
    }
    public function konteksTulis(int $kelasId): bool
    {
        return KelasKuliah::query()->whereKey($kelasId)->whereIn('status', ['persiapan', 'aktif'])
            ->whereHas('rombel.periodeAkademik', fn(Builder $q) => $q->where('status', 'aktif'))->exists();
    }
    public function batasi(Builder $q, User $user): Builder
    {
        $kelola = $this->kelasKelola($user)->select('kelas_kuliah.id');
        $peserta = $this->kelasPeserta($user)->select('kelas_kuliah.id');
        return $q->where(function (Builder $b) use ($kelola, $peserta): void {
            $b->whereIn('materi.kelas_kuliah_id', $kelola)->orWhere(function (Builder $s) use ($peserta): void {
                $s->whereIn('materi.kelas_kuliah_id', $peserta)->where('materi.status', Materi::TERBIT)
                    ->whereNotNull('materi.terbit_at')->where('materi.terbit_at', '<=', now('UTC'))
                    ->where(function (Builder $p): void {
                        $p->whereNull('materi.pertemuan_id')->orWhereHas('pertemuan', fn(Builder $r) => $r->where('status', '!=', 'batal'));
                    });
            });
        });
    }
    public function lihat(User $user, Materi $materi): bool
    {
        return $this->batasi(Materi::query(), $user)->whereKey($materi->id)->exists();
    }
}
