<?php

namespace App\Http\Requests;

use App\Models\Kegiatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class KegiatanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $kegiatan = $this->route('kegiatan');

        return $this->user() !== null
            && $kegiatan instanceof Kegiatan
            && Gate::allows('manage', $kegiatan);
    }

    protected function prepareForValidation(): void
    {
        $alasan = $this->input('alasan');

        if (is_string($alasan)) {
            $alasan = trim($alasan);
        }

        $this->merge([
            'alasan' => $alasan === '' ? null : $alasan,
        ]);
    }

    public function rules(): array
    {
        $perpanjang = $this->routeIs(
            'kegiatan.perpanjang'
        );

        return [
            'versi' => [
                'required',
                'string',
                'size:64',
                'regex:/\A[a-f0-9]{64}\z/',
            ],

            'alasan' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'konfirmasi' => [
                'required',
                'accepted',
            ],

            'tenggat_baru' => $perpanjang
                ? [
                    'required',
                    'string',
                    'size:16',
                ]
                : [
                    'prohibited',
                ],

            'status' => [
                'prohibited',
            ],

            'terbit_at' => [
                'prohibited',
            ],

            'ditutup_at' => [
                'prohibited',
            ],

            'diarsipkan_at' => [
                'prohibited',
            ],

            'revisi' => [
                'prohibited',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'versi.required' =>
                'Versi kegiatan tidak ditemukan. Muat ulang halaman.',

            'versi.regex' =>
                'Versi kegiatan tidak valid. Muat ulang halaman.',

            'konfirmasi.required' =>
                'Centang konfirmasi sebelum menjalankan tindakan.',

            'konfirmasi.accepted' =>
                'Konfirmasi tindakan belum dicentang.',

            'tenggat_baru.required' =>
                'Batas pengumpulan yang baru wajib diisi.',

            'tenggat_baru.size' =>
                'Format batas pengumpulan tidak valid.',
        ];
    }
}