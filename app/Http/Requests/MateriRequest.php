<?php

namespace App\Http\Requests;

use App\Models\Materi;
use App\Rules\TautanMateri;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class MateriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && Gate::allows('akses-materi');
    }
    protected function prepareForValidation(): void
    {
        // Jangan menerima array berkas_ids tersembunyi dari klien; sumbernya hanya kolom teks ini.
        $raw = $this->input('lampiran_ids', '');
        $ids = is_string($raw) ? preg_split('/[\s,]+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY) : null;
        $this->merge(['berkas_ids' => $ids]);
        foreach (['judul', 'isi', 'tautan_eksternal', 'alasan'] as $key) {
            if (is_string($this->input($key))) {
                $value = trim($this->input($key));
                $this->merge([$key => $value === '' ? null : $value]);
            }
        }
    }
    public function rules(): array
    {
        $edit = $this->route('materi') instanceof Materi;
        return [
            'kelas_kuliah_id' => $edit ? ['prohibited'] : ['required', 'integer', 'min:1'],
            'form_token' => $edit ? ['prohibited'] : ['required', 'uuid'],
            'versi' => $edit ? ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/'] : ['prohibited'],
            'pertemuan_id' => ['nullable', 'integer', 'min:1'],
            'judul' => ['required', 'string', 'max:200'],
            'isi' => ['nullable', 'string', 'max:10000'],
            'tautan_eksternal' => ['nullable', 'string', 'max:2000', new TautanMateri()],
            'lampiran_ids' => ['nullable', 'string', 'max:250'],
            'berkas_ids' => ['present', 'array', 'max:' . (int) config('materi.maks_lampiran', 10)],
            'berkas_ids.*' => ['required', 'string', 'regex:/\A[1-9][0-9]{0,17}\z/', 'distinct'],
            'alasan' => $edit ? ['required', 'string', 'min:10', 'max:1000'] : ['nullable', 'string', 'max:1000'],
            'pembuat_id' => ['prohibited'],
            'status' => ['prohibited'],
            'revisi' => ['prohibited'],
            'terbit_at' => ['prohibited'],
            'diarsipkan_at' => ['prohibited'],
        ];
    }
}
