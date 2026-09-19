<?php

namespace App\Http\Requests;

use App\Services\AksesPengumpulan;
use Illuminate\Foundation\Http\FormRequest;

class PengumpulanDrafRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(AksesPengumpulan::class)->mahasiswa($this->user());
    }
    public function rules(): array
    {
        return [
            'token_draf' => ['required', 'uuid'],
            'dasar_versi' => ['required', 'integer', 'min:0', 'max:4294967294'],
            'pemilik_id' => ['prohibited'],
            'detail_krs_id' => ['prohibited'],
            'kegiatan_id' => ['prohibited'],
            'status' => ['prohibited'],
            'versi' => ['prohibited'],
            'kunci_kirim' => ['prohibited']
        ];
    }
}
