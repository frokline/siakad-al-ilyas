<?php

namespace App\Http\Requests;

use App\Models\Kurikulum;
use App\Models\KurikulumMataKuliah;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class KurikulumMataKuliahRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $kurikulum = $this->route('kurikulum');

        return $user instanceof User
            && $kurikulum instanceof Kurikulum
            && Gate::forUser($user)->allows('kelola-kurikulum');
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        $sks = $this->input('sks');

        if (is_string($sks)) {
            $normalized['sks'] = str_replace(',', '.', trim($sks));
        }

        $sifat = $this->input('sifat');

        if (is_string($sifat)) {
            $normalized['sifat'] = strtolower(trim($sifat));
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $kurikulum = $this->route('kurikulum');
        $editing = $this->route('detail') instanceof KurikulumMataKuliah;

        $rules = [
            'version' => [
                'bail',
                'required',
                'string',
                'regex:/\A[a-f0-9]{64}\z/',
            ],
            'kurikulum_id' => ['prohibited'],
            'id' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];

        if ($this->isMethod('DELETE')) {
            return array_merge($rules, [
                'konfirmasi' => ['required', 'accepted'],
                'mata_kuliah_id' => ['prohibited'],
                'sks' => ['prohibited'],
                'semester_rekomendasi' => ['prohibited'],
                'sifat' => ['prohibited'],
            ]);
        }

        return array_merge($rules, [
            'mata_kuliah_id' => $editing
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('mata_kuliah', 'id')
                        ->where('program_studi_id', $kurikulum->program_studi_id)
                        ->where('aktif', true),
                    Rule::unique('kurikulum_mata_kuliah', 'mata_kuliah_id')
                        ->where('kurikulum_id', $kurikulum->getKey()),
                ],

            'sks' => [
                'bail',
                'required',
                'numeric',
                'decimal:0,1',
                'between:0.1,999.9',
            ],

            'semester_rekomendasi' => [
                'bail',
                'required',
                'integer',
                'between:1,32767',
            ],

            'sifat' => [
                'bail',
                'required',
                'string',
                Rule::in(array_keys(KurikulumMataKuliah::SIFAT)),
            ],

            'konfirmasi' => ['prohibited'],
        ]);
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'string' => ':attribute harus berupa teks.',
            'prohibited' => ':attribute tidak boleh dikirim atau diubah.',
            'mata_kuliah_id.min' => 'Mata kuliah tidak valid.',
            'mata_kuliah_id.exists' => 'Pilih mata kuliah aktif dari program studi kurikulum ini.',
            'mata_kuliah_id.unique' => 'Mata kuliah sudah tercantum dalam kurikulum ini.',
            'sks.numeric' => 'SKS harus berupa angka.',
            'sks.decimal' => 'SKS maksimal memiliki satu angka desimal.',
            'sks.between' => 'SKS harus antara 0,1 dan 999,9.',
            'semester_rekomendasi.between' => 'Semester rekomendasi harus antara 1 dan 32767.',
            'sifat.in' => 'Sifat mata kuliah harus Wajib atau Pilihan.',
            'version.required' => 'Versi data tidak tersedia. Muat ulang halaman.',
            'version.regex' => 'Versi data tidak valid. Muat ulang halaman.',
            'konfirmasi.required' => 'Centang konfirmasi pengeluaran mata kuliah.',
            'konfirmasi.accepted' => 'Centang konfirmasi pengeluaran mata kuliah.',
        ];
    }

    public function attributes(): array
    {
        return [
            'mata_kuliah_id' => 'Mata kuliah',
            'kurikulum_id' => 'Kurikulum',
            'sks' => 'SKS',
            'semester_rekomendasi' => 'Semester rekomendasi',
            'sifat' => 'Sifat mata kuliah',
            'version' => 'Versi data',
            'konfirmasi' => 'Konfirmasi',
        ];
    }
}
