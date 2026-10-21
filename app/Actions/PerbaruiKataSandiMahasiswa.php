<?php

namespace App\Actions;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class PerbaruiKataSandiMahasiswa
{
    /**
     * Mengganti kata sandi akun mahasiswa yang sedang login.
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function jalankan(User $user, array $data): User
    {
        $kataSandiSaatIni = $data['current_password'] ?? '';
        $kataSandiBaru = $data['password'] ?? '';

        return DB::transaction(
            function () use ($user, $kataSandiSaatIni, $kataSandiBaru): User {
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

                if (! Hash::check($kataSandiSaatIni, $akun->password_hash)) {
                    throw ValidationException::withMessages([
                        'current_password' =>
                            'Kata sandi saat ini tidak benar.',
                    ]);
                }

                DB::table('users')
                    ->where('id', $user->getKey())
                    ->update([
                        'password_hash' => Hash::make($kataSandiBaru),
                        'updated_at' => now(),
                    ]);

                return User::query()
                    ->findOrFail($user->getKey());
            },
            3
        );
    }
}
