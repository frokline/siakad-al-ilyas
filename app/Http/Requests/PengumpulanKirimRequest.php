<?php

namespace App\Http\Requests;

use App\Models\Pengumpulan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PengumpulanKirimRequest extends FormRequest
{
    public function authorize(): bool
    {
        $p = $this->route('pengumpulan');
        return $p instanceof Pengumpulan && Gate::allows('kirim', $p);
    }
    public function rules(): array
    {
        return [
            'versi_form' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'kunci_kirim' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'konfirmasi' => ['accepted'],
            'jawaban_teks' => ['prohibited'],
            'lampiran_ids' => ['prohibited'],
            'berkas_ids' => ['prohibited'],
            'pemilik_id' => ['prohibited'],
            'detail_krs_id' => ['prohibited'],
            'kegiatan_id' => ['prohibited'],
            'status' => ['prohibited'],
            'dikirim_at' => ['prohibited']
        ];
    }
}
