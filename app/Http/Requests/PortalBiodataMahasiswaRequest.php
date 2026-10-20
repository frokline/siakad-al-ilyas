<?php

namespace App\Http\Requests;

use App\Models\Mahasiswa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

final class PortalBiodataMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return Mahasiswa::query()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
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

            'keluarga' => ['nullable', 'array'],
            'keluarga.ayah' => ['nullable', 'array'],
            'keluarga.ibu' => ['nullable', 'array'],
            'keluarga.wali' => ['nullable', 'array'],

            'berkas_ijazah_terakhir' => ['nullable', 'file', 'min:1', 'max:' . config('berkas.maks_kib'), 'mimes:pdf,jpg,jpeg,png'],
            'berkas_transkrip' => ['nullable', 'file', 'min:1', 'max:' . config('berkas.maks_kib'), 'mimes:pdf,jpg,jpeg,png'],
            'berkas_ktp' => ['nullable', 'file', 'min:1', 'max:' . config('berkas.maks_kib'), 'mimes:pdf,jpg,jpeg,png'],
            'berkas_kartu_keluarga' => ['nullable', 'file', 'min:1', 'max:' . config('berkas.maks_kib'), 'mimes:pdf,jpg,jpeg,png'],
            'berkas_akta_kelahiran' => ['nullable', 'file', 'min:1', 'max:' . config('berkas.maks_kib'), 'mimes:pdf,jpg,jpeg,png'],
            'berkas_pas_foto_3x4' => ['nullable', 'file', 'min:1', 'max:' . config('berkas.maks_kib'), 'mimes:jpg,jpeg,png'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email_pribadi' => 'email pribadi',
            'nomor_hp' => 'nomor HP',
            'alamat_ktp' => 'alamat KTP',
            'alamat_domisili' => 'alamat domisili',
            'domisili_sama_dengan_ktp' => 'alamat domisili sama dengan KTP',
            'keluarga.ayah.nama_lengkap' => 'nama lengkap ayah',
            'keluarga.ibu.nama_lengkap' => 'nama lengkap ibu',
            'keluarga.wali.nama_lengkap' => 'nama lengkap wali',
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_hp.regex' => 'Nomor HP hanya boleh berisi angka, spasi, tanda tambah, tanda hubung, dan tanda kurung.',
            'email_pribadi.email' => 'Format email pribadi tidak valid.',
            'ipk.max' => 'Nilai IPK maksimal 4.',
        ];
    }
}
