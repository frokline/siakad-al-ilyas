<?php

namespace App\Http\Requests;

use App\Models\Pertemuan;
use App\Models\Presensi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PresensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sesi = $this->route('pertemuan');
        return $sesi instanceof Pertemuan && ($this->user()?->can('lihat-presensi', $sesi) ?? false);
    }

    protected function prepareForValidation(): void
    {
        foreach (['catatan', 'alasan'] as $field) {
            if (is_string($this->input($field))) {
                $nilai = trim($this->input($field));
                $this->merge([$field => $nilai === '' ? null : $nilai]);
            }
        }
    }

    public function rules(): array
    {
        $versi = ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/'];
        return match ($this->route()->getName()) {
            'presensi.siapkan' => ['versi_pertemuan' => $versi, 'konfirmasi' => ['accepted']],
            'presensi.tutup' => ['versi_daftar' => $versi, 'konfirmasi' => ['accepted']],
            'presensi.catat', 'presensi.koreksi' => [
                'versi_presensi' => $versi,
                'status' => ['required', 'string', Rule::in(array_keys(Presensi::PILIHAN))],
                'halaman' => ['nullable', 'integer', 'min:1', 'max:100000'],
                'filter_status' => ['nullable', 'string', Rule::in(array_keys(Presensi::STATUS))],
                'catatan' => ['nullable', 'string', 'max:1000'],
                'alasan' => [$this->routeIs('presensi.koreksi') ? 'required' : 'nullable', 'string', 'min:10', 'max:2000'],
            ],
            default => [],
        };
    }
}
