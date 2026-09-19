<?php

namespace App\Http\Requests;

use App\Models\KalenderAkademik;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class KalenderAkademikRequest extends FormRequest
{
    public function authorize(): bool
    {
        $k = $this->route('kalenderAkademik');
        return $k instanceof KalenderAkademik ? Gate::allows('update', $k) : Gate::allows('create', KalenderAkademik::class);
    }
    // Layanan memvalidasi data asli agar datetime-local hanya dikonversi ke UTC satu kali.
    public function rules(): array
    {
        return [];
    }
}
