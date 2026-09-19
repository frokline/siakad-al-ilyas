<?php

namespace App\Http\Requests;

use App\Models\JenisSurat;
use App\Services\AturanJenisSurat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class JenisSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        $j = $this->route('jenisSurat');
        return $j instanceof JenisSurat ? Gate::allows('update', $j) : Gate::allows('create', JenisSurat::class);
    }
    protected function prepareForValidation(): void
    {
        $this->merge(AturanJenisSurat::normalisasi($this->only(['kode', 'nama', 'syarat', 'alasan'])));
    }
    public function rules(): array
    {
        $baru = ! ($this->route('jenisSurat') instanceof JenisSurat);
        return [
            ...AturanJenisSurat::aturanIsi($baru),
            'form_token' => $baru ? ['required', 'uuid'] : ['prohibited'],
            'versi' => $baru ? ['prohibited'] : ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => $baru ? ['nullable', 'string', 'max:1000'] : ['required', 'string', 'min:10', 'max:1000'],
            'aktif' => ['prohibited'],
            'dinonaktifkan_at' => ['prohibited'],
            'pembuat_id' => ['prohibited'],
            'revisi' => ['prohibited'],
            'hash_permohonan' => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited']
        ];
    }
    public function messages(): array
    {
        return [
            'kode.regex' => 'Kode 2–30 karakter, dimulai huruf, hanya huruf A–Z, angka, tanda - atau _.',
            'nama.not_regex' => 'Nama surat harus satu baris tanpa karakter kontrol.',
            'kode.prohibited' => 'Kode tidak dapat diubah setelah dibuat.',
            'aktif.prohibited' => 'Ubah status melalui tombol Aktifkan/Nonaktifkan.'
        ];
    }
}
