<?php

namespace App\Http\Requests;

use App\Models\JenisBiaya;
use App\Services\AturanJenisBiaya;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class JenisBiayaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $j = $this->route('jenisBiaya');
        return $j instanceof JenisBiaya ? Gate::allows('update', $j) : Gate::allows('create', JenisBiaya::class);
    }
    protected function prepareForValidation(): void
    {
        $this->merge(AturanJenisBiaya::normalisasi($this->only(['kode', 'nama', 'keterangan', 'alasan'])));
    }
    public function rules(): array
    {
        $baru = ! ($this->route('jenisBiaya') instanceof JenisBiaya);
        return [
            ...AturanJenisBiaya::aturanIsi($baru),
            'form_token' => $baru ? ['required', 'uuid'] : ['prohibited'],
            'versi' => $baru ? ['prohibited'] : ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => $baru ? ['nullable', 'string', 'max:1000'] : ['required', 'string', 'min:10', 'max:1000'],
            'aktif' => ['prohibited'],
            'dinonaktifkan_at' => ['prohibited'],
            'pembuat_id' => ['prohibited'],
            'revisi' => ['prohibited'],
            'nominal' => ['prohibited'],
            'bulan_tagihan' => ['prohibited'],
            'tahun_tagihan' => ['prohibited']
        ];
    }
    public function messages(): array
    {
        return [
            'kode.regex' => 'Kode 2–30 karakter, dimulai huruf, hanya huruf A–Z, angka, tanda - atau _.',
            'nama.not_regex' => 'Nama biaya harus satu baris tanpa karakter kontrol.',
            'kode.prohibited' => 'Kode tidak dapat diubah setelah dibuat.',
            'aktif.prohibited' => 'Ubah status melalui tombol Aktifkan/Nonaktifkan.',
            'nominal.prohibited' => 'Nominal diatur pada Tagihan, bukan Jenis Biaya.'
        ];
    }
}
