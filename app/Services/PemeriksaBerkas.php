<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

final class PemeriksaBerkas
{
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

        $tipe = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        if (! isset($tipe[$ext]) || $tipe[$ext] !== $mime) {
            $this->gagal(
                'Gunakan PDF, JPG, atau PNG dengan isi dan ekstensi yang sesuai.'
            );
        }

        $path = $file->getRealPath();

        if ($mime === 'application/pdf') {
            $awal = file_get_contents($path, false, null, 0, 5);

            if ($awal !== '%PDF-') {
                $this->gagal('Header PDF tidak sesuai.');
            }
        } else {
            $gambar = @getimagesize($path);

            if (
                ! is_array($gambar)
                || ($gambar['mime'] ?? '') !== $mime
                || $gambar[0] < 1
                || $gambar[1] < 1
                || $gambar[0] * $gambar[1] > (int) config('berkas.maks_piksel')
            ) {
                $this->gagal(
                    'Gambar tidak valid atau dimensinya terlalu besar.'
                );
            }
        }

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

    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages([
            'file' => $pesan,
        ]);
    }
}
