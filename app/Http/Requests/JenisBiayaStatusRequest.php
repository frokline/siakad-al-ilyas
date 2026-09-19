<?php

namespace App\Http\Requests;

use App\Services\AturanJenisBiaya;
use App\Services\AksesKeuangan;
use Illuminate\Foundation\Http\FormRequest;

class JenisBiayaStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(AksesKeuangan::class)->kelola($this->user());
    }
    protected function prepareForValidation(): void
    {
        $this->merge(AturanJenisBiaya::normalisasi($this->only('alasan')));
    }
    public function rules(): array
    {
        return [
            'versi' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
            'konfirmasi' => ['accepted'],
            'kode' => ['prohibited'],
            'nama' => ['prohibited'],
            'keterangan' => ['prohibited'],
            'aktif' => ['prohibited'],
            'dinonaktifkan_at' => ['prohibited'],
            'revisi' => ['prohibited']
        ];
    }
}
