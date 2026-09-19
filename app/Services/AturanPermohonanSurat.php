<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class AturanPermohonanSurat
{
    public static function data(array $data, bool $baru): array
    {
        foreach (['keperluan', 'catatan', 'nomor_surat'] as $key) {
            if (is_string($data[$key] ?? null)) {
                $data[$key] = trim(str_replace(["\r\n", "\r"], "\n", $data[$key]));
            }
        }
        if (is_string($data['nomor_surat'] ?? null)) {
            $data['nomor_surat'] = strtoupper($data['nomor_surat']);
        }
        $teks = 'not_regex:/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/';
        $aturan = $baru ? [
            'registrasi_semester_id' => ['required', 'integer', 'min:1'],
            'jenis_surat_id' => ['required', 'integer', 'min:1'],
            'versi_jenis' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'form_token' => ['required', 'uuid'],
            'keperluan' => ['required', 'string', 'min:10', 'max:2000', $teks],
            'lampiran_berkas_id' => ['nullable', 'integer', 'min:1'],
        ] : [
            'tujuan' => ['required', Rule::in(['diproses', 'ditolak', 'terbit', 'dibatalkan'])],
            'versi' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'catatan' => ['required', 'string', 'min:10', 'max:1000', $teks],
            'nomor_surat' => ['exclude_unless:tujuan,terbit', 'required', 'string', 'regex:/\A[A-Z0-9][A-Z0-9\/_.-]{1,99}\z/'],
            'hasil_berkas_id' => ['exclude_unless:tujuan,terbit', 'required', 'integer', 'min:1'],
        ];
        $aturan['konfirmasi'] = ['required', 'accepted'];
        $server = [
            'pemohon_id',
            'mahasiswa_id',
            'status',
            'revisi',
            'slot_aktif',
            'nomor_pengajuan',
            'akademik_snapshot',
            'jenis_snapshot',
            'lampiran_snapshot',
            'hasil_snapshot',
            'hash_permohonan',
            'diajukan_at',
            'terbit_at'
        ];
        foreach ($server as $key) {
            $aturan[$key] = ['prohibited'];
        }
        foreach (
            $baru ? ['tujuan', 'versi', 'catatan', 'hasil_berkas_id', 'nomor_surat']
                : ['registrasi_semester_id', 'jenis_surat_id', 'keperluan', 'lampiran_berkas_id', 'versi_jenis', 'form_token'] as $key
        ) {
            $aturan[$key] = ['prohibited'];
        }
        $v = Arr::except(Validator::make($data, $aturan)->validate(), $server);
        unset($v['konfirmasi']);
        foreach (['registrasi_semester_id', 'jenis_surat_id', 'lampiran_berkas_id', 'hasil_berkas_id'] as $key) {
            if (isset($v[$key])) {
                $v[$key] = (int) $v[$key];
            }
        }
        if ($baru) {
            $v['lampiran_berkas_id'] = $v['lampiran_berkas_id'] ?? null;
        }
        $v['konfirmasi'] = true;
        return $v;
    }
}
