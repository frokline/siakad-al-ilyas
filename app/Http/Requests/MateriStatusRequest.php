<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class MateriStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && Gate::allows('akses-materi');
    }
    public function rules(): array
    {
        return [
            'versi' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
            'konfirmasi' => ['accepted']
        ];
    }
}
