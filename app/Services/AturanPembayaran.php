<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

final class AturanPembayaran
{
    public static function rules(): array
    {
        $r = [
            'form_token' => ['required', 'uuid'],
            'versi_tagihan' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'versi_tujuan' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'bukti_berkas_id' => ['required', 'integer', 'min:1'],
            'nominal_diajukan' => ['required', 'string', 'max:15'],
            'tanggal_transfer' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:' . now('Asia/Makassar')->format('Y-m-d')],
            'referensi_bank' => ['nullable', 'string', 'max:100', 'not_regex:/[\x00-\x1F\x7F]/'],
            'konfirmasi' => ['required', 'accepted']
        ];
        foreach (
            [
                'tagihan_id',
                'pengunggah_id',
                'status',
                'nomor_pengajuan',
                'tujuan_transfer',
                'tagihan_snapshot',
                'bukti_snapshot',
                'bukti_sha256',
                'hash_permohonan',
                'tagihan_aktif_id',
                'bukti_aktif_sha256',
                'revisi',
                'diajukan_at'
            ] as $key
        ) {
            $r[$key] = ['prohibited'];
        }
        return $r;
    }
    public static function data(array $data): array
    {
        $v = Validator::make($data, self::rules())->validate();
        $v = Arr::only($v, ['form_token', 'versi_tagihan', 'versi_tujuan', 'bukti_berkas_id', 'nominal_diajukan', 'tanggal_transfer', 'referensi_bank']);
        try {
            $v['nominal_diajukan'] = UangTagihan::normal($v['nominal_diajukan']);
        } catch (\Illuminate\Validation\ValidationException) {
            throw \Illuminate\Validation\ValidationException::withMessages(['nominal_diajukan' => 'Isi nominal positif tanpa pemisah ribuan, maksimal dua desimal.']);
        }
        $v['bukti_berkas_id'] = (int) $v['bukti_berkas_id'];
        $v['referensi_bank'] = isset($v['referensi_bank']) ? trim($v['referensi_bank']) : null;
        if ($v['referensi_bank'] === '') {
            $v['referensi_bank'] = null;
        }
        return $v;
    }
}
