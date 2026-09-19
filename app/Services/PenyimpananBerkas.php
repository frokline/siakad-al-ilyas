<?php

namespace App\Services;

use App\Models\Berkas;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class PenyimpananBerkas
{
    public function diskBaru(): string
    {
        $nama = (string) config('berkas.disk');
        if (
            ! in_array($nama, config('berkas.disk_diizinkan'), true)
            || (! app()->environment(['local', 'testing']) && $nama !== 'berkas_s3')
        ) {
            throw new RuntimeException('Penyimpanan privat belum dikonfigurasi.');
        }
        return $nama;
    }
    public function disk(Berkas $file): Filesystem
    {
        if (
            ! in_array($file->storage_disk, config('berkas.disk_diizinkan'), true)
            || ! preg_match('~\Asiakad/berkas/[0-9]{4}/[0-9]{2}/[a-f0-9-]{36}\.(pdf|jpg|png)\z~', $file->object_key)
        ) {
            throw new RuntimeException('Lokasi berkas tidak sesuai konfigurasi.');
        }
        return Storage::disk($file->storage_disk);
    }
    public function tulis(Berkas $file, string $path): void
    {
        $stream = fopen($path, 'rb');
        if (! is_resource($stream)) {
            throw new RuntimeException('Berkas sementara tidak dapat dibaca.');
        }
        try {
            // Objek memakai UUID baru; tidak ada penggantian isi pada objek yang tersedia.
            if (! $this->disk($file)->put($file->object_key, $stream, ['visibility' => 'private', 'ContentType' => $file->mime_type])) {
                throw new RuntimeException('Penyimpanan tidak menyelesaikan unggahan.');
            }
            if ($this->disk($file)->size($file->object_key) !== $file->ukuran_byte) {
                throw new RuntimeException('Ukuran objek tidak sesuai.');
            }
        } finally {
            fclose($stream);
        }
    }
    public function buka(Berkas $file): mixed
    {
        if (! app()->environment(['local', 'testing']) && $file->pemeriksaan !== 'clamav') {
            throw new RuntimeException('Berkas ini belum melalui pemindaian produksi.');
        }
        if ($this->disk($file)->size($file->object_key) !== $file->ukuran_byte) {
            throw new RuntimeException('Objek tidak tersedia atau ukurannya berubah.');
        }
        $stream = $this->disk($file)->readStream($file->object_key);
        if (! is_resource($stream)) {
            throw new RuntimeException('Objek tidak dapat dibaca.');
        }
        $salinan = fopen('php://temp/maxmemory:2097152', 'w+b');
        if (! is_resource($salinan)) {
            fclose($stream);
            throw new RuntimeException('Berkas sementara tidak dapat dibuat.');
        }
        // Verifikasi sebelum mengirim satu byte pun ke browser. Memori maksimal 2 MB;
        // sisanya menggunakan file sementara PHP, untuk batas unggahan 20 MB.
        try {
            $hash = hash_init('sha256');
            $jumlah = 0;
            while (! feof($stream)) {
                $bagian = fread($stream, 1048576);
                if ($bagian === false || ($bagian === '' && ! feof($stream))) {
                    throw new RuntimeException('Pembacaan objek terputus.');
                }
                $jumlah += strlen($bagian);
                if ($jumlah > $file->ukuran_byte) {
                    throw new RuntimeException('Ukuran objek melebihi metadata.');
                }
                hash_update($hash, $bagian);
                if (fwrite($salinan, $bagian) !== strlen($bagian)) {
                    throw new RuntimeException('Berkas sementara tidak dapat ditulis.');
                }
            }
            if ($jumlah !== $file->ukuran_byte || ! hash_equals($file->sha256, hash_final($hash))) {
                throw new RuntimeException('Integritas objek tidak sesuai.');
            }
            rewind($salinan);
            return $salinan;
        } catch (\Throwable $error) {
            fclose($salinan);
            throw $error;
        } finally {
            fclose($stream);
        }
    }
}
