<?php

namespace App\Http\Requests;

use App\Models\Mahasiswa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola-mahasiswa') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $perubahan = [];

        if (is_string($this->input('nim'))) {
            $perubahan['nim'] = Str::upper(
                trim($this->input('nim'))
            );
        }

        foreach (
            [
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'alamat',
            ] as $kolom
        ) {
            $nilai = $this->input($kolom);

            if (is_string($nilai)) {
                $nilai = trim($nilai);

                $perubahan[$kolom] = $nilai === '' ? null : $nilai;
            }
        }

        $this->merge($perubahan);
    }

    public function rules(): array
    {
        $mahasiswa = $this->route('mahasiswa');
        $mengedit = $mahasiswa instanceof Mahasiswa;

        $nimUnik = Rule::unique('mahasiswa', 'nim');

        if ($mengedit) {
            $nimUnik->ignore($mahasiswa);
        }

        $hariIni = now(
            config('siakad.timezone', 'Asia/Makassar')
        )->toDateString();

        return [
            'user_id' => $mengedit
                ? ['prohibited']
                : [
                    'bail',
                    'required',
                    'integer',
                    'min:1',
                    Rule::exists('users', 'id')->where('status', 'aktif'),
                    Rule::unique('mahasiswa', 'user_id'),
                ],

            'nim' => [
                'bail',
                'required',
                'string',
                'max:40',
                'regex:/\A[A-Z0-9][A-Z0-9._-]*\z/',
                $nimUnik,
            ],

            'tempat_lahir' => [
                'nullable',
                'string',
                'max:100',
            ],

            'tanggal_lahir' => [
                'bail',
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:1900-01-01',
                'before_or_equal:' . $hariIni,
            ],

            'jenis_kelamin' => [
                'nullable',
                'string',
                Rule::in(array_keys(Mahasiswa::JENIS_KELAMIN)),
            ],

            'alamat' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'versi' => $mengedit
                ? ['bail', 'required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/']
                : ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'akun pengguna',
            'nim' => 'NIM',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'alamat' => 'alamat',
            'versi' => 'versi formulir',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute tidak valid.',
            'min.numeric' => ':attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'in' => 'Pilihan :attribute tidak valid.',

            'user_id.exists' => 'Akun tidak ditemukan atau sudah nonaktif.',
            'user_id.unique' => 'Akun ini sudah memiliki data mahasiswa.',
            'user_id.prohibited' => 'Akun pemilik mahasiswa tidak dapat diganti.',

            'nim.unique' => 'NIM sudah digunakan mahasiswa lain.',
            'nim.regex' => 'NIM hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',

            'tanggal_lahir.date_format' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'tanggal_lahir.after_or_equal' => 'Tanggal lahir paling awal 1 Januari 1900.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melewati hari ini.',

            'versi.required' => 'Formulir tidak lengkap. Buka ulang halaman edit.',
            'versi.string' => 'Formulir tidak valid. Buka ulang halaman edit.',
            'versi.size' => 'Formulir tidak valid. Buka ulang halaman edit.',
            'versi.regex' => 'Formulir tidak valid. Buka ulang halaman edit.',
            'versi.prohibited' => 'Formulir penambahan mahasiswa tidak valid.',
        ];
    }
}
