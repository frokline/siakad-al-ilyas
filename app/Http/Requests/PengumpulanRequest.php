<?php

namespace App\Http\Requests;

use App\Models\Pengumpulan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PengumpulanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $p = $this->route('pengumpulan');
        return $p instanceof Pengumpulan && Gate::allows('update', $p);
    }
    protected function prepareForValidation(): void
    {
        $raw = $this->input('lampiran_ids', '');
        $this->merge(['berkas_ids' => is_string($raw) ? preg_split('/[\s,]+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY) : null]);
    }
    public function rules(): array
    {
        return [
            'versi_form' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'jawaban_teks' => ['nullable', 'string', 'max:' . max(1, min(10000, (int) config('pengumpulan.maks_karakter_jawaban', 10000)))],
            'lampiran_ids' => ['nullable', 'string', 'max:150'],
            'berkas_ids' => ['present', 'array', 'max:5'],
            'berkas_ids.*' => ['required', 'string', 'regex:/\A[1-9][0-9]{0,17}\z/', 'distinct'],
            'pemilik_id' => ['prohibited'],
            'detail_krs_id' => ['prohibited'],
            'kegiatan_id' => ['prohibited'],
            'status' => ['prohibited'],
            'versi' => ['prohibited'],
            'revisi' => ['prohibited'],
            'kunci_kirim' => ['prohibited'],
            'dikirim_at' => ['prohibited'],
            'hash_jawaban' => ['prohibited']
        ];
    }
}
