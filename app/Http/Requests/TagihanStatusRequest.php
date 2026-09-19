<?php

namespace App\Http\Requests;

use App\Services\AksesTagihan;
use Illuminate\Foundation\Http\FormRequest;

class TagihanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && app(AksesTagihan::class)->petugas($this->user());
    }
    public function rules(): array
    {
        return [
            'versi' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000', 'regex:/\S.{8,}\S/s'],
            'konfirmasi' => ['required', 'accepted'],
            'status' => ['prohibited'],
            'nominal' => ['prohibited'],
            'mahasiswa_id' => ['prohibited'],
            'snapshot' => ['prohibited']
        ];
    }
}
