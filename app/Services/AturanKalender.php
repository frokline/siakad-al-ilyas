<?php

namespace App\Services;

use App\Models\KalenderAkademik;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AturanKalender
{
    public static function isi(array $data, bool $baru): array
    {
        foreach (['judul', 'keterangan', 'alasan'] as $key) {
            if (is_string($data[$key] ?? null)) {
                $data[$key] = trim(str_replace(["\r\n", "\r"], "\n", $data[$key]));
            }
        }
        if (($data['program_studi_id'] ?? null) === '') {
            $data['program_studi_id'] = null;
        }
        $rules = [
            'periode_akademik_id' => ['required', 'integer', 'min:1'],
            'program_studi_id' => ['nullable', 'integer', 'min:1'],
            'judul' => ['required', 'string', 'min:3', 'max:200', 'not_regex:/[\x00-\x1F\x7F]/'],
            'keterangan' => ['nullable', 'string', 'max:5000', 'not_regex:/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/'],
            'jenis' => ['required', Rule::in(array_keys(KalenderAkademik::JENIS))],
            'mulai_lokal' => ['required', 'date_format:Y-m-d\TH:i'],
            'selesai_lokal' => ['required', 'date_format:Y-m-d\TH:i'],
            'form_token' => $baru ? ['required', 'uuid'] : ['prohibited'],
            'versi' => $baru ? ['prohibited'] : ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => $baru ? ['nullable', 'string', 'max:1000'] : ['required', 'string', 'min:10', 'max:1000'],
        ];
        foreach (['status', 'pembuat_id', 'revisi', 'mulai_at', 'selesai_at', 'diterbitkan_at', 'dibatalkan_at', 'catatan_perubahan', 'hash_permohonan'] as $key) {
            $rules[$key] = ['prohibited'];
        }
        $v = Validator::make($data, $rules)->validate();
        $mulai = self::waktu($v['mulai_lokal']);
        $selesai = self::waktu($v['selesai_lokal']);
        if ($selesai->lteTo($mulai) || $selesai->greaterThan($mulai->addDays(366))) {
            throw ValidationException::withMessages(['selesai_lokal' => 'Akhir harus setelah awal; durasi agenda maksimal 366 hari.']);
        }
        return [
            'isi' => [
                'periode_akademik_id' => (int) $v['periode_akademik_id'],
                'program_studi_id' => isset($v['program_studi_id']) ? (int) $v['program_studi_id'] : null,
                'judul' => $v['judul'],
                'keterangan' => ($v['keterangan'] ?? '') !== '' ? $v['keterangan'] : null,
                'jenis' => $v['jenis'],
                'mulai_at' => $mulai->utc()->format('Y-m-d H:i:s'),
                'selesai_at' => $selesai->utc()->format('Y-m-d H:i:s')
            ],
            'kontrol' => Arr::only($v, ['form_token', 'versi', 'alasan'])
        ];
    }
    public static function tindakan(array $data): array
    {
        if (is_string($data['alasan'] ?? null)) {
            $data['alasan'] = trim($data['alasan']);
        }
        $rules = [
            'aksi' => ['required', Rule::in(['terbitkan', 'batalkan'])],
            'versi' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
            'konfirmasi' => ['required', 'accepted']
        ];
        foreach (['periode_akademik_id', 'program_studi_id', 'judul', 'jenis', 'keterangan', 'mulai_lokal', 'selesai_lokal', 'status', 'pembuat_id', 'revisi', 'catatan_perubahan'] as $key) {
            $rules[$key] = ['prohibited'];
        }
        return Arr::only(Validator::make($data, $rules)->validate(), ['aksi', 'versi', 'alasan', 'konfirmasi']);
    }
    private static function waktu(string $nilai): CarbonImmutable
    {
        $w = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $nilai, KalenderAkademik::ZONA);
        if (! $w || $w->format('Y-m-d\TH:i') !== $nilai || $w->year < 2000 || $w->year > 2100) {
            throw ValidationException::withMessages(['mulai_lokal' => 'Tanggal harus valid pada tahun 2000–2100.']);
        }
        return $w;
    }
}
