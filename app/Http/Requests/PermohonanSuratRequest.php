<?php

namespace App\Http\Requests;

use App\Models\PermohonanSurat;
use App\Services\AturanPermohonanSurat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PermohonanSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        $p = $this->route('permohonanSurat');
        // Otorisasi tindakan spesifik diperiksa ulang setelah validasi dan di dalam transaksi.
        return $p instanceof PermohonanSurat ? Gate::allows('view', $p) : Gate::allows('create', PermohonanSurat::class);
    }
    public function rules(): array
    {
        return [];
    }
    public function dataSurat(): array
    {
        return AturanPermohonanSurat::data($this->all(), ! ($this->route('permohonanSurat') instanceof PermohonanSurat));
    }
}
