<?php

namespace App\Http\Requests;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DosenRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');

        return $user instanceof User
            && Gate::forUser($user)->allows('kelola-dosen');
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        $kodeDosen = $this->input('kode_dosen');

        if (is_string($kodeDosen)) {
            $normalized['kode_dosen'] = Str::upper(trim($kodeDosen));
        }

        $status = $this->input('status');

        if (is_string($status)) {
            $normalized['status'] = Str::lower(trim($status));
        }

        foreach (['nidn', 'gelar'] as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            $normalized[$field] = $value === '' ? null : $value;
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $dosen = $this->route('dosen');
        $editing = $dosen instanceof Dosen;

        $kodeUnique = Rule::unique('dosen', 'kode_dosen');
        $nidnUnique = Rule::unique('dosen', 'nidn');

        if ($editing) {
            $kodeUnique->ignore($dosen);
            $nidnUnique->ignore($dosen);
        }

        return [
            'user_id' => $editing
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('users', 'id')
                        ->where('status', User::STATUS_AKTIF),
                    Rule::unique('dosen', 'user_id'),
                ],

            'kode_dosen' => [
                'bail',
                'required',
                'string',
                'max:40',
                'regex:/\A[A-Z0-9][A-Z0-9._-]*\z/',
                $kodeUnique,
            ],

            'nidn' => [
                'bail',
                'nullable',
                'string',
                'max:40',
                'regex:/\A[0-9]+\z/',
                $nidnUnique,
            ],

            'gelar' => [
                'bail',
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'bail',
                'required',
                'string',
                Rule::in(array_keys(Dosen::STATUS)),
            ],

            'version' => $editing
                ? [
                    'bail',
                    'required',
                    'string',
                    'regex:/\A[a-f0-9]{64}\z/',
                ]
                : ['prohibited'],

            'id' => ['prohibited'],
            'nama' => ['prohibited'],
            'email' => ['prohibited'],
            'telepon' => ['prohibited'],
            'status_akun' => ['prohibited'],
            'roles' => ['prohibited'],
            'password' => ['prohibited'],
            'password_hash' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'max' => ':attribute maksimal :max karakter.',
            'prohibited' => ':attribute tidak boleh dikirim atau diubah melalui formulir ini.',
            'user_id.min' => 'Akun pengguna tidak valid.',
            'user_id.exists' => 'Pilih akun pengguna yang aktif.',
            'user_id.unique' => 'Akun sudah terhubung dengan dosen lain.',
            'kode_dosen.regex' => 'Kode dosen hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
            'kode_dosen.unique' => 'Kode dosen sudah digunakan.',
            'nidn.regex' => 'NIDN hanya boleh berisi angka tanpa spasi atau tanda baca.',
            'nidn.unique' => 'NIDN sudah digunakan oleh dosen lain.',
            'status.in' => 'Status dosen harus Aktif atau Nonaktif.',
            'version.required' => 'Versi data tidak tersedia. Muat ulang formulir.',
            'version.regex' => 'Versi data tidak valid. Muat ulang formulir.',
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'Akun pengguna',
            'kode_dosen' => 'Kode dosen',
            'nidn' => 'NIDN',
            'gelar' => 'Gelar',
            'status' => 'Status dosen',
            'status_akun' => 'Status akun',
            'version' => 'Versi data',
        ];
    }
}
