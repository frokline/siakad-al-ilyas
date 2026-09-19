<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ManageUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user('web');

        return $actor instanceof User
            && Gate::forUser($actor)->allows('kelola-pengguna');
    }

    protected function prepareForValidation(): void
    {
        foreach (['nama', 'username', 'email', 'telepon'] as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if (in_array($field, ['username', 'email'], true)) {
                $value = Str::lower($value);
            }

            $this->merge([$field => $value]);
        }
    }

    public function rules(): array
    {
        $target = $this->route('user');
        $editing = $target instanceof User;
        $targetId = $editing ? $target->getKey() : null;

        $limitBytes = static function (
            string $attribute,
            mixed $value,
            Closure $fail,
        ): void {
            if (is_string($value) && strlen($value) > 72) {
                $fail('Kata sandi maksimal 72 byte.');
            }
        };

        $rules = [
            'current_password' => [
                'bail',
                'required',
                'string',
                'max:72',
                $limitBytes,
            ],

            'version' => $editing
                ? ['bail', 'required', 'string', 'regex:/\A[a-f0-9]{64}\z/']
                : ['prohibited'],

            'id' => ['prohibited'],
            'password_hash' => ['prohibited'],
            'remember_token' => ['prohibited'],
        ];

        if ($this->isMethod('DELETE')) {
            return $rules;
        }

        return array_merge($rules, [
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
                Rule::unique('users', 'username')->ignore($targetId),
            ],

            'email' => [
                'bail',
                'required',
                'string',
                'email:rfc',
                'max:190',
                Rule::unique('users', 'email')->ignore($targetId),
            ],

            'telepon' => [
                'bail',
                'nullable',
                'string',
                'max:30',
                'regex:/\A\+?[0-9][0-9 ()-]{5,28}\z/',
            ],

            'status' => [
                'required',
                Rule::in([
                    User::STATUS_AKTIF,
                    User::STATUS_NONAKTIF,
                ]),
            ],

            'roles' => [
                'bail',
                'required',
                'array',
                'list',
                'min:1',
                'max:' . count(Role::KODE_SISTEM),
            ],

            'roles.*' => [
                'bail',
                'required',
                'integer',
                'distinct',
                Rule::exists('roles', 'id')->where(
                    fn($query) => $query->whereIn(
                        'kode',
                        Role::KODE_SISTEM,
                    ),
                ),
            ],

            'password' => [
                'bail',
                $editing ? 'nullable' : 'required',
                'string',
                'max:72',
                'confirmed',
                $limitBytes,

                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'unique' => ':attribute sudah digunakan.',
            'email.email' => 'Alamat email tidak valid.',
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, titik, garis bawah, atau tanda hubung; diawali huruf atau angka.',
            'telepon.regex' => 'Format nomor telepon tidak valid.',
            'status.in' => 'Status akun tidak valid.',
            'roles.required' => 'Pilih minimal satu peran.',
            'roles.min' => 'Pilih minimal satu peran.',
            'roles.array' => 'Format peran tidak valid.',
            'roles.list' => 'Format peran tidak valid.',
            'roles.*.exists' => 'Peran yang dipilih tidak tersedia.',
            'roles.*.distinct' => 'Peran yang sama tidak boleh dipilih berulang.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'version.required' => 'Muat ulang halaman sebelum menyimpan.',
            'version.regex' => 'Formulir tidak valid. Muat ulang halaman.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'Nama lengkap',
            'username' => 'Username',
            'email' => 'Email',
            'telepon' => 'Nomor telepon',
            'status' => 'Status',
            'roles' => 'Peran',
            'roles.*' => 'Peran',
            'password' => 'Kata sandi pengguna',
            'current_password' => 'Kata sandi admin',
        ];
    }
}
