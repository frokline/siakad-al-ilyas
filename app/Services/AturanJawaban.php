<?php

namespace App\Services;

use App\Models\Berkas;
use App\Models\Kegiatan;
use Illuminate\Validation\ValidationException;

final class AturanJawaban
{
    private const MIME = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'png' => ['image/png'],
        'doc' => ['application/msword'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => [
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ],
        'zip' => [
            'application/zip',
            'application/x-zip-compressed',
            'multipart/x-zip',
        ],
    ];

    public static function teks(mixed $input): ?string
    {
        if ($input === null) {
            return null;
        }

        if (
            ! is_string($input)
            || ! mb_check_encoding($input, 'UTF-8')
        ) {
            self::gagal('Pesan jawaban tidak valid.');
        }

        $teks = trim(
            str_replace(["\r\n", "\r"], "\n", $input)
        );

        $maks = max(
            1,
            min(
                10000,
                (int) config(
                    'pengumpulan.maks_karakter_jawaban',
                    10000
                )
            )
        );

        if (
            mb_strlen($teks) > $maks
            || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $teks)
        ) {
            self::gagal(
                'Pesan terlalu panjang atau mengandung karakter yang tidak diizinkan.'
            );
        }

        return $teks === '' ? null : $teks;
    }

    public static function ids(array $ids): array
    {
        if (count($ids) > 10) {
            self::gagal(
                'Maksimal sepuluh berkas. Batas tugas dapat lebih kecil.'
            );
        }

        $hasil = [];

        foreach ($ids as $id) {
            if (
                ! is_scalar($id)
                || ! preg_match(
                    '/\A[1-9][0-9]{0,17}\z/',
                    (string) $id
                )
            ) {
                self::gagal('ID berkas tidak valid.');
            }

            $hasil[] = (int) $id;
        }

        if (count(array_unique($hasil)) !== count($hasil)) {
            self::gagal('Berkas tidak boleh dipilih lebih dari satu kali.');
        }

        sort($hasil, SORT_NUMERIC);

        return $hasil;
    }

    public static function berkas(
        Berkas $berkas,
        Kegiatan $kegiatan,
        int $pemilikId
    ): void {
        $mimeSesuai = in_array(
            $berkas->mime_type,
            self::MIME[$berkas->ekstensi] ?? [],
            true
        );

        if (
            $berkas->diunggah_oleh !== $pemilikId
            || $berkas->status !== Berkas::TERSEDIA
            || $berkas->ukuran_byte < 1
            || $berkas->ukuran_byte > $kegiatan->maks_ukuran_byte
            || ! in_array(
                $berkas->ekstensi,
                $kegiatan->ekstensi_diizinkan,
                true
            )
            || ! $mimeSesuai
            || ! preg_match(
                '/\A[a-f0-9]{64}\z/',
                (string) $berkas->sha256
            )
            || (
                ! app()->environment(['local', 'testing'])
                && $berkas->pemeriksaan !== 'clamav'
            )
        ) {
            self::gagal(
                'Berkas tidak tersedia, bukan milik Anda, belum dipindai, atau tidak sesuai ketentuan tugas.'
            );
        }
    }

    public static function gagal(string $pesan): never
    {
        throw ValidationException::withMessages([
            'pengumpulan' => $pesan,
        ]);
    }
}