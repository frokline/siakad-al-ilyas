<?php

namespace App\Http\Requests;

use App\Models\Tagihan;
use App\Services\AturanTagihan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class TagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $t = $this->route('tagihan');
        return $t instanceof Tagihan ? Gate::allows('update', $t) : Gate::allows('create', Tagihan::class);
    }
    // Aturan bisnis dipakai sama oleh HTTP dan layanan, tidak ada dua versi validasi.
    public function rules(): array
    {
        return [];
    }
    public function dataTagihan(): array
    {
        $baru = ! ($this->route('tagihan') instanceof Tagihan);
        return array_merge(AturanTagihan::isi($this->all(), $baru), AturanTagihan::kontrol($this->all(), $baru));
    }
}
