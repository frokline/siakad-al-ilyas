<?php

namespace App\Http\Requests;

use App\Models\RegistrasiSemester;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrasiSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola-registrasi-semester') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach (['riwayat_studi_id', 'rombel_id', 'status', 'alasan_status', 'versi'] as $key) {
            if (is_string($this->input($key))) {
                $value = trim($this->input($key));
                $data[$key] = $value === '' ? null : $value;
            }
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        $registrasi = $this->route('registrasiSemester');
        $edit = $registrasi instanceof RegistrasiSemester;

        return [
            'riwayat_studi_id' => $edit
                ? ['prohibited']
                : ['bail', 'required', 'integer', Rule::exists('riwayat_studi', 'id')->where('status', 'aktif')],
            'rombel_id' => $edit && ! $registrasi->penempatanDapatDiubah()
                ? ['prohibited']
                : ['bail', 'required', 'integer', Rule::exists('rombel', 'id')],
            'status' => $edit
                ? ['required', 'string', Rule::in(array_keys($registrasi->pilihanStatus()))]
                : ['prohibited'],
            'alasan_status' => $edit
                ? [
                    Rule::requiredIf(fn(): bool => in_array(
                        $this->input('status'),
                        [RegistrasiSemester::CUTI, RegistrasiSemester::BATAL],
                        true
                    )),
                    'nullable',
                    'string',
                    'min:10',
                    'max:1000',
                ]
                : ['prohibited'],
            'versi' => $edit
                ? ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/']
                : ['prohibited'],
            'konfirmasi' => ['required', 'accepted'],
            'id' => ['prohibited'],
            'periode_akademik_id' => ['prohibited'],
            'semester_studi' => ['prohibited'],
            'penempatan_dikunci_at' => ['prohibited'],
            'revisi' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'riwayat_studi_id' => 'riwayat studi',
            'rombel_id' => 'rombel',
            'alasan_status' => 'alasan status',
            'konfirmasi' => 'konfirmasi data',
            'versi' => 'versi formulir',
        ];
    }

    public function messages(): array
    {
        return [
            'konfirmasi.accepted' => 'Centang konfirmasi setelah memeriksa data.',
            'konfirmasi.required' => 'Konfirmasi data diperlukan.',
            'alasan_status.required' => 'Tuliskan alasan cuti atau pembatalan.',
        ];
    }
}
