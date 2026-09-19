<?php

namespace App\Http\Requests;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\AturanPembayaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        $t = $this->route('tagihan');
        return $t instanceof Tagihan && Gate::allows('create', [Pembayaran::class, $t]);
    }
    public function rules(): array
    {
        return AturanPembayaran::rules();
    }
}
