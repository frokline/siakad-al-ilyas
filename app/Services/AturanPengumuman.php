<?php

namespace App\Services;

use App\Models\Pengumuman;
use App\Models\SasaranPengumuman;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AturanPengumuman
{
    public static function isi(array $input, bool $baru): array
    {
        // Baris kosong dari form diabaikan; baris setengah terisi tetap divalidasi.
        if (isset($input['sasaran']) && is_array($input['sasaran'])) {
            $input['sasaran'] = array_values(array_filter($input['sasaran'], fn($s) => ! is_array($s)
                || count(array_filter($s, fn($v) => $v !== null && $v !== '')) > 0));
        }
        foreach (['judul', 'isi'] as $key) {
            if (isset($input[$key]) && is_string($input[$key])) {
                $input[$key] = trim($input[$key]);
            }
        }
        $v = Validator::make($input, [
            'judul' => ['required', 'string', 'min:3', 'max:200'],
            'isi' => ['required', 'string', 'min:3', 'max:20000'],
            'berakhir_lokal' => ['nullable', 'date_format:Y-m-d\TH:i', 'before:2101-01-01', 'after:1999-12-31'],
            'form_token' => $baru ? ['required', 'uuid'] : ['prohibited'],
            'versi' => $baru ? ['prohibited'] : ['required', 'regex:/\A[a-f0-9]{64}\z/'],
            'sasaran' => ['required', 'array', 'min:1', 'max:10'],
            'sasaran.*' => ['required', 'array:lingkup,program_studi_id,kelas_kuliah_id,role_id'],
            'sasaran.*.lingkup' => ['required', Rule::in(['kampus', 'prodi', 'kelas'])],
            'sasaran.*.program_studi_id' => ['nullable', 'integer', 'min:1'],
            'sasaran.*.kelas_kuliah_id' => ['nullable', 'integer', 'min:1'],
            'sasaran.*.role_id' => ['nullable', 'integer', 'min:1'],
        ])->validate();
        $targets = [];
        foreach ($v['sasaran'] as $i => $s) {
            foreach (['program_studi_id', 'kelas_kuliah_id', 'role_id'] as $k) {
                $s[$k] = ! empty($s[$k]) ? (int) $s[$k] : null;
            }
            $ok = match ($s['lingkup']) {
                'kampus' => $s['program_studi_id'] === null && $s['kelas_kuliah_id'] === null,
                'prodi' => $s['program_studi_id'] !== null && $s['kelas_kuliah_id'] === null,
                'kelas' => $s['kelas_kuliah_id'] !== null && $s['program_studi_id'] === null,
            };
            if (! $ok) {
                throw ValidationException::withMessages(["sasaran.$i" => 'Isi hanya induk yang sesuai lingkup. Kampus: kedua induk kosong.']);
            }
            $s = ['lingkup' => $s['lingkup'], 'program_studi_id' => $s['program_studi_id'], 'kelas_kuliah_id' => $s['kelas_kuliah_id'], 'role_id' => $s['role_id']];
            $key = SasaranPengumuman::kunci($s);
            if (isset($targets[$key])) {
                throw ValidationException::withMessages(["sasaran.$i" => 'Sasaran yang sama tidak boleh berulang.']);
            }
            $targets[$key] = $s;
        }
        ksort($targets);
        $v['sasaran'] = array_values($targets);
        $v['berakhir_at'] = empty($v['berakhir_lokal']) ? null : CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $v['berakhir_lokal'], Pengumuman::ZONA)->utc()->format('Y-m-d H:i:s');
        unset($v['berakhir_lokal']);
        return $v;
    }
    public static function tindakan(array $input): array
    {
        return Validator::make($input, [
            'aksi' => ['required', Rule::in(['terbit', 'arsip'])],
            'versi' => ['required', 'regex:/\A[a-f0-9]{64}\z/'],
            'konfirmasi' => ['accepted'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000']
        ])->validate();
    }
}
