<?php

namespace App\Services;

use App\Models\Berkas;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class BuktiPembayaran
{
    public static function snapshot(Berkas $b): array
    {
        return [
            'nama_asli' => $b->nama_asli,
            'mime_type' => $b->mime_type,
            'ekstensi' => $b->ekstensi,
            'ukuran_byte' => $b->ukuran_byte,
            'sha256' => $b->sha256,
            'revisi' => $b->revisi
        ];
    }
    public static function cocok(Pembayaran $p, Berkas $b): bool
    {
        return $p->bukti_berkas_id === (int) $b->id && $b->diunggah_oleh === $p->pengunggah_id
            && $b->status === Berkas::TERSEDIA && $p->bukti_snapshot === self::snapshot($b)
            && hash_equals($p->bukti_sha256, (string) $b->sha256);
    }
    public function periksa(int $id, int $userId): array
    {
        $b = Berkas::query()->whereKey($id)->where('diunggah_oleh', $userId)->where('status', Berkas::TERSEDIA)->first();
        if (! $b || ! in_array($b->ekstensi, ['pdf', 'jpg', 'png'], true)) {
            throw ValidationException::withMessages(['bukti_berkas_id' => 'Pilih berkas PDF/JPG/PNG milik akun Anda yang tersedia.']);
        }
        try {
            $stream = app(PenyimpananBerkas::class)->buka($b);
            fclose($stream);
        } catch (\Throwable $e) {
            Log::warning('Pemeriksaan bukti pembayaran gagal.', ['berkas_id' => $b->id, 'jenis' => $e::class]);
            throw ValidationException::withMessages(['bukti_berkas_id' => 'Bukti tidak dapat diverifikasi dari penyimpanan. Coba lagi atau hubungi pengelola.']);
        }
        return self::snapshot($b);
    }
}
