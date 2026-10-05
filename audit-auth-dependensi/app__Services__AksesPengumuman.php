<?php

namespace App\Services;

use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class AksesPengumuman
{
    public const ROLES = ['admin_akademik', 'admin_keuangan', 'dosen', 'mahasiswa'];
    public function __construct(private AksesMateri $materi, private AksesBerkas $berkas) {}
    public function masuk(User $u): bool
    {
        return $this->berkas->masuk($u);
    }
    public function admin(User $u): bool
    {
        return $this->materi->admin($u);
    }
    public function menulis(User $u): bool
    {
        return $this->admin($u) || $this->materi->dosen($u);
    }
    public function kelola(Builder $q, User $u): Builder
    {
        if ($this->admin($u)) {
            return $q;
        }
        if (! $this->materi->dosen($u)) {
            return $q->whereRaw('1 = 0');
        }
        return $q->where('pembuat_id', $u->id)->whereHas('sasaran')
            ->whereDoesntHave('sasaran', function (Builder $s) use ($u): void {
                $s->where(function (Builder $b) use ($u): void {
                    $b->where('lingkup', '!=', 'kelas')->orWhereNull('kelas_kuliah_id')
                        ->orWhereNotIn('kelas_kuliah_id', $this->materi->kelasKelola($u)->select('kelas_kuliah.id'))
                        ->orWhereHas('role', fn($r) => $r->whereNotIn('kode', ['dosen', 'mahasiswa']));
                });
            });
    }
    public function mengelola(User $u, Pengumuman $p): bool
    {
        return $this->kelola(Pengumuman::query(), $u)->whereKey($p->id)->exists();
    }
    public function membaca(User $u, Pengumuman $p): bool
    {
        return $this->mengelola($u, $p) || $this->bacaan(Pengumuman::query(), $u)->whereKey($p->id)->exists();
    }
    public function bacaan(Builder $q, User $u): Builder
    {
        if (! $this->masuk($u)) {
            return $q->whereRaw('1 = 0');
        }
        $roles = User::query()->findOrFail($u->id)->roles()->whereIn('kode', self::ROLES)->pluck('roles.kode')->all();
        $roles = array_values(array_filter($roles, fn($r) => match ($r) {
            'dosen' => $this->materi->dosen($u),
            'mahasiswa' => $this->materi->mahasiswa($u),
            default => true,
        }));
        return $q->where('status', 'terbit')->where('terbit_at', '<=', now('UTC'))
            ->where(fn($b) => $b->whereNull('berakhir_at')->orWhere('berakhir_at', '>', now('UTC')))
            ->whereHas('sasaran', function (Builder $s) use ($u, $roles): void {
                $s->where(function (Builder $semua) use ($u, $roles): void {
                    $semua->whereRaw('1 = 0');
                    foreach ($roles as $role) {
                        // Role dan keanggotaan harus berasal dari persona yang sama.
                        $semua->orWhere(function (Builder $persona) use ($u, $role): void {
                            $persona->where(fn($b) => $b->whereNull('role_id')->orWhereHas('role', fn($r) => $r->where('kode', $role)))
                                ->where(function (Builder $lingkup) use ($u, $role): void {
                                    $lingkup->where('lingkup', 'kampus');
                                    if (! in_array($role, ['dosen', 'mahasiswa'], true)) {
                                        return;
                                    }
                                    $kelas = $role === 'dosen'
                                        ? \App\Models\KelasKuliah::query()->whereIn('kelas_kuliah.status', ['aktif', 'selesai'])
                                        ->whereIn('kelas_kuliah.id', DB::table('pengajar_kelas as aj')->join('dosen as ds', 'ds.id', '=', 'aj.dosen_id')
                                            ->where('aj.aktif', true)->where('ds.user_id', $u->id)->where('ds.status', 'aktif')->select('aj.kelas_kuliah_id'))
                                        : $this->materi->kelasPeserta($u);
                                    $lingkup->orWhere(fn($b) => $b->where('lingkup', 'kelas')->whereIn('kelas_kuliah_id', (clone $kelas)->select('kelas_kuliah.id')));
                                    $prodi = $role === 'mahasiswa'
                                        ? DB::table('riwayat_studi as h')->join('mahasiswa as m', 'm.id', '=', 'h.mahasiswa_id')
                                        ->join('kurikulum as k', 'k.id', '=', 'h.kurikulum_id')->where('m.user_id', $u->id)
                                        ->where('h.status', 'aktif')->select('k.program_studi_id')
                                        : DB::table('kelas_kuliah as c')->join('rombel as b', 'b.id', '=', 'c.rombel_id')
                                        ->join('paket_semester as p', 'p.id', '=', 'b.paket_semester_id')
                                        ->join('kurikulum as k', 'k.id', '=', 'p.kurikulum_id')
                                        ->whereIn('c.id', (clone $kelas)->select('kelas_kuliah.id'))->select('k.program_studi_id');
                                    $lingkup->orWhere(fn($b) => $b->where('lingkup', 'prodi')->whereIn('program_studi_id', $prodi));
                                });
                        });
                    }
                });
            });
    }
}
