<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class AksesProfilDosen
{
    public function __construct(
        private readonly AksesKelasDosen $aksesKelasDosen
    ) {
    }

    /**
     * Memastikan akun aktif memiliki role dosen
     * dan profil dosen yang masih aktif.
     */
    public function masuk(User $user): bool
    {
        return $user->exists
            && $this->aksesKelasDosen->masuk($user);
    }

    /**
     * Membatasi query hanya pada profil dosen
     * milik akun yang sedang login.
     */
    public function batasi(Builder $query, User $user): Builder
    {
        if (! $this->masuk($user)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(
            'dosen.user_id',
            $user->getKey()
        );
    }

    /**
     * Mengambil profil dosen milik akun yang login.
     */
    public function profil(User $user): Dosen
    {
        $profil = $this->batasi(Dosen::query(), $user)
            ->with('user')
            ->first();

        if (! $profil) {
            throw (new ModelNotFoundException())
                ->setModel(Dosen::class);
        }

        return $profil;
    }

    /**
     * Memastikan profil tertentu milik pengguna yang login.
     */
    public function lihat(User $user, Dosen $dosen): bool
    {
        return $this->batasi(Dosen::query(), $user)
            ->whereKey($dosen->getKey())
            ->exists();
    }

    /**
     * Kolom identitas akademik yang dilindungi.
     */
    public function kolomAkademikDilindungi(): array
    {
        return [
            'id',
            'user_id',
            'kode_dosen',
            'nidn',
            'gelar',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Data pribadi yang boleh diperbarui dosen.
     * Telepon tersimpan di tabel users.
     * Alamat tersimpan di tabel dosen.
     */
    public function kolomDapatDiperbarui(): array
    {
        return [
            'telepon',
            'alamat',
        ];
    }
}
