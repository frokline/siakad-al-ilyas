<?php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\KelasKuliah;
use App\Models\User;
use App\Services\AksesKegiatan;

final class KegiatanPolicy
{
    public function __construct(
        private readonly AksesKegiatan $akses
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->akses->masuk($user);
    }

    public function create(
        User $user,
        KelasKuliah $kelas
    ): bool {
        return $this->akses->kelola(
            $user,
            (int) $kelas->id
        )
            && $this->akses->konteksTulis(
                (int) $kelas->id
            );
    }

    public function view(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $this->akses->lihat(
            $user,
            $kegiatan
        );
    }

    public function bacaIsi(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $this->akses->bacaIsi(
            $user,
            $kegiatan
        );
    }

    public function manage(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $this->akses->kelola(
            $user,
            (int) $kegiatan->kelas_kuliah_id
        );
    }

    public function update(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return in_array(
            $kegiatan->status,
            [
                Kegiatan::TERBIT,
                Kegiatan::DITUTUP,
            ],
            true
        )
            && $this->manage(
                $user,
                $kegiatan
            )
            && $this->akses->konteksTulis(
                (int) $kegiatan->kelas_kuliah_id
            );
    }

    public function tutup(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $kegiatan->status
                === Kegiatan::TERBIT
            && $this->manage(
                $user,
                $kegiatan
            );
    }

    public function bukaKembali(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $kegiatan->status
                === Kegiatan::DITUTUP
            && $this->aktif(
                $user,
                $kegiatan
            );
    }

    public function perpanjang(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $kegiatan->memerlukanPengumpulan()
            && in_array(
                $kegiatan->status,
                [
                    Kegiatan::TERBIT,
                    Kegiatan::DITUTUP,
                ],
                true
            )
            && $this->aktif(
                $user,
                $kegiatan
            );
    }

    public function arsipkan(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $kegiatan->status
                !== Kegiatan::ARSIP
            && $this->manage(
                $user,
                $kegiatan
            );
    }

    public function pulihkan(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $kegiatan->status
                === Kegiatan::ARSIP
            && $this->manage(
                $user,
                $kegiatan
            )
            && $this->akses->konteksTulis(
                (int) $kegiatan->kelas_kuliah_id
            );
    }

    private function aktif(
        User $user,
        Kegiatan $kegiatan
    ): bool {
        return $this->manage(
            $user,
            $kegiatan
        )
            && $this->akses->konteksTulis(
                (int) $kegiatan->kelas_kuliah_id
            )
            && KelasKuliah::query()
                ->whereKey(
                    $kegiatan->kelas_kuliah_id
                )
                ->where('status', 'aktif')
                ->exists();
    }
}