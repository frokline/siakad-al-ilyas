<?php

namespace App\Http\Requests;

use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MataKuliahRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');

        return $user instanceof User
            && Gate::forUser($user)->allows('kelola-mata-kuliah');
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['kode', 'nama'] as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            $normalized[$field] = $field === 'kode'
                ? Str::upper($value)
                : $value;
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $editing = $this->route('mataKuliah') instanceof MataKuliah;

        return [
            'program_studi_id' => $editing
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('program_studi', 'id'),
                ],

            'kode' => $editing
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'string',
                    'max:40',
                    'regex:/\A[A-Z0-9][A-Z0-9._-]*\z/',
                ],

            'nama' => [
                'bail',
                'required',
                'string',
                'max:150',
            ],

            'aktif' => [
                'bail',
                'required',
                'boolean',
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
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $validator->errors()->isNotEmpty()
                    || $this->route('mataKuliah') instanceof MataKuliah
                ) {
                    return;
                }

                $exists = MataKuliah::query()
                    ->where(
                        'program_studi_id',
                        (int) $this->input('program_studi_id')
                    )
                    ->where('kode', $this->input('kode'))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'kode',
                        'Kode mata kuliah sudah digunakan pada program studi ini.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'max' => ':attribute maksimal :max karakter.',
            'prohibited' => ':attribute tidak boleh dikirim atau diubah.',
            'program_studi_id.min' => 'Program studi tidak valid.',
            'program_studi_id.exists' => 'Program studi tidak ditemukan.',
            'program_studi_id.prohibited' => 'Program studi hanya ditentukan saat pembuatan.',
            'kode.prohibited' => 'Kode mata kuliah hanya ditentukan saat pembuatan.',
            'kode.regex' => 'Kode hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
            'aktif.boolean' => 'Status harus Aktif atau Nonaktif.',
            'version.required' => 'Versi data tidak tersedia. Muat ulang formulir.',
            'version.regex' => 'Versi data tidak valid. Muat ulang formulir.',
        ];
    }

    public function attributes(): array
    {
        return [
            'program_studi_id' => 'Program studi',
            'kode' => 'Kode mata kuliah',
            'nama' => 'Nama mata kuliah',
            'aktif' => 'Status',
            'version' => 'Versi data',
        ];
    }
}
