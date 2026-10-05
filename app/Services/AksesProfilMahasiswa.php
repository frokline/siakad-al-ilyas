<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class AksesProfilMahasiswa
{
    public function __construct(
        private readonly AksesKrsMahasiswa $aksesKrsMahasiswa
    ) {
    }

    /**
     * Memastikan akun aktif mempunyai role dan profil mahasiswa.
     */
    public function masuk(User $user): bool
    {
        return $this->aksesKrsMahasiswa->masuk($user);
    }

    /**
     * Membatasi query mahasiswa hanya kepada profil akun yang login.
     */
    public function batasi(Builder $query, User $user): Builder
    {
        if (! $this->masuk($user)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('mahasiswa.user_id', $user->getKey());
    }

    /**
     * Mengambil profil mahasiswa milik akun yang sedang login.
     */
    public function profil(User $user): Mahasiswa
    {
        $profil = $this->batasi(Mahasiswa::query(), $user)
            ->with([
                'user',
                'riwayatStudi' => static function ($query): void {
                    $query->orderByDesc('angkatan')
                        ->orderByDesc('id');
                },
                'riwayatStudi.kurikulum.programStudi',
                'riwayatStudi.dosenPa.user',
                'riwayatStudi.periodeMulai',
                'riwayatStudi.periodeAkhir',
                'riwayatStudi.registrasiSemester' => static function (
                    $query
                ): void {
                    $query->orderByDesc('semester_studi')
                        ->orderByDesc('id');
                },
                'riwayatStudi.registrasiSemester.periodeAkademik',
                'riwayatStudi.registrasiSemester.rombel',
            ])
            ->first();

        if (! $profil) {
            throw (new ModelNotFoundException())
                ->setModel(Mahasiswa::class);
        }

        return $profil;
    }

    /**
     * Memastikan profil tertentu adalah milik pengguna yang login.
     */
    public function lihat(User $user, Mahasiswa $mahasiswa): bool
    {
        return $this->batasi(Mahasiswa::query(), $user)
            ->whereKey($mahasiswa->getKey())
            ->exists();
    }

    /**
     * Data akademik penting tidak boleh diperbarui melalui portal.
     */
    public function kolomAkademikDilindungi(): array
    {
        return [
            'id',
            'user_id',
            'nim',
            'status',
            'program_studi_id',
            'kurikulum_id',
            'angkatan',
            'periode_mulai_id',
            'periode_akhir_id',
            'dosen_pa_id',
            'aktif_guard',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Data pribadi terbatas yang nantinya boleh diperbarui.
     */
    public function kolomDapatDiperbarui(): array
    {
        return [
            'telepon',
            'alamat',
        ];
    }
}