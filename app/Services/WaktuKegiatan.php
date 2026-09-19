<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Validation\ValidationException;

final class WaktuKegiatan
{
    public static function zona(): string
    {
        return (string) config('siakad.timezone', 'Asia/Makassar');
    }

    public static function dariForm(mixed $value, string $field): CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}\z/', $value)) {
            self::gagal($field);
        }
        try {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value, new DateTimeZone(self::zona()));
            $errors = DateTimeImmutable::getLastErrors();
        } catch (\Throwable) {
            self::gagal($field);
        }
        if (
            ! $date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d\TH:i') !== $value || (int) $date->format('Y') < 2000 || (int) $date->format('Y') > 2099
        ) {
            self::gagal($field);
        }
        return CarbonImmutable::instance($date)->setTimezone('UTC');
    }
    private static function gagal(string $field): never
    {
        throw ValidationException::withMessages([$field => 'Isi tanggal dan jam yang valid (tahun 2000–2099) pada zona waktu kampus.']);
    }
}
