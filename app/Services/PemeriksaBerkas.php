<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

final class PemeriksaBerkas
{
    private const TIPE = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
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

    public function periksa(UploadedFile $file): array
    {
        if (
            ! $file->isValid()
            || $file->getSize() < 1
            || $file->getSize() > (int) config('berkas.maks_kib') * 1024
        ) {
            $this->gagal(
                'Unggahan harus berisi data dan berukuran paling besar 20 MB.'
            );
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        if (
            ! isset(self::TIPE[$ext])
            || ! in_array($mime, self::TIPE[$ext], true)
        ) {
            $this->gagal(
                'Format berkas tidak diizinkan atau isi berkas tidak sesuai ekstensinya.'
            );
        }

        $path = $file->getRealPath();

        if (! is_string($path) || $path === '') {
            $this->gagal('Berkas sementara tidak dapat dibaca.');
        }

        $this->pastikanFormatSesuai($path, $ext, $mime);

        $scanner = (string) config('berkas.scanner');

        if (
            ! app()->environment(['local', 'testing'])
            && $scanner !== 'clamav'
        ) {
            $this->gagal(
                'Pemindai berkas belum dikonfigurasi. Hubungi pengelola sistem.'
            );
        }

        if ($scanner === 'clamav') {
            try {
                $process = new Process([
                    (string) config('berkas.clamscan'),
                    '--no-summary',
                    '--',
                    $path,
                ]);

                $process
                    ->setTimeout((int) config('berkas.scanner_timeout'))
                    ->run();

                $code = $process->getExitCode();
            } catch (\Throwable) {
                $this->gagal(
                    'Pemindaian tidak selesai. Coba lagi atau hubungi pengelola.'
                );
            }

            if ($code === 1) {
                $this->gagal('Berkas ditolak oleh pemindai.');
            }

            if ($code !== 0) {
                $this->gagal(
                    'Pemindai belum siap. Unggahan tidak disimpan.'
                );
            }
        } elseif ($scanner !== 'none') {
            $this->gagal('Konfigurasi pemindai tidak dikenali.');
        }

        $nama = basename(
            str_replace('\\', '/', $file->getClientOriginalName())
        );

        $nama = preg_replace('/[\x00-\x1F\x7F]/u', '', $nama)
            ?: 'berkas.' . $ext;

        $hash = hash_file('sha256', $path);

        if ($hash === false) {
            $this->gagal(
                'Berkas sementara tidak dapat diperiksa.'
            );
        }

        return [
            'nama_asli' => mb_substr($nama, 0, 255),
            'mime_type' => $mime,
            'ekstensi' => $ext === 'jpeg' ? 'jpg' : $ext,
            'ukuran_byte' => (int) $file->getSize(),
            'sha256' => $hash,
            'pemeriksaan' => $scanner === 'clamav'
                ? 'clamav'
                : 'format',
        ];
    }

    private function pastikanFormatSesuai(
        string $path,
        string $ext,
        string $mime
    ): void {
        if ($ext === 'pdf') {
            $awal = file_get_contents($path, false, null, 0, 5);

            if ($awal !== '%PDF-') {
                $this->gagal('Header PDF tidak sesuai.');
            }

            return;
        }

        if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            $gambar = @getimagesize($path);

            if (
                ! is_array($gambar)
                || ($gambar['mime'] ?? '') !== $mime
                || $gambar[0] < 1
                || $gambar[1] < 1
                || $gambar[0] * $gambar[1]
                    > (int) config('berkas.maks_piksel')
            ) {
                $this->gagal(
                    'Gambar tidak valid atau dimensinya terlalu besar.'
                );
            }

            return;
        }

        if (in_array($ext, ['doc', 'xls', 'ppt'], true)) {
            $awal = file_get_contents($path, false, null, 0, 8);

            if ($awal !== hex2bin('D0CF11E0A1B11AE1')) {
                $this->gagal(
                    'Berkas Office lama tidak memiliki struktur yang sesuai.'
                );
            }

            return;
        }

        if (
            in_array($ext, ['docx', 'xlsx', 'pptx'], true)
            && ! $this->arsipOfficeSesuai($path, $ext)
        ) {
            $this->gagal(
                'Berkas Office tidak memiliki struktur yang sesuai.'
            );
        }

        if ($ext === 'zip' && ! $this->arsipZipSesuai($path)) {
            $this->gagal('Berkas ZIP tidak memiliki struktur yang sesuai.');
        }
    }

    private function arsipOfficeSesuai(
        string $path,
        string $ext
    ): bool {
        $folder = [
            'docx' => 'word/',
            'xlsx' => 'xl/',
            'pptx' => 'ppt/',
        ][$ext];

        return $this->arsipMemiliki(
            $path,
            static function (\ZipArchive $zip) use ($folder): bool {
                if ($zip->locateName('[Content_Types].xml') === false) {
                    return false;
                }

                for ($nomor = 0; $nomor < $zip->numFiles; $nomor++) {
                    $nama = $zip->getNameIndex($nomor);

                    if (
                        is_string($nama)
                        && str_starts_with($nama, $folder)
                    ) {
                        return true;
                    }
                }

                return false;
            }
        );
    }

    private function arsipZipSesuai(string $path): bool
    {
        return $this->arsipMemiliki(
            $path,
            static function (\ZipArchive $zip): bool {
                return $zip->numFiles >= 0;
            }
        );
    }

    private function arsipMemiliki(
        string $path,
        callable $pemeriksa
    ): bool {
        if (! class_exists(\ZipArchive::class)) {
            $this->gagal(
                'Ekstensi PHP ZIP belum aktif. Hubungi pengelola sistem.'
            );
        }

        $zip = new \ZipArchive();
        $hasil = $zip->open($path);

        if ($hasil !== true) {
            return false;
        }

        try {
            return (bool) $pemeriksa($zip);
        } finally {
            $zip->close();
        }
    }

    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages([
            'file' => $pesan,
        ]);
    }
}