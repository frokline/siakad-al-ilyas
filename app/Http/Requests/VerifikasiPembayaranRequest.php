<?php

namespace App\Http\Requests;

use App\Models\Pembayaran;
use App\Services\AksesPembayaran;
use Illuminate\Foundation\Http\FormRequest;

class VerifikasiPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pembayaran = $this->route('pembayaran');
        $user = $this->user();

        return $pembayaran instanceof Pembayaran
            && $user !== null
            && app(AksesPembayaran::class)->petugas($user);
    }

    protected function prepareForValidation(): void
    {
        $catatan = $this->input('catatan');

        $this->merge([
            'versi' => strtolower(trim((string) $this->input('versi'))),
            'catatan' => is_string($catatan) ? trim($catatan) : $catatan,
        ]);
    }

    public function rules(): array
    {
        return [
            'versi' => [
                'required',
                'string',
                'regex:/\A[a-f0-9]{64}\z/',
            ],

            'catatan' => [
                'required',
                'string',
                'min:10',
                'max:2000',
                'not_regex:/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',
            ],

            'konfirmasi' => [
                'required',
                'accepted',
            ],

            // Nilai sensitif ditentukan oleh server, bukan dari form.
            'tindakan' => ['prohibited'],
            'status' => ['prohibited'],
            'status_sebelum' => ['prohibited'],
            'status_sesudah' => ['prohibited'],
            'pembayaran_id' => ['prohibited'],
            'petugas_id' => ['prohibited'],
            'pengunggah_id' => ['prohibited'],
            'tagihan_id' => ['prohibited'],
            'bukti_berkas_id' => ['prohibited'],
            'nominal_diajukan' => ['prohibited'],
            'revisi' => ['prohibited'],
            'revisi_pembayaran' => ['prohibited'],
            'waktu' => ['prohibited'],
            'diajukan_at' => ['prohibited'],
            'dibatalkan_at' => ['prohibited'],
            'tagihan_snapshot' => ['prohibited'],
            'bukti_snapshot' => ['prohibited'],
            'bukti_sha256' => ['prohibited'],
            'tagihan_aktif_id' => ['prohibited'],
            'bukti_aktif_sha256' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'versi.required' => 'Versi pembayaran wajib dikirim.',
            'versi.regex' => 'Versi pembayaran tidak valid. Muat ulang halaman.',

            'catatan.required' => 'Catatan verifikasi wajib diisi.',
            'catatan.min' => 'Catatan verifikasi minimal 10 karakter.',
            'catatan.max' => 'Catatan verifikasi maksimal 2.000 karakter.',
            'catatan.not_regex' => 'Catatan mengandung karakter yang tidak diizinkan.',

            'konfirmasi.required' => 'Konfirmasi tindakan wajib diberikan.',
            'konfirmasi.accepted' => 'Anda harus mengonfirmasi tindakan verifikasi.',

            '*.prohibited' => 'Data yang tidak diizinkan terdeteksi dalam permintaan.',
        ];
    }

    public function versiPembayaran(): string
    {
        return (string) $this->validated('versi');
    }

    public function catatanVerifikasi(): string
    {
        return trim((string) $this->validated('catatan'));
    }
}
