<?php

namespace App\Http\Requests;

use App\Models\Pembayaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PembayaranBatalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $p = $this->route('pembayaran');
        return $p instanceof Pembayaran && Gate::allows('cancel', $p);
    }
    public function rules(): array
    {
        return [
            'versi' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000', 'regex:/\S.{8,}\S/s'],
            'konfirmasi' => ['required', 'accepted'],
            'status' => ['prohibited'],
            'bukti_berkas_id' => ['prohibited'],
            'nominal_diajukan' => ['prohibited']
        ];
    }
}
