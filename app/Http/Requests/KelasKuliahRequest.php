<?php

namespace App\Http\Requests;

use App\Models\KelasKuliah;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelasKuliahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola-kelas-kuliah') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach (['rombel_id', 'detail_paket_id', 'kode', 'status', 'versi'] as $key) {
            if (is_string($this->input($key))) {
                $value = trim($this->input($key));
                $data[$key] = $value === '' ? null : ($key === 'kode' ? strtoupper($value) : $value);
            }
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        $kelas = $this->route('kelasKuliah');
        $edit = $kelas instanceof KelasKuliah;
        $rombelInput = $this->input('rombel_id');
        $rombelId = $edit ? $kelas->rombel_id
            : (is_scalar($rombelInput) ? (int) $rombelInput : 0);

        $kodeUnik = Rule::unique('kelas_kuliah', 'kode')->where('rombel_id', $rombelId);
        if ($edit) {
            $kodeUnik->ignore($kelas);
        }

        return [
            'rombel_id' => $edit
                ? ['prohibited']
                : ['bail', 'required', 'integer', 'exists:rombel,id'],
            'detail_paket_id' => $edit
                ? ['prohibited']
                : ['bail', 'required', 'integer', 'exists:detail_paket,id'],
            'kode' => $edit && ! $kelas->kodeDapatDiubah()
                ? ['prohibited']
                : ['bail', 'required', 'string', 'max:40', 'regex:/\A[A-Z0-9][A-Z0-9._-]*\z/', $kodeUnik],
            'status' => $edit
                ? ['required', 'string', Rule::in(array_keys(KelasKuliah::STATUS))]
                : ['prohibited'],
            'versi' => $edit
                ? ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/']
                : ['prohibited'],
            'konfirmasi' => ['required', 'accepted'],
            'id' => ['prohibited'],
            'periode_akademik_id' => ['prohibited'],
            'paket_semester_id' => ['prohibited'],
            'mata_kuliah_id' => ['prohibited'],
            'nama_mk_snapshot' => ['prohibited'],
            'sks_snapshot' => ['prohibited'],
            'revisi' => ['prohibited'],
            'diaktifkan_at' => ['prohibited'],
            'diselesaikan_at' => ['prohibited'],
            'diarsipkan_at' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'rombel_id' => 'rombel',
            'detail_paket_id' => 'mata kuliah paket',
            'kode' => 'kode kelas',
            'versi' => 'versi formulir',
            'konfirmasi' => 'konfirmasi data',
        ];
    }

    public function messages(): array
    {
        return [
            'kode.unique' => 'Kode kelas sudah digunakan dalam rombel ini.',
            'kode.regex' => 'Gunakan huruf besar, angka, titik, garis bawah, atau tanda hubung.',
            'konfirmasi.accepted' => 'Centang konfirmasi setelah memeriksa data kelas.',
            'konfirmasi.required' => 'Konfirmasi data diperlukan.',
        ];
    }
}
