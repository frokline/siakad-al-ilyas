<?php

namespace App\Services;

use App\Models\Berkas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use LogicException;

final class BerkasSurat
{
    public static function snapshot(Berkas $b): array
    {
        return [
            'id' => (int) $b->id,
            'pengunggah_id' => $b->diunggah_oleh,
            'nama_asli' => $b->nama_asli,
            'mime_type' => $b->mime_type,
            'ekstensi' => $b->ekstensi,
            'ukuran_byte' => $b->ukuran_byte,
            'sha256' => $b->sha256,
            'revisi' => $b->revisi
        ];
    }
    public static function cocok(Berkas $b, ?array $snapshot): bool
    {
        return $b->status === Berkas::TERSEDIA && \Illuminate\Support\Arr::sortRecursive($snapshot ?? []) === \Illuminate\Support\Arr::sortRecursive(self::snapshot($b));
    }
    public function periksa(int $id, int $pemilik, bool $hasil): array
    {
        $b = Berkas::query()->whereKey($id)->where('diunggah_oleh', $pemilik)->where('status', Berkas::TERSEDIA)->first();
        $key = $hasil ? 'hasil_berkas_id' : 'lampiran_berkas_id';
        if (
            ! $b || ! in_array($b->ekstensi, $hasil ? ['pdf'] : ['pdf', 'jpg', 'png'], true)
            || ($hasil && $b->mime_type !== 'application/pdf')
        ) {
            throw ValidationException::withMessages([$key => $hasil ? 'Pilih PDF final milik akun petugas yang sedang login.' : 'Pilih PDF/JPG/PNG milik akun Anda yang tersedia.']);
        }
        try {
            $stream = app(PenyimpananBerkas::class)->buka($b);
            fclose($stream);
        } catch (\Throwable $e) {
            Log::warning('Pemeriksaan dokumen surat gagal.', ['berkas_id' => $id, 'jenis' => $e::class]);
            throw ValidationException::withMessages([$key => 'Isi berkas tidak dapat diverifikasi. Unggah ulang berkas yang benar atau hubungi pengelola.']);
        }
        return self::snapshot($b);
    }
    public function kunci(int $id, array $snapshot): Berkas
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Penautan dokumen memerlukan transaksi.');
        }
        $b = Berkas::query()->whereKey($id)->lockForUpdate()->firstOrFail();
        if (! self::cocok($b, $snapshot)) {
            $this->gagal();
        }
        foreach (['materi_berkas', 'kegiatan_berkas', 'pengumpulan_berkas'] as $t) {
            if (Schema::hasTable($t) && DB::table($t)->where('berkas_id', $id)->lockForUpdate()->first()) {
                $this->gagal();
            }
        }
        if (Schema::hasTable('pembayaran') && DB::table('pembayaran')->where('bukti_berkas_id', $id)->lockForUpdate()->first()) {
            $this->gagal();
        }
        if (DB::table('permohonan_surat')->where(fn($q) => $q->where('lampiran_berkas_id', $id)->orWhere('hasil_berkas_id', $id))->lockForUpdate()->first()) {
            $this->gagal();
        }
        return $b;
    }
    private function gagal(): never
    {
        throw ValidationException::withMessages(['berkas' => 'Berkas berubah atau telah digunakan. Unggah dokumen khusus untuk permohonan ini, bukan lampiran pembelajaran/bukti pembayaran.']);
    }
}
