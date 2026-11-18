<?php

namespace App\Http\Requests;

use App\Services\AksesProfilDosen;
use Illuminate\Foundation\Http\FormRequest;

final class PortalFotoProfilDosenRequest extends FormRequest
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
            'foto' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'foto.required' =>
                'Silakan pilih foto terlebih dahulu.',

            'foto.image' =>
                'File harus berupa gambar yang valid.',

            'foto.mimes' =>
                'Foto harus berformat JPG, JPEG, PNG, atau WebP.',

            'foto.max' =>
                'Ukuran foto maksimal 2 MB.',
        ];
    }
}