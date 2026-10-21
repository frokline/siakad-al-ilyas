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
                'konsentrasi',
                'sistem_kuliah',
                'jenis_pendaftaran',
                'jalur_pendaftaran',
                'gelombang',
                'tanggal_awal_masuk',
                'tanggal_daftar_ulang',
                'kebutuhan_khusus',
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

        $aturanBerkas = [
            'nullable',
            'file',
            'min:1',
            'max:' . config('berkas.maks_kib'),
            'mimes:pdf,jpg,jpeg,png',
        ];

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

            'konsentrasi' => [
                'nullable',
                'string',
                'max:150',
            ],

            'sistem_kuliah' => [
                'nullable',
                'string',
                'max:100',
            ],

            'jenis_pendaftaran' => [
                'nullable',
                'string',
                'max:100',
            ],

            'jalur_pendaftaran' => [
                'nullable',
                'string',
                'max:100',
            ],

            'gelombang' => [
                'nullable',
                'string',
                'max:50',
            ],

            'tanggal_awal_masuk' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'tanggal_daftar_ulang' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'kebutuhan_khusus' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'agama' => ['nullable', 'string', 'max:30'],
            'suku' => ['nullable', 'string', 'max:100'],
            'kewarganegaraan' => ['nullable', 'string', 'max:60'],
            'nik' => ['nullable', 'string', 'max:32'],
            'nomor_kk' => ['nullable', 'string', 'max:32'],
            'npwp' => ['nullable', 'string', 'max:32'],
            'nomor_kps' => ['nullable', 'string', 'max:32'],
            'golongan_darah' => ['nullable', 'string', 'max:3'],
            'status_nikah' => ['nullable', 'string', 'max:30'],
            'ukuran_jas_almamater' => ['nullable', 'string', 'max:10'],
            'nomor_hp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'email_pribadi' => ['nullable', 'email', 'max:190'],
            'pekerjaan' => ['nullable', 'string', 'max:100'],
            'instansi_pekerjaan' => ['nullable', 'string', 'max:190'],
            'nomor_rekening' => ['nullable', 'string', 'max:50'],
            'nama_rekening' => ['nullable', 'string', 'max:150'],
            'nama_bank' => ['nullable', 'string', 'max:100'],
            'berat_badan' => ['nullable', 'integer', 'min:0', 'max:250'],
            'tinggi_badan' => ['nullable', 'integer', 'min:0', 'max:300'],
            'penghasilan' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'transportasi' => ['nullable', 'string', 'max:50'],

            'alamat_ktp' => ['nullable', 'string'],
            'rt_ktp' => ['nullable', 'string', 'max:5'],
            'rw_ktp' => ['nullable', 'string', 'max:5'],
            'dusun_ktp' => ['nullable', 'string', 'max:100'],
            'desa_kelurahan_ktp' => ['nullable', 'string', 'max:100'],
            'provinsi_ktp' => ['nullable', 'string', 'max:100'],
            'kota_kabupaten_ktp' => ['nullable', 'string', 'max:100'],
            'kecamatan_ktp' => ['nullable', 'string', 'max:100'],
            'kode_pos_ktp' => ['nullable', 'string', 'max:10'],
            'domisili_sama_dengan_ktp' => ['nullable', 'boolean'],
            'alamat_domisili' => ['nullable', 'string'],
            'rt_domisili' => ['nullable', 'string', 'max:5'],
            'rw_domisili' => ['nullable', 'string', 'max:5'],
            'dusun_domisili' => ['nullable', 'string', 'max:100'],
            'desa_kelurahan_domisili' => ['nullable', 'string', 'max:100'],
            'provinsi_domisili' => ['nullable', 'string', 'max:100'],
            'kota_kabupaten_domisili' => ['nullable', 'string', 'max:100'],
            'kecamatan_domisili' => ['nullable', 'string', 'max:100'],
            'kode_pos_domisili' => ['nullable', 'string', 'max:10'],
            'status_tinggal' => ['nullable', 'string', 'max:50'],

            'jenis_pendidikan_asal' => ['nullable', 'string', 'max:100'],
            'perguruan_tinggi' => ['nullable', 'string', 'max:190'],
            'program_studi_asal' => ['nullable', 'string', 'max:190'],
            'ipk' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'sks_diakui' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'nomor_ijazah' => ['nullable', 'string', 'max:100'],
            'nisn' => ['nullable', 'string', 'max:20'],

            'keluarga' => ['nullable', 'array:ayah,ibu,wali'],

            'keluarga.ayah' => ['nullable', 'array'],
            'keluarga.ibu' => ['nullable', 'array'],
            'keluarga.wali' => ['nullable', 'array'],
            'keluarga.*.nama_lengkap' => ['nullable', 'string', 'max:150'],
            'keluarga.*.nik' => ['nullable', 'string', 'max:32'],
            'keluarga.*.tanggal_lahir' => ['nullable', 'date_format:Y-m-d'],
            'keluarga.*.status_hidup' => ['nullable', 'string', 'max:30'],
            'keluarga.*.status_kekerabatan' => ['nullable', 'string', 'max:60'],
            'keluarga.*.pendidikan_terakhir' => ['nullable', 'string', 'max:100'],
            'keluarga.*.pekerjaan' => ['nullable', 'string', 'max:100'],
            'keluarga.*.penghasilan' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'keluarga.*.alamat' => ['nullable', 'string', 'max:2000'],
            'keluarga.*.nomor_telepon' => ['nullable', 'string', 'max:30'],
            'keluarga.*.email' => ['nullable', 'email', 'max:190'],

            'berkas_ijazah_terakhir' => $aturanBerkas,
            'berkas_transkrip' => $aturanBerkas,
            'berkas_ktp' => $aturanBerkas,
            'berkas_kartu_keluarga' => $aturanBerkas,
            'berkas_akta_kelahiran' => $aturanBerkas,
            'berkas_pas_foto_3x4' => [
                'nullable',
                'file',
                'min:1',
                'max:' . config('berkas.maks_kib'),
                'mimes:jpg,jpeg,png',
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
            'konsentrasi' => 'konsentrasi',
            'sistem_kuliah' => 'sistem kuliah',
            'jenis_pendaftaran' => 'jenis pendaftaran',
            'jalur_pendaftaran' => 'jalur pendaftaran',
            'gelombang' => 'gelombang',
            'tanggal_awal_masuk' => 'tanggal awal masuk',
            'tanggal_daftar_ulang' => 'tanggal daftar ulang',
            'kebutuhan_khusus' => 'kebutuhan khusus',
            'email_pribadi' => 'email pribadi',
            'nomor_hp' => 'nomor HP',
            'alamat_ktp' => 'alamat KTP',
            'alamat_domisili' => 'alamat domisili',
            'domisili_sama_dengan_ktp' => 'alamat domisili sama dengan KTP',
            'keluarga.ayah.nama_lengkap' => 'nama lengkap ayah',
            'keluarga.ibu.nama_lengkap' => 'nama lengkap ibu',
            'keluarga.wali.nama_lengkap' => 'nama lengkap wali',
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

            'nomor_hp.regex' => 'Nomor HP hanya boleh berisi angka, spasi, tanda tambah, tanda hubung, dan tanda kurung.',
            'email_pribadi.email' => 'Format email pribadi tidak valid.',
            'ipk.max' => 'Nilai IPK maksimal 4.',

            'versi.required' => 'Formulir tidak lengkap. Buka ulang halaman edit.',
            'versi.string' => 'Formulir tidak valid. Buka ulang halaman edit.',
            'versi.size' => 'Formulir tidak valid. Buka ulang halaman edit.',
            'versi.regex' => 'Formulir tidak valid. Buka ulang halaman edit.',
            'versi.prohibited' => 'Formulir penambahan mahasiswa tidak valid.',
        ];
    }
}
