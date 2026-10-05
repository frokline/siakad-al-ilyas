<?php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\Pengumpulan;
use App\Models\User;
use App\Services\AksesPengumpulan;

class PengumpulanPolicy
{
    public function __construct(
        private readonly AksesPengumpulan $akses
    ) {
    }

    /**
     * Pengguna portal pengumpulan dapat melihat daftar
     * yang sudah dibatasi oleh layanan akses.
     */
    public function viewAny(User $user): bool
    {
        return $this->akses->masuk($user);
    }

    /**
     * Mahasiswa hanya melihat jawabannya sendiri.
     * Dosen hanya melihat jawaban kelas penugasannya.
     */
    public function view(
        User $user,
        Pengumpulan $pengumpulan
    ): bool {
        return $this->akses->lihat(
            $user,
            $pengumpulan
        );
    }

    /**
     * Mahasiswa dapat membuka ruang jawaban kegiatan
     * yang memang diikutinya.
     */
    public function ruangSaya(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $this->akses->ruangSaya(
            $user,
            $kegiatan
        );
    }

    /**
     * Hanya dosen yang mengelola kelas tersebut
     * yang dapat melihat rekap.
     */
    public function rekap(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $this->akses->kelola(
            $user,
            $kegiatan
        );
    }

    /**
     * Mahasiswa dapat membuat jawaban selama
     * jendela pengumpulan masih terbuka.
     */
    public function create(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $this->akses->bolehTulis(
            $user,
            $kegiatan
        );
    }

    /**
     * Jawaban terkirim dapat diperbarui oleh pemiliknya
     * selama tenggat belum berakhir.
     */
    public function update(
        User $user,
        Pengumpulan $pengumpulan
    ): bool {
        return $pengumpulan->status
                === Pengumpulan::TERKIRIM
            && $this->akses->pemilik(
                $user,
                $pengumpulan
            )
            && $this->akses->bolehTulis(
                $user,
                $pengumpulan->kegiatan
            );
    }

    /**
     * Tombol hapus sebenarnya melakukan pembatalan logis.
     * Data tetap disimpan sebagai riwayat audit.
     */
    public function delete(
        User $user,
        Pengumpulan $pengumpulan
    ): bool {
        return $pengumpulan->status
                === Pengumpulan::TERKIRIM
            && $this->akses->pemilik(
                $user,
                $pengumpulan
            )
            && $this->akses->bolehTulis(
                $user,
                $pengumpulan->kegiatan
            );
    }

    /**
     * Pengumpulan tidak pernah dihapus permanen.
     */
    public function forceDelete(
        User $user,
        Pengumpulan $pengumpulan
    ): bool {
        return false;
    }

    /**
     * Riwayat pembatalan tidak dipulihkan lewat Eloquent.
     */
    public function restore(
        User $user,
        Pengumpulan $pengumpulan
    ): bool {
        return false;
    }
}