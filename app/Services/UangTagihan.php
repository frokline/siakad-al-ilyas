<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

final class UangTagihan
{
    public static function normal(string $nilai): string
    {
        // Tanpa float: DECIMAL(14,2), maksimum 999999999999.99.
        $nilai = trim($nilai);
        if (! preg_match('/\A(?:0|[1-9][0-9]{0,11})(?:\.[0-9]{1,2})?\z/', $nilai)) {
            throw ValidationException::withMessages(['nominal' => 'Isi angka tanpa pemisah ribuan, maksimal dua desimal dengan titik.']);
        }
        [$utuh, $pecahan] = array_pad(explode('.', $nilai, 2), 2, '');
        $hasil = $utuh . '.' . str_pad($pecahan, 2, '0');
        if ($hasil === '0.00') {
            throw ValidationException::withMessages(['nominal' => 'Nominal harus lebih dari nol.']);
        }
        return $hasil;
    }
    public static function rupiah(string $nilai): string
    {
        [$utuh, $pecahan] = explode('.', self::normal($nilai));
        return 'Rp ' . preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $utuh) . ',' . $pecahan;
    }
}
