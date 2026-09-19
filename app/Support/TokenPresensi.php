<?php

namespace App\Support;

use App\Models\Pertemuan;
use Illuminate\Validation\ValidationException;

final class TokenPresensi
{
    public static function buat(string $lingkup, array $data): string
    {
        return hash_hmac('sha256', json_encode([$lingkup, $data], JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    public static function pertemuan(Pertemuan $sesi): string
    {
        return self::buat('persiapan-presensi', [$sesi->id, $sesi->revisi, $sesi->status]);
    }

    public static function periksa(string $terkini, mixed $dikirim, string $field): void
    {
        if (! is_string($dikirim) || ! hash_equals($terkini, $dikirim)) {
            throw ValidationException::withMessages([
                $field => 'Data sudah berubah. Muat ulang halaman dan periksa data terbaru sebelum menyimpan.',
            ]);
        }
    }
}
