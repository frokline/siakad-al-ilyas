<?php

namespace App\Http\Requests;

use App\Services\AksesNotifikasi;
use Illuminate\Foundation\Http\FormRequest;

class NotifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && app(AksesNotifikasi::class)->masuk($this->user());
    }
    public function rules(): array
    {
        return $this->routeIs('notifikasi.baca-halaman')
            ? ['ids' => ['required', 'array', 'min:1', 'max:20'], 'ids.*' => ['required', 'integer', 'min:1', 'distinct']]
            : ['penerima_id' => ['prohibited'], 'sumber_id' => ['prohibited'], 'url' => ['prohibited']];
    }
}
