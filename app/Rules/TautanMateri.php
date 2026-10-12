<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class TautanMateri implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            ! is_string($value) || ! filter_var($value, FILTER_VALIDATE_URL)
            || preg_match('/[\x00-\x20\x7f\\\\]/', $value)
        ) {
            $fail('Tautan harus berupa URL HTTPS yang valid.');
            return;
        }
        $url = parse_url($value);
        if (
            ! is_array($url) || strtolower($url['scheme'] ?? '') !== 'https'
            || isset($url['user']) || isset($url['pass'])
            || (isset($url['port']) && $url['port'] !== 443)
            || ! in_array(strtolower($url['host'] ?? ''), config('materi.host_tautan', []), true)
        ) {
            $fail('Gunakan HTTPS dari host referensi yang diizinkan pengelola.');
        }
    }
}
