<?php

namespace App\Http\Requests;

use App\Services\AturanJenisSurat;
use App\Services\AksesJenisSurat;
use Illuminate\Foundation\Http\FormRequest;

class JenisSuratStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(AksesJenisSurat::class)->kelola($this->user());
    }
    protected function prepareForValidation(): void
    {
        $this->merge(AturanJenisSurat::normalisasi($this->only('alasan')));
    }
    public function rules(): array
    {
        return [
            'versi' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
            'konfirmasi' => ['required', 'accepted'],
            'kode' => ['prohibited'],
            'nama' => ['prohibited'],
            'syarat' => ['prohibited'],
            'pembuat_id' => ['prohibited'],
            'form_token' => ['prohibited'],
            'hash_permohonan' => ['prohibited'],
            'aktif' => ['prohibited'],
            'dinonaktifkan_at' => ['prohibited'],
            'revisi' => ['prohibited']
        ];
    }
}
