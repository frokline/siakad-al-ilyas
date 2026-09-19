<?php

namespace App\Http\Requests;

use App\Models\Pengumuman;
use App\Services\AturanPengumuman;
use Illuminate\Foundation\Http\FormRequest;

class PengumumanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $p = $this->route('pengumuman');
        return $p instanceof Pengumuman ? $this->user()->can('update', $p) : $this->user()->can('create', Pengumuman::class);
    }
    public function rules(): array
    {
        return [];
    }
    // Validasi terpusat juga dipanggil action untuk penulisan non-HTTP.
    protected function passedValidation(): void
    {
        AturanPengumuman::isi($this->all(), ! ($this->route('pengumuman') instanceof Pengumuman));
    }
}
