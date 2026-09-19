<?php

namespace App\Http\Requests;

use App\Models\PengajarKelas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PengajarKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola-pengajar-kelas') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $normal = [];
        foreach (['peran', 'alasan', 'versi_tim'] as $field) {
            if ($this->exists($field) && is_string($this->input($field))) {
                $normal[$field] = trim($this->input($field));
            }
        }
        $this->merge($normal);
    }

    public function rules(): array
    {
        $membuat = $this->route()->getActionMethod() === 'store';
        $rules = [
            'kelas_kuliah_id' => $membuat
                ? ['required', 'integer', 'min:1', Rule::exists('kelas_kuliah', 'id')] : ['prohibited'],
            'dosen_id' => $membuat
                ? ['required', 'integer', 'min:1', Rule::exists('dosen', 'id')] : ['prohibited'],
            'peran' => ['required', Rule::in(array_keys(PengajarKelas::PERAN))],
            'aktif' => $membuat ? ['prohibited'] : ['required', 'boolean'],
            'versi_tim' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:2000'],
            'konfirmasi' => ['required', 'accepted'],
        ];

        foreach (
            [
                'id',
                'pengajar_kelas_id',
                'revisi',
                'koordinator_aktif',
                'diaktifkan_at',
                'dinonaktifkan_at',
                'user_id',
                'pelaku_id',
                'kelas',
                'tim',
                'rombel_id',
                'periode_akademik_id',
                'created_at',
                'updated_at',
            ] as $field
        ) {
            $rules[$field] = ['prohibited'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'kelas_kuliah_id' => 'kelas kuliah',
            'dosen_id' => 'dosen',
            'versi_tim' => 'versi tim pengajar',
            'peran' => 'peran pengajar',
            'aktif' => 'status penugasan',
            'alasan' => 'alasan penugasan/perubahan',
            'konfirmasi' => 'konfirmasi tindakan',
        ];
    }

    public function messages(): array
    {
        return [
            'konfirmasi.required' => 'Centang konfirmasi setelah memeriksa penugasan dan dampaknya.',
            'konfirmasi.accepted' => 'Centang konfirmasi setelah memeriksa penugasan dan dampaknya.',
            'alasan.min' => 'Tuliskan alasan yang jelas, minimal 10 karakter.',
            'versi_tim.required' => 'Formulir tidak lengkap. Muat ulang halaman.',
            'versi_tim.regex' => 'Versi tim tidak valid. Muat ulang halaman.',
            '*.prohibited' => 'Kolom :attribute tidak boleh dikirim pada tindakan ini.',
        ];
    }
}
