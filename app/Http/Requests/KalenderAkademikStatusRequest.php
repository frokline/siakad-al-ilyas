<?php

namespace App\Http\Requests;

use App\Services\AksesKalender;
use Illuminate\Foundation\Http\FormRequest;

class KalenderAkademikStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(AksesKalender::class)->kelola($this->user());
    }
    public function rules(): array
    {
        return [];
    }
}
