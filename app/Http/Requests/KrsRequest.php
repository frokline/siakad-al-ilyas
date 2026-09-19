<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KrsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola-krs') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $normal = [];
        foreach (['catatan', 'alasan', 'versi_form'] as $field) {
            if ($this->exists($field) && is_string($this->input($field))) {
                $nilai = trim($this->input($field));
                $normal[$field] = $nilai === '' ? null : $nilai;
            }
        }
        $this->merge($normal);
    }

    public function rules(): array
    {
        $aksi = $this->route()->getActionMethod();
        $butuhAlasan = in_array($aksi, ['kembalikan', 'revisi', 'pulihkan', 'batalkan'], true);

        $rules = [
            'registrasi_semester_id' => $aksi === 'store'
                ? ['required', 'integer', 'min:1', Rule::exists('registrasi_semester', 'id')]
                : ['prohibited'],
            'catatan' => in_array($aksi, ['store', 'update'], true)
                ? ['nullable', 'string', 'max:2000'] : ['prohibited'],
            'versi_form' => $aksi === 'store'
                ? ['prohibited'] : ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => $butuhAlasan
                ? ['required', 'string', 'min:10', 'max:2000'] : ['prohibited'],
            'konfirmasi' => ['required', 'accepted'],
            '_form' => ['nullable', Rule::in([$aksi])],
        ];

        foreach (
            [
                'id',
                'krs_id',
                'detail_krs_id',
                'kelas_kuliah_id',
                'detail_paket_id',
                'rombel_id',
                'riwayat_studi_id',
                'mahasiswa_id',
                'periode_akademik_id',
                'status',
                'versi',
                'details',
                'mata_kuliah_id',
                'disahkan_oleh',
                'diajukan_at',
                'disahkan_at',
                'aktif_at',
                'batal_at',
                'pelaku_id',
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
            'registrasi_semester_id' => 'registrasi semester',
            'versi_form' => 'versi formulir',
            'catatan' => 'catatan',
            'alasan' => 'alasan tindakan',
            'konfirmasi' => 'konfirmasi tindakan',
        ];
    }

    public function messages(): array
    {
        return [
            'konfirmasi.required' => 'Centang konfirmasi setelah memeriksa data.',
            'konfirmasi.accepted' => 'Centang konfirmasi setelah memeriksa data.',
            'versi_form.required' => 'Formulir tidak lengkap. Muat ulang halaman.',
            'versi_form.regex' => 'Versi formulir tidak valid. Muat ulang halaman.',
            'alasan.min' => 'Tuliskan alasan yang jelas, minimal 10 karakter.',
            '*.prohibited' => 'Kolom :attribute tidak boleh dikirim pada tindakan ini.',
        ];
    }
}
