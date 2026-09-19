<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class TautanPertemuanAman implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::sesuai($value)) {
            $fail('Tautan harus URL HTTPS yang valid, tanpa kredensial, spasi, atau karakter kontrol.');
        }
    }

    public static function sesuai(mixed $nilai): bool
    {
        if (
            ! is_string($nilai) || strlen($nilai) > 2048 || $nilai === ''
            || preg_match('/[\x00-\x20\x7F]/', $nilai) || str_contains($nilai, '\\')
            || filter_var($nilai, FILTER_VALIDATE_URL) === false
        ) {
            return false;
        }
        $bagian = parse_url($nilai);
        return is_array($bagian)
            && strtolower($bagian['scheme'] ?? '') === 'https'
            && ! empty($bagian['host'])
            && ! isset($bagian['user']) && ! isset($bagian['pass']);
    }
}
