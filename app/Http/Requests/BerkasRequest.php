<?php

namespace App\Http\Requests;

use App\Models\Berkas;
use Illuminate\Foundation\Http\FormRequest;

class BerkasRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->routeIs('berkas.store')) {
            return $this->user()?->can('create', Berkas::class) ?? false;
        }
        $file = $this->route('berkas');
        return $file instanceof Berkas && ($this->user()?->can('view', $file) ?? false);
    }
    protected function prepareForValidation(): void
    {
        foreach (['label', 'keterangan', 'alasan'] as $key) {
            if (is_string($this->input($key))) {
                $value = trim($this->input($key));
                $this->merge([$key => $value === '' ? null : $value]);
            }
        }
    }
    public function rules(): array
    {
        $metadata = ['label' => ['required', 'string', 'max:200'], 'keterangan' => ['nullable', 'string', 'max:2000']];
        if ($this->routeIs('berkas.store')) {
            return $metadata + [
                'upload_token' => ['required', 'uuid'],
                'file' => ['required', 'file', 'min:1', 'max:' . config('berkas.maks_kib'), 'mimes:pdf,jpg,jpeg,png']
            ];
        }
        $rules = [
            'versi' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:2000']
        ];
        return $this->routeIs('berkas.update') ? $rules + $metadata : $rules + ['konfirmasi' => ['accepted']];
    }
}
