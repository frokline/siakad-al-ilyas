<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class AksesPresensiMahasiswa
{
    public function __construct(
        private readonly AksesKrsMahasiswa $aksesKrsMahasiswa
    ) {
    }

    /**
     * Menentukan apakah akun memiliki akses sebagai mahasiswa.
     */
    public function masuk(User $user): bool
    {
        return $this->aksesKrsMahasiswa->masuk($user);
    }

    /**
     * Membatasi query agar mahasiswa hanya melihat presensi miliknya.
     *
     * Riwayat lama tetap dapat dilihat meskipun semester atau KRS
     * sudah selesai karena mahasiswa_id tersimpan pada presensi.
     */
    public function batasi(Builder $query, User $user): Builder
    {
        if (! $this->masuk($user)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn(
            'presensi.mahasiswa_id',
            DB::table('mahasiswa')
                ->select('id')
                ->where('user_id', $user->id)
        );
    }

    /**
     * Memastikan satu record presensi dimiliki mahasiswa yang login.
     */
    public function lihat(User $user, Presensi $presensi): bool
    {
        if (! $this->masuk($user)) {
            return false;
        }

        return $this->batasi(Presensi::query(), $user)
            ->whereKey($presensi->getKey())
            ->exists();
    }

    /**
     * Mengambil presensi atau menghasilkan respons 404.
     *
     * Respons 404 digunakan agar ID presensi milik mahasiswa lain
     * tidak dapat ditebak dari respons 403.
     */
    public function temukan(User $user, int|string $id): Presensi
    {
        return $this->batasi(Presensi::query(), $user)
            ->with([
                'kelasKuliah',
                'detailKrs',
                'daftar.pertemuan',
            ])
            ->findOrFail($id);
    }
}