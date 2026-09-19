<?php

namespace App\Http\Requests;

use App\Models\Pengumuman;
use App\Services\AturanPengumuman;
use Illuminate\Foundation\Http\FormRequest;

class PengumumanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $p = $this->route('pengumuman');
        return $p instanceof Pengumuman && $this->user()->can('audit', $p);
    }
    public function rules(): array
    {
        return [];
    }
    protected function passedValidation(): void
    {
        AturanPengumuman::tindakan($this->all());
    }
}
