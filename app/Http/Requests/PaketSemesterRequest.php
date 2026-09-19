<?php

namespace App\Http\Requests;

use App\Models\Kurikulum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PaketSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && Gate::forUser($this->user())
            ->allows('kelola-paket-semester');
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['nama', 'kurikulum_id', 'semester_studi'] as $field) {
            if ($this->exists($field) && is_string($this->input($field))) {
                $normalized[$field] = trim($this->input($field));
            }
        }

        if (
            $this->routeIs('admin.paket-semester.update')
            && ! $this->exists('mata_kuliah_ids')
        ) {
            $normalized['mata_kuliah_ids'] = [];
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $creating = $this->routeIs('admin.paket-semester.store');
        $updating = $this->routeIs('admin.paket-semester.update');

        $statusAction = $this->routeIs(
            'admin.paket-semester.terbitkan',
            'admin.paket-semester.arsipkan'
        );

        $rules = [
            'kurikulum_id' => $creating
                ? [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('kurikulum', 'id')
                        ->where('status', Kurikulum::AKTIF),
                ]
                : ['prohibited'],

            'semester_studi' => $creating
                ? ['bail', 'required', 'integer', 'between:1,32767']
                : ['prohibited'],

            'nama' => $statusAction
                ? ['prohibited']
                : ['bail', 'required', 'string', 'max:100'],

            'mata_kuliah_ids' => $updating
                ? ['present', 'array', 'max:500']
                : ['prohibited'],

            'version' => $creating
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'string',
                    'size:64',
                    'regex:/\A[a-f0-9]{64}\z/',
                ],

            'konfirmasi' => $statusAction
                ? ['required', 'accepted']
                : ['prohibited'],

            'id' => ['prohibited'],
            'versi' => ['prohibited'],
            'status' => ['prohibited'],
            'program_studi_id' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];

        if ($updating) {
            $rules['mata_kuliah_ids.*'] = [
                'bail',
                'required',
                'integer',
                'min:1',
                'distinct',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'present' => ':attribute harus disertakan.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'string' => ':attribute harus berupa teks.',
            'array' => ':attribute harus berupa daftar.',
            'between' => ':attribute harus antara :min dan :max.',
            'exists' => ':attribute tidak tersedia atau sudah tidak aktif.',
            'prohibited' => ':attribute tidak boleh diubah pada tindakan ini.',

            'nama.max' => 'Nama paket maksimal 100 karakter.',

            'mata_kuliah_ids.max' =>
            'Maksimal 500 mata kuliah dalam satu permintaan.',
            'mata_kuliah_ids.*.integer' => 'Pilihan mata kuliah tidak valid.',
            'mata_kuliah_ids.*.min' => 'Pilihan mata kuliah tidak valid.',
            'mata_kuliah_ids.*.distinct' =>
            'Mata kuliah yang sama tidak boleh dipilih lebih dari sekali.',

            'version.required' => 'Muat ulang halaman sebelum menyimpan.',
            'version.size' => 'Versi formulir tidak valid.',
            'version.regex' => 'Versi formulir tidak valid.',

            'konfirmasi.required' => 'Centang konfirmasi untuk melanjutkan.',
            'konfirmasi.accepted' => 'Tindakan harus dikonfirmasi.',
        ];
    }

    public function attributes(): array
    {
        return [
            'kurikulum_id' => 'Kurikulum',
            'semester_studi' => 'Semester studi',
            'nama' => 'Nama paket',
            'mata_kuliah_ids' => 'Pilihan mata kuliah',
            'version' => 'Versi formulir',
            'konfirmasi' => 'Konfirmasi',
        ];
    }
}
