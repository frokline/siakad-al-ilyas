<?php

namespace App\Actions;

use App\Models\Dosen;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class PerbaruiKataSandiDosen
{
    /**
     * Mengganti kata sandi akun dosen yang sedang login.
     *
     * Memverifikasi password lama, status akun,
     * role dosen, dan status profil dosen.
     */
    public function jalankan(User $user, array $data): User
    {
        $kataSandiSaatIni = $data['current_password'] ?? '';
        $kataSandiBaru = $data['password'] ?? '';

        if (
            ! is_string($kataSandiSaatIni)
            || ! is_string($kataSandiBaru)
            || $kataSandiSaatIni === ''
            || $kataSandiBaru === ''
        ) {
            throw ValidationException::withMessages([
                'password' => 'Data kata sandi tidak valid.',
            ]);
        }

        return DB::transaction(
            function () use (
                $user,
                $kataSandiSaatIni,
                $kataSandiBaru
            ): User {
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

                if (! Hash::check(
                    $kataSandiSaatIni,
                    $akun->password_hash
                )) {
                    throw ValidationException::withMessages([
                        'current_password' =>
                            'Kata sandi saat ini tidak benar.',
                    ]);
                }

                if (Hash::check(
                    $kataSandiBaru,
                    $akun->password_hash
                )) {
                    throw ValidationException::withMessages([
                        'password' =>
                            'Kata sandi baru harus berbeda dari kata sandi saat ini.',
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