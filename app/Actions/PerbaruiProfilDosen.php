<?php

namespace App\Actions;

use App\Models\Dosen;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PerbaruiProfilDosen
{
    /**
     * Memperbarui data pribadi dosen yang diizinkan.
     *
     * Telepon disimpan pada tabel users.
     * Alamat disimpan pada tabel dosen.
     */
    public function jalankan(User $user, array $data): Dosen
    {
        $kolomDiizinkan = [
            'telepon',
            'alamat',
        ];

        $kolomTidakDiizinkan = array_diff(
            array_keys($data),
            $kolomDiizinkan
        );

        if ($kolomTidakDiizinkan !== []) {
            throw ValidationException::withMessages([
                'profil' =>
                    'Permintaan memuat kolom profil yang tidak diizinkan.',
            ]);
        }

        return DB::transaction(
            function () use ($user, $data): Dosen {
                $akun = DB::table('users')
                    ->where('id', $user->getKey())
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $akun
                    || $akun->status !== User::STATUS_AKTIF
                ) {
                    throw new AuthorizationException(
                        'Akun tidak memiliki akses portal dosen.'
                    );
                }

                $memilikiPeranDosen = DB::table('user_roles')
                    ->join(
                        'roles',
                        'roles.id',
                        '=',
                        'user_roles.role_id'
                    )
                    ->where(
                        'user_roles.user_id',
                        $user->getKey()
                    )
                    ->where('roles.kode', Role::DOSEN)
                    ->exists();

                if (! $memilikiPeranDosen) {
                    throw new AuthorizationException(
                        'Akun tidak memiliki peran dosen.'
                    );
                }

                $profil = DB::table('dosen')
                    ->where('user_id', $user->getKey())
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $profil
                    || $profil->status !== Dosen::AKTIF
                ) {
                    throw new AuthorizationException(
                        'Profil dosen tidak ditemukan atau tidak aktif.'
                    );
                }

                $waktu = now();

                $teleponBaru = $data['telepon'] ?? null;
                $alamatBaru = $data['alamat'] ?? null;

                if ($akun->telepon !== $teleponBaru) {
                    DB::table('users')
                        ->where('id', $user->getKey())
                        ->update([
                            'telepon' => $teleponBaru,
                            'updated_at' => $waktu,
                        ]);
                }

                if (($profil->alamat ?? null) !== $alamatBaru) {
                    DB::table('dosen')
                        ->where('id', $profil->id)
                        ->where('user_id', $user->getKey())
                        ->update([
                            'alamat' => $alamatBaru,
                            'updated_at' => $waktu,
                        ]);
                }

                return Dosen::query()
                    ->with('user')
                    ->where('user_id', $user->getKey())
                    ->firstOrFail();
            },
            3
        );
    }
}