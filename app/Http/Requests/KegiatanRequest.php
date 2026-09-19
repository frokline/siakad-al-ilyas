<?php

namespace App\Http\Requests;

use App\Models\Kegiatan;
use App\Services\WaktuKegiatan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class KegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && Gate::allows('akses-kegiatan');
    }
    protected function prepareForValidation(): void
    {
        $raw = $this->input('lampiran_ids', '');
        $this->merge(['berkas_ids' => is_string($raw) ? preg_split('/[\s,]+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY) : null]);
        foreach (['judul', 'instruksi', 'alasan'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }
    public function rules(): array
    {
        $edit = $this->route('kegiatan') instanceof Kegiatan;
        return [
            'kelas_kuliah_id' => $edit ? ['prohibited'] : ['required', 'integer', 'min:1'],
            'form_token' => $edit ? ['prohibited'] : ['required', 'uuid'],
            'versi' => $edit ? ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/'] : ['prohibited'],
            'pertemuan_id' => ['nullable', 'integer', 'min:1'],
            'jenis' => ['required', Rule::in(array_keys(Kegiatan::JENIS))],
            'judul' => ['required', 'string', 'max:200'],
            'instruksi' => ['required', 'string', 'min:10', 'max:10000'],
            'buka_lokal' => ['required', 'string', 'size:16'],
            'tenggat_lokal' => ['required', 'string', 'size:16'],
            'maks_mb' => ['required', 'integer', 'min:1', 'max:' . min(20, (int) config('kegiatan.batas_mb_jawaban', 20))],
            'maks_berkas' => ['required', 'integer', 'min:1', 'max:' . min(5, (int) config('kegiatan.batas_berkas_jawaban', 5))],
            'ekstensi_diizinkan' => ['required', 'array', 'min:1', 'max:3'],
            'ekstensi_diizinkan.*' => ['required', 'string', 'distinct', Rule::in(config('kegiatan.ekstensi_jawaban', ['pdf', 'jpg', 'png']))],
            'lampiran_ids' => ['nullable', 'string', 'max:250'],
            'berkas_ids' => ['present', 'array', 'max:' . (int) config('kegiatan.maks_lampiran_instruksi', 10)],
            'berkas_ids.*' => ['required', 'string', 'regex:/\A[1-9][0-9]{0,17}\z/', 'distinct'],
            'alasan' => $edit ? ['required', 'string', 'min:10', 'max:1000'] : ['nullable', 'string', 'max:1000'],
            'pembuat_id' => ['prohibited'],
            'metode' => ['prohibited'],
            'status' => ['prohibited'],
            'revisi' => ['prohibited'],
            'terbit_at' => ['prohibited'],
            'buka_at' => ['prohibited'],
            'tenggat_at' => ['prohibited'],
            'ditutup_at' => ['prohibited'],
            'diarsipkan_at' => ['prohibited'],
            'maks_ukuran_byte' => ['prohibited'],
        ];
    }
    public function after(): array
    {
        return [function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            try {
                $buka = WaktuKegiatan::dariForm($this->input('buka_lokal'), 'buka_lokal');
                $tenggat = WaktuKegiatan::dariForm($this->input('tenggat_lokal'), 'tenggat_lokal');
                if (! $buka->lt($tenggat)) {
                    $v->errors()->add('tenggat_lokal', 'Tenggat harus lebih akhir daripada waktu mulai.');
                }
            } catch (ValidationException $e) {
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $m) {
                        $v->errors()->add($field, $m);
                    }
                }
            }
        }];
    }
}
