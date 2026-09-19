<?php

namespace App\Http\Requests;

use App\Models\Kurikulum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class KurikulumRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');

        return $user instanceof User
            && Gate::forUser($user)->allows('kelola-kurikulum');
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['kode', 'nama', 'status'] as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            $normalized[$field] = match ($field) {
                'kode' => Str::upper($value),
                'status' => Str::lower($value),
                default => $value,
            };
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $kurikulum = $this->route('kurikulum');
        $editing = $kurikulum instanceof Kurikulum;

        $locked = $editing
            && ! $kurikulum->identitasDapatDiubah();

        return [
            'program_studi_id' => $locked
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('program_studi', 'id'),
                ],

            'kode' => $locked
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'string',
                    'max:40',
                    'regex:/\A[A-Z0-9][A-Z0-9._-]*\z/',
                ],

            'nama' => $locked
                ? ['prohibited']
                : ['bail', 'required', 'string', 'max:150'],

            'tahun_berlaku' => $locked
                ? ['prohibited']
                : ['bail', 'required', 'integer', 'between:1900,9999'],

            'status' => $editing
                ? [
                    'bail',
                    'required',
                    'string',
                    Rule::in(array_keys($kurikulum->pilihanStatus())),
                ]
                : ['prohibited'],

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
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $kurikulum = $this->route('kurikulum');

                if (
                    $kurikulum instanceof Kurikulum
                    && ! $kurikulum->identitasDapatDiubah()
                ) {
                    return;
                }

                $query = Kurikulum::query()
                    ->where(
                        'program_studi_id',
                        (int) $this->input('program_studi_id')
                    )
                    ->where('kode', $this->input('kode'));

                if ($kurikulum instanceof Kurikulum) {
                    $query->where('id', '<>', $kurikulum->getKey());
                }

                if ($query->exists()) {
                    $validator->errors()->add(
                        'kode',
                        'Kode kurikulum sudah digunakan pada program studi ini.'
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
            'prohibited' => ':attribute tidak boleh dikirim atau diubah pada kondisi ini.',
            'program_studi_id.min' => 'Program studi tidak valid.',
            'program_studi_id.exists' => 'Program studi tidak ditemukan.',
            'kode.regex' => 'Kode hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
            'tahun_berlaku.between' => 'Tahun berlaku harus antara 1900 dan 9999.',
            'status.in' => 'Perubahan status tersebut tidak diizinkan.',
            'version.required' => 'Versi data tidak tersedia. Muat ulang formulir.',
            'version.regex' => 'Versi data tidak valid. Muat ulang formulir.',
        ];
    }

    public function attributes(): array
    {
        return [
            'program_studi_id' => 'Program studi',
            'kode' => 'Kode kurikulum',
            'nama' => 'Nama kurikulum',
            'tahun_berlaku' => 'Tahun berlaku',
            'status' => 'Status',
            'version' => 'Versi data',
        ];
    }
}
