<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\PengajarKelas;
use App\Models\Pertemuan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class AksesPresensi
{
    public function admin(User $user): bool
    {
        return $user->hasRole(Role::ADMIN_AKADEMIK);
    }

    public function dosen(User $user): bool
    {
        return $user->hasRole(Role::DOSEN)
            && Dosen::query()->where('user_id', $user->id)->where('status', Dosen::AKTIF)->exists();
    }

    public function masuk(User $user): bool
    {
        return $this->admin($user) || $this->dosen($user);
    }

    public function penugasan(User $user): Builder
    {
        return PengajarKelas::query()->where('aktif', true)
            ->whereHas('dosen', fn(Builder $q) => $q->where('user_id', $user->id)->where('status', Dosen::AKTIF));
    }

    public function lihat(User $user, Pertemuan $sesi): bool
    {
        return $this->admin($user) || ($this->dosen($user)
            && $this->penugasan($user)->where('kelas_kuliah_id', $sesi->kelas_kuliah_id)->exists());
    }

    public function batasi(Builder $query, User $user): Builder
    {
        if ($this->admin($user)) {
            return $query;
        }
        if (! $this->dosen($user)) {
            return $query->whereRaw('1 = 0');
        }
        return $query->whereIn('kelas_kuliah_id', $this->penugasan($user)->select('kelas_kuliah_id'));
    }

    public function konteksAktif(Pertemuan $sesi): bool
    {
        $sesi->loadMissing('kelasKuliah.rombel.periodeAkademik');
        return $sesi->kelasKuliah->status === KelasKuliah::AKTIF
            && $sesi->kelasKuliah->rombel->periodeAkademik->status === 'aktif';
    }

    public function catat(User $user, Pertemuan $sesi): bool
    {
        return $this->lihat($user, $sesi) && $this->konteksAktif($sesi)
            && $sesi->status === Pertemuan::BERLANGSUNG;
    }

    public function jalankan(User $user, Pertemuan $sesi): bool
    {
        // Hanya penanggung jawab sesi yang boleh mulai/selesai; tim lain boleh mengisi presensi.
        return $this->admin($user) || ($this->dosen($user) && $this->konteksAktif($sesi)
            && $this->penugasan($user)->whereKey($sesi->pengajar_kelas_id)
            ->where('kelas_kuliah_id', $sesi->kelas_kuliah_id)->exists());
    }
}
