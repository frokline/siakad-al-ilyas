<?php

namespace App\Http\Requests;

use App\Services\AksesProfilMahasiswa;
use Illuminate\Foundation\Http\FormRequest;

final class PortalProfilMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && app(AksesProfilMahasiswa::class)->masuk($user);
    }

    protected function prepareForValidation(): void
    {
        $telepon = $this->input('telepon');
        $alamat = $this->input('alamat');

        $this->merge([
            'telepon' => is_string($telepon)
                ? trim($telepon)
                : $telepon,

            'alamat' => is_string($alamat)
                ? trim($alamat)
                : $alamat,
        ]);
    }

    public function rules(): array
    {
        return [
            'telepon' => [
                'nullable',
                'string',
                'max:25',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'alamat' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'telepon.string' =>
                'Nomor telepon harus berupa teks.',

            'telepon.max' =>
                'Nomor telepon maksimal 25 karakter.',

            'telepon.regex' =>
                'Nomor telepon hanya boleh berisi angka, spasi, tanda tambah, tanda hubung, dan tanda kurung.',

            'alamat.string' =>
                'Alamat harus berupa teks.',

            'alamat.max' =>
                'Alamat maksimal 1.000 karakter.',
        ];
    }

    /**
     * Hanya mengembalikan kolom yang boleh diubah mahasiswa.
     */
    public function dataAman(): array
    {
        $data = $this->validated();

        return [
            'telepon' => $this->kosongMenjadiNull(
                $data['telepon'] ?? null
            ),
            'alamat' => $this->kosongMenjadiNull(
                $data['alamat'] ?? null
            ),
        ];
    }

    private function kosongMenjadiNull(mixed $nilai): ?string
    {
        if (! is_string($nilai)) {
            return null;
        }

        $nilai = trim($nilai);

        return $nilai === '' ? null : $nilai;
    }
}