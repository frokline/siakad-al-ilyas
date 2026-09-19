<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CreateInitialAdmin extends Command
{
    protected $signature = 'siakad:buat-admin';

    protected $description = 'Membuat akun admin akademik pertama';

    public function handle(): int
    {
        $role = Role::query()
            ->where('kode', Role::ADMIN_AKADEMIK)
            ->first();

        if (! $role) {
            $this->error(
                'Peran admin_akademik belum tersedia. Jalankan migration terlebih dahulu.',
            );

            return self::FAILURE;
        }

        if ($role->users()->exists()) {
            $this->error(
                'Admin akademik sudah tersedia. Perintah ini hanya untuk admin pertama.',
            );

            return self::FAILURE;
        }

        $this->line(
            'Password: minimal 12 karakter, huruf besar/kecil, angka, simbol; maksimal 72 byte.',
        );

        $validator = Validator::make([
            'nama' => trim((string) $this->ask('Nama lengkap')),

            'username' => Str::lower(
                trim((string) $this->ask('Username')),
            ),

            'email' => Str::lower(
                trim((string) $this->ask('Email')),
            ),

            'password' => (string) $this->secret(
                'Kata sandi',
                false,
            ),

            'password_confirmation' => (string) $this->secret(
                'Ulangi kata sandi',
                false,
            ),
        ], [
            'nama' => [
                'bail',
                'required',
                'string',
                'max:150',
            ],

            'username' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/\A[a-z0-9][a-z0-9._-]*\z/',
                'unique:users,username',
            ],

            'email' => [
                'bail',
                'required',
                'string',
                'email:rfc',
                'max:190',
                'unique:users,email',
            ],

            'password' => [
                'bail',
                'required',
                'string',
                'confirmed',

                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail,
                ): void {
                    if (strlen($value) > 72) {
                        $fail('Kata sandi maksimal 72 byte.');
                    }
                },
            ],
        ], [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah digunakan.',
            'username.regex' => 'Username harus diawali huruf/angka dan hanya boleh berisi a-z, 0-9, titik, garis bawah, atau tanda hubung.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $validated = $validator->validated();

        $profile = [
            'nama' => $validated['nama'],
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];

        $passwordHash = Hash::make($validated['password']);

        try {
            $admin = DB::transaction(
                function () use ($role, $profile, $passwordHash): User {
                    $lockedRole = Role::query()
                        ->whereKey($role->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($lockedRole->users()->exists()) {
                        throw ValidationException::withMessages([
                            'admin' => 'Admin akademik sudah dibuat oleh proses lain.',
                        ]);
                    }

                    $user = new User();
                    $user->fill($profile);
                    $user->password_hash = $passwordHash;
                    $user->status = User::STATUS_AKTIF;
                    $user->save();

                    $user->roles()->attach($lockedRole->getKey());

                    return $user;
                },
                3,
            );
        } catch (UniqueConstraintViolationException) {
            $this->error(
                'Username atau email sudah digunakan. Jalankan ulang dengan data berbeda.',
            );

            return self::FAILURE;
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        $this->info('Admin berhasil dibuat.');
        $this->line('Username: ' . $admin->username);

        return self::SUCCESS;
    }
}
