<?php

namespace App\Services;

use App\Models\Berkas;
use App\Models\Kegiatan;
use Illuminate\Validation\ValidationException;

final class AturanJawaban
{
    public static function teks(mixed $input): ?string
    {
        if ($input === null) {
            return null;
        }
        if (! is_string($input) || ! mb_check_encoding($input, 'UTF-8')) {
            self::gagal('Teks jawaban tidak valid.');
        }
        $teks = trim(str_replace(["\r\n", "\r"], "\n", $input));
        $maks = max(1, min(10000, (int) config('pengumpulan.maks_karakter_jawaban', 10000)));
        if (mb_strlen($teks) > $maks || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $teks)) {
            self::gagal('Teks terlalu panjang atau mengandung karakter kontrol yang tidak diizinkan.');
        }
        return $teks === '' ? null : $teks;
    }
    public static function ids(array $ids): array
    {
        if (count($ids) > 5) {
            self::gagal('Maksimal lima ID berkas. Batas kegiatan dapat lebih kecil.');
        }
        $hasil = [];
        foreach ($ids as $id) {
            if (! is_scalar($id) || ! preg_match('/\A[1-9][0-9]{0,17}\z/', (string) $id)) {
                self::gagal('ID berkas tidak valid.');
            }
            $hasil[] = (int) $id;
        }
        if (count(array_unique($hasil)) !== count($hasil)) {
            self::gagal('ID berkas tidak boleh berulang.');
        }
        sort($hasil, SORT_NUMERIC);
        return $hasil;
    }
    public static function berkas(Berkas $b, Kegiatan $k, int $pemilikId): void
    {
        $mime = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'png' => 'image/png'];
        if (
            $b->diunggah_oleh !== $pemilikId || $b->status !== Berkas::TERSEDIA
            || $b->ukuran_byte < 1 || $b->ukuran_byte > $k->maks_ukuran_byte
            || ! in_array($b->ekstensi, $k->ekstensi_diizinkan, true) || ($mime[$b->ekstensi] ?? null) !== $b->mime_type
            || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $b->sha256)
            || (! app()->environment(['local', 'testing']) && $b->pemeriksaan !== 'clamav')
        ) {
            self::gagal('Berkas tidak tersedia, bukan milik Anda, belum dipindai, atau tidak sesuai batas kegiatan.');
        }
    }
    public static function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['pengumpulan' => $pesan]);
    }
}
