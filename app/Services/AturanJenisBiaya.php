<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

final class AturanJenisBiaya
{
    public static function normalisasi(array $data): array
    {
        foreach (['kode', 'nama', 'keterangan', 'alasan'] as $field) {
            if (is_string($data[$field] ?? null)) {
                $data[$field] = trim(str_replace(["\r\n", "\r"], "\n", $data[$field]));
                if (in_array($field, ['keterangan', 'alasan'], true) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
        }
        if (is_string($data['kode'] ?? null)) {
            $data['kode'] = strtoupper($data['kode']);
        }
        return $data;
    }
    public static function aturanIsi(bool $baru): array
    {
        return [
            'kode' => $baru ? ['required', 'string', 'regex:/\A[A-Z][A-Z0-9_-]{1,29}\z/'] : ['prohibited'],
            'nama' => ['required', 'string', 'min:3', 'max:100', 'not_regex:/[\x00-\x1F\x7F]/'],
            'keterangan' => ['nullable', 'string', 'max:1000', 'not_regex:/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/']
        ];
    }
    public static function validasiIsi(array $data, bool $baru): array
    {
        return Validator::make(self::normalisasi($data), self::aturanIsi($baru))->validate();
    }
}
