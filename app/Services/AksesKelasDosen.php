<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class AksesKelasDosen
{
    /**
     * Memastikan akun masih aktif, memiliki role dosen,
     * dan mempunyai profil dosen aktif.
     */
    public function masuk(User $user): bool
    {
        return DB::table('users')
            ->join(
                'user_roles',
                'user_roles.user_id',
                '=',
                'users.id'
            )
            ->join(
                'roles',
                'roles.id',
                '=',
                'user_roles.role_id'
            )
            ->join(
                'dosen',
                'dosen.user_id',
                '=',
                'users.id'
            )
            ->where('users.id', $user->getKey())
            ->where('users.status', User::STATUS_AKTIF)
            ->where('roles.kode', Role::DOSEN)
            ->where('dosen.status', Dosen::AKTIF)
            ->exists();
    }

    /**
     * Membatasi daftar kelas hanya pada kelas yang mempunyai
     * penugasan aktif untuk dosen yang sedang login.
     *
     * Kelas selesai tetap dapat dilihat sebagai riwayat selama
     * penugasan dosennya masih tercatat aktif.
     */
    public function batasi(
        Builder $query,
        User $user
    ): Builder {
        if (! $this->masuk($user)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            'pengajarKelas',
            static function (Builder $penugasan) use ($user): void {
                $penugasan
                    ->where('pengajar_kelas.aktif', true)
                    ->whereHas(
                        'dosen',
                        static function (Builder $dosen) use ($user): void {
                            $dosen
                                ->where(
                                    'dosen.user_id',
                                    $user->getKey()
                                )
                                ->where(
                                    'dosen.status',
                                    Dosen::AKTIF
                                );
                        }
                    );
            }
        );
    }

    /**
     * Membatasi kelas yang masih dapat digunakan untuk kegiatan
     * mengajar aktif.
     */
    public function batasiKelasAktif(
        Builder $query,
        User $user
    ): Builder {
        return $this->batasi($query, $user)
            ->where(
                'kelas_kuliah.status',
                KelasKuliah::AKTIF
            )
            ->whereHas(
                'rombel.periodeAkademik',
                static function (Builder $periode): void {
                    $periode->where(
                        'periode_akademik.status',
                        'aktif'
                    );
                }
            );
    }

    /**
     * Memastikan dosen boleh melihat suatu kelas.
     */
    public function lihat(
        User $user,
        KelasKuliah $kelas
    ): bool {
        return $this->batasi(
            KelasKuliah::query(),
            $user
        )
            ->whereKey($kelas->getKey())
            ->exists();
    }

    /**
     * Memastikan dosen masih boleh melakukan aktivitas mengajar
     * pada kelas tersebut.
     */
    public function kelola(
        User $user,
        KelasKuliah $kelas
    ): bool {
        return $this->batasiKelasAktif(
            KelasKuliah::query(),
            $user
        )
            ->whereKey($kelas->getKey())
            ->exists();
    }

    /**
     * Mengambil detail kelas milik dosen atau menghasilkan 404.
     */
    public function temukan(
        User $user,
        int|string $id
    ): KelasKuliah {
        $kelas = $this->batasi(
            KelasKuliah::query(),
            $user
        )
            ->with([
                'rombel.periodeAkademik',

                'pengajarKelas' => static function (
                    $penugasan
                ): void {
                    $penugasan
                        ->where('aktif', true)
                        ->orderBy('peran')
                        ->orderBy('id');
                },

                'pengajarKelas.dosen.user',

                'jadwalKuliah' => static function (
                    $jadwal
                ): void {
                    $jadwal
                        ->orderByDesc('aktif')
                        ->orderBy('hari')
                        ->orderBy('jam_mulai');
                },

                'pertemuan' => static function (
                    $pertemuan
                ): void {
                    $pertemuan
                        ->orderBy('nomor')
                        ->orderBy('id');
                },
            ])
            ->find($id);

        if (! $kelas) {
            throw (new ModelNotFoundException())
                ->setModel(KelasKuliah::class, [$id]);
        }

        return $kelas;
    }
}
