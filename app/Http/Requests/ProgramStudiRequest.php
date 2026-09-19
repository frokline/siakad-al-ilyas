<?php

namespace App\Http\Requests;

use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProgramStudiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user('web');

        return $actor instanceof User
            && Gate::forUser($actor)->allows('kelola-program-studi');
    }

    protected function prepareForValidation(): void
    {
        foreach (['kode', 'nama', 'jenjang'] as $field) {
            $value = $this->input($field);

            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            $this->merge([
                $field => $field === 'kode'
                    ? Str::upper($value)
                    : $value,
            ]);
        }
    }

    public function rules(): array
    {
        $programStudi = $this->route('programStudi');
        $editing = $programStudi instanceof ProgramStudi;

        return [
            'kode' => [
                'bail',
                'required',
                'string',
                'max:30',
                'regex:/\A[A-Z0-9][A-Z0-9._-]*\z/',
                Rule::unique('program_studi', 'kode')->ignore(
                    $editing ? $programStudi->getKey() : null,
                ),
            ],

            'nama' => [
                'bail',
                'required',
                'string',
                'max:150',
            ],

            'jenjang' => [
                'bail',
                'required',
                'string',
                'max:30',
            ],

            'aktif' => [
                'required',
                'boolean',
            ],

            'version' => $editing
                ? ['bail', 'required', 'string', 'regex:/\A[a-f0-9]{64}\z/']
                : ['prohibited'],

            'id' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max.string' => ':attribute maksimal :max karakter.',
            'kode.unique' => 'Kode program studi sudah digunakan.',
            'kode.regex' => 'Kode hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung; diawali huruf atau angka.',
            'aktif.boolean' => 'Status program studi tidak valid.',
            'version.required' => 'Muat ulang halaman sebelum menyimpan.',
            'version.regex' => 'Formulir tidak valid. Muat ulang halaman.',
            'prohibited' => ':attribute tidak boleh dikirim.',
        ];
    }

    public function attributes(): array
    {
        return [
            'kode' => 'Kode program studi',
            'nama' => 'Nama program studi',
            'jenjang' => 'Jenjang/program',
            'aktif' => 'Status',
        ];
    }
}
