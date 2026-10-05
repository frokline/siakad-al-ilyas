<?php

namespace App\Actions;

use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PerbaruiProfilMahasiswa
{
    /**
     * Memperbarui data pribadi yang diizinkan.
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function jalankan(User $user, array $data): Mahasiswa
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
            function () use ($user, $data): Mahasiswa {
                $akun = DB::table('users')
                    ->where('id', $user->getKey())
                    ->lockForUpdate()
                    ->first();

                if (! $akun || $akun->status !== 'aktif') {
                    throw new AuthorizationException(
                        'Akun tidak memiliki akses portal mahasiswa.'
                    );
                }

                $memilikiPeranMahasiswa = DB::table('user_roles')
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
                    ->where('roles.kode', Role::MAHASISWA)
                    ->exists();

                if (! $memilikiPeranMahasiswa) {
                    throw new AuthorizationException(
                        'Akun tidak memiliki peran mahasiswa.'
                    );
                }

                $profil = DB::table('mahasiswa')
                    ->where('user_id', $user->getKey())
                    ->lockForUpdate()
                    ->first();

                if (! $profil) {
                    throw new AuthorizationException(
                        'Profil mahasiswa tidak ditemukan.'
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

                if ($profil->alamat !== $alamatBaru) {
                    DB::table('mahasiswa')
                        ->where('id', $profil->id)
                        ->where('user_id', $user->getKey())
                        ->update([
                            'alamat' => $alamatBaru,
                            'updated_at' => $waktu,
                        ]);
                }

                return Mahasiswa::query()
                    ->with('user')
                    ->where('user_id', $user->getKey())
                    ->firstOrFail();
            },
            3
        );
    }
}