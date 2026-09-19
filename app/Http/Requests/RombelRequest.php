<?php

namespace App\Http\Requests;

use App\Models\PaketSemester;
use App\Models\Rombel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RombelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && Gate::forUser($this->user())->allows('kelola-rombel');
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->exists('kode') && is_string($this->input('kode'))) {
            $normalized['kode'] = strtoupper(trim($this->input('kode')));
        }

        foreach (
            [
                'periode_akademik_id',
                'paket_semester_id',
                'kapasitas',
            ] as $field
        ) {
            if ($this->exists($field) && is_string($this->input($field))) {
                $value = trim($this->input($field));

                $normalized[$field] = $value === '' ? null : $value;
            }
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $rombel = $this->route('rombel');
        $editing = $rombel instanceof Rombel;

        $inputPeriode = $this->input('periode_akademik_id');

        $periodeId = $editing
            ? $rombel->periode_akademik_id
            : (
                is_scalar($inputPeriode)
                ? (int) $inputPeriode
                : 0
            );

        $uniqueKode = Rule::unique('rombel', 'kode')
            ->where('periode_akademik_id', $periodeId);

        if ($editing) {
            $uniqueKode->ignore($rombel);
        }

        return [
            'periode_akademik_id' => $editing
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('periode_akademik', 'id')
                        ->whereIn(
                            'status',
                            Rombel::STATUS_PERIODE_TERBUKA
                        ),
                ],

            'paket_semester_id' => $editing
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('paket_semester', 'id')
                        ->where('status', PaketSemester::DITERBITKAN),
                ],

            'kode' => [
                'bail',
                'required',
                'string',
                'max:40',
                'regex:/\A[A-Z0-9][A-Z0-9._-]*\z/',
                $uniqueKode,
            ],

            'kapasitas' => [
                'bail',
                'nullable',
                'integer',
                'between:1,32767',
            ],

            'version' => $editing
                ? [
                    'bail',
                    'required',
                    'string',
                    'size:64',
                    'regex:/\A[a-f0-9]{64}\z/',
                ]
                : ['prohibited'],

            'id' => ['prohibited'],
            'kurikulum_id' => ['prohibited'],
            'program_studi_id' => ['prohibited'],
            'semester_studi' => ['prohibited'],
            'status' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'string' => ':attribute harus berupa teks.',
            'min' => ':attribute tidak valid.',
            'exists' => ':attribute tidak tersedia atau sudah berubah.',
            'prohibited' => ':attribute tidak boleh diubah pada tindakan ini.',

            'kode.max' => 'Kode rombel maksimal 40 karakter.',
            'kode.regex' =>
            'Kode diawali huruf atau angka dan hanya boleh berisi '
                . 'huruf, angka, titik, garis bawah, atau tanda hubung.',
            'kode.unique' =>
            'Kode rombel sudah digunakan pada periode akademik ini.',

            'kapasitas.between' =>
            'Kapasitas harus antara 1 dan 32767, atau dikosongkan.',

            'version.required' => 'Muat ulang formulir sebelum menyimpan.',
            'version.size' => 'Versi formulir tidak valid.',
            'version.regex' => 'Versi formulir tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'periode_akademik_id' => 'Periode akademik',
            'paket_semester_id' => 'Paket semester',
            'kode' => 'Kode rombel',
            'kapasitas' => 'Kapasitas',
            'version' => 'Versi formulir',
        ];
    }
}
