<?php

namespace App\Http\Requests;

use App\Models\RiwayatStudi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RiwayatStudiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && Gate::forUser($this->user())
            ->allows('kelola-riwayat-studi');
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (
            [
                'mahasiswa_id',
                'kurikulum_id',
                'angkatan',
                'periode_mulai_id',
                'periode_akhir_id',
                'dosen_pa_id',
            ] as $field
        ) {
            if ($this->exists($field) && is_string($this->input($field))) {
                $value = trim($this->input($field));

                $normalized[$field] = $value === '' ? null : $value;
            }
        }

        if ($this->exists('status') && is_string($this->input('status'))) {
            $normalized['status'] = strtolower(trim($this->input('status')));
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $editing = $this->route('riwayatStudi') instanceof RiwayatStudi;

        $closing = $editing
            && $this->input('status') !== RiwayatStudi::AKTIF;

        return [
            'mahasiswa_id' => $editing
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('mahasiswa', 'id'),
                ],

            'kurikulum_id' => $editing
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('kurikulum', 'id')
                        ->where('status', 'aktif'),
                ],

            'angkatan' => $editing
                ? ['prohibited']
                : ['bail', 'required', 'integer', 'between:1900,9999'],

            'periode_mulai_id' => $editing
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('periode_akademik', 'id'),
                ],

            'dosen_pa_id' => [
                'bail',
                'nullable',
                'integer',
                'min:1',
                Rule::exists('dosen', 'id'),
            ],

            'status' => $editing
                ? [
                    'bail',
                    'required',
                    'string',
                    Rule::in(array_keys(RiwayatStudi::STATUS)),
                ]
                : ['prohibited'],

            'periode_akhir_id' => $closing
                ? [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('periode_akademik', 'id'),
                ]
                : ['prohibited'],

            'konfirmasi_penutupan' => $editing
                ? (
                    $closing
                    ? ['required', 'accepted']
                    : ['nullable', 'boolean']
                )
                : ['prohibited'],

            'version' => $editing
                ? ['bail', 'required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/']
                : ['prohibited'],

            'id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'program_studi_id' => ['prohibited'],
            'aktif_guard' => ['prohibited'],
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
            'between' => ':attribute harus antara :min dan :max.',
            'exists' => ':attribute tidak tersedia atau sudah berubah.',
            'in' => ':attribute tidak valid.',
            'prohibited' => ':attribute tidak boleh diubah pada tindakan ini.',

            'konfirmasi_penutupan.required' =>
            'Centang konfirmasi sebelum menutup riwayat studi.',
            'konfirmasi_penutupan.accepted' =>
            'Penutupan riwayat studi harus dikonfirmasi.',
            'konfirmasi_penutupan.boolean' =>
            'Konfirmasi penutupan tidak valid.',

            'version.required' => 'Muat ulang formulir sebelum menyimpan.',
            'version.size' => 'Versi formulir tidak valid. Muat ulang halaman.',
            'version.regex' => 'Versi formulir tidak valid. Muat ulang halaman.',
        ];
    }

    public function attributes(): array
    {
        return [
            'mahasiswa_id' => 'Mahasiswa',
            'kurikulum_id' => 'Kurikulum',
            'angkatan' => 'Angkatan',
            'periode_mulai_id' => 'Periode mulai',
            'periode_akhir_id' => 'Periode akhir',
            'dosen_pa_id' => 'Dosen PA',
            'status' => 'Status riwayat',
            'konfirmasi_penutupan' => 'Konfirmasi penutupan',
            'version' => 'Versi formulir',
        ];
    }
}
