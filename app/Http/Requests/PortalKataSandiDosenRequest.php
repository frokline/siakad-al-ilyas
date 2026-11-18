<?php

namespace App\Http\Requests;

use App\Services\AksesProfilDosen;
use Illuminate\Foundation\Http\FormRequest;

final class PortalKataSandiDosenRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && app(AksesProfilDosen::class)->masuk($user);
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:current_password',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' =>
                'Kata sandi saat ini wajib diisi.',

            'current_password.string' =>
                'Kata sandi saat ini harus berupa teks.',

            'password.required' =>
                'Kata sandi baru wajib diisi.',

            'password.string' =>
                'Kata sandi baru harus berupa teks.',

            'password.min' =>
                'Kata sandi baru minimal 8 karakter.',

            'password.confirmed' =>
                'Konfirmasi kata sandi baru tidak cocok.',

            'password.different' =>
                'Kata sandi baru harus berbeda dari kata sandi saat ini.',
        ];
    }

    /**
     * Hanya mengembalikan data yang diperlukan
     * untuk proses penggantian kata sandi.
     */
    public function dataAman(): array
    {
        $data = $this->validated();

        return [
            'current_password' =>
                (string) ($data['current_password'] ?? ''),

            'password' =>
                (string) ($data['password'] ?? ''),
        ];
    }
}