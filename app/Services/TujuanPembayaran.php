<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

final class TujuanPembayaran
{
    public static function teks(): string
    {
        $value = trim((string) config('pembayaran.tujuan_transfer'));
        if (mb_strlen($value) < 10 || mb_strlen($value) > 150 || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            throw ValidationException::withMessages(['tujuan_transfer' => 'Rekening tujuan resmi belum dikonfigurasi dengan benar. Hubungi admin keuangan.']);
        }
        return $value;
    }
    public static function versi(): string
    {
        return hash_hmac('sha256', 'tujuan-pembayaran:' . self::teks(), (string) config('app.key'));
    }
}
