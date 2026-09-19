<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

final class AturanTagihan
{
    public static function isi(array $data, bool $baru): array
    {
        $rules = [
            'nominal' => ['required', 'string', 'max:15'],
            'jatuh_tempo' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2199-12-31'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'registrasi_semester_id' => $baru ? ['required', 'integer', 'min:1'] : ['prohibited'],
            'jenis_biaya_id' => $baru ? ['required', 'integer', 'min:1'] : ['prohibited'],
            'tahun_tagihan' => $baru ? ['required', 'integer', 'between:2000,2199'] : ['prohibited'],
            'bulan_tagihan' => $baru ? ['required', 'integer', 'between:1,12'] : ['prohibited']
        ];
        foreach (['mahasiswa_id', 'nomor', 'status', 'snapshot', 'pembuat_id', 'revisi', 'diterbitkan_at', 'dibatalkan_at', 'hash_permohonan'] as $key) {
            $rules[$key] = ['prohibited'];
        }
        $v = Validator::make($data, $rules)->validate();
        $v = \Illuminate\Support\Arr::only($v, $baru
            ? ['registrasi_semester_id', 'jenis_biaya_id', 'tahun_tagihan', 'bulan_tagihan', 'nominal', 'jatuh_tempo', 'catatan']
            : ['nominal', 'jatuh_tempo', 'catatan']);
        $v['nominal'] = UangTagihan::normal($v['nominal']);
        $v['catatan'] = isset($v['catatan']) ? trim($v['catatan']) : null;
        if ($v['catatan'] === '') {
            $v['catatan'] = null;
        }
        if ($baru) {
            foreach (['registrasi_semester_id', 'jenis_biaya_id', 'tahun_tagihan', 'bulan_tagihan'] as $key) {
                $v[$key] = (int) $v[$key];
            }
        }
        return $v;
    }
    public static function kontrol(array $data, bool $baru): array
    {
        return Validator::make($data, [
            'form_token' => $baru ? ['required', 'uuid'] : ['prohibited'],
            'versi' => $baru ? ['prohibited'] : ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000', 'regex:/\S.{8,}\S/s']
        ])->validate();
    }
}
