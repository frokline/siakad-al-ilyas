<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\JenisSurat;
use App\Models\PermohonanSurat;
use App\Models\RiwayatSurat;
use App\Models\User;
use App\Services\AksesSurat;
use App\Services\AturanPermohonanSurat;
use App\Services\BerkasSurat;
use App\Services\KonteksSurat;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class KelolaPermohonanSurat
{
    public function ajukan(int $userId, array $data): PermohonanSurat
    {
        $v = AturanPermohonanSurat::data($data, true);
        $hash = hash('sha256', json_encode($v, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        $u = User::query()->findOrFail($userId);
        abort_unless(app(AksesSurat::class)->mahasiswa($u), 403);
        $ulang = PermohonanSurat::query()->where('pemohon_id', $userId)->where('form_token', $v['form_token'])->first();
        if ($ulang) {
            return $this->ulang($ulang, $hash);
        }
        // I/O dilakukan sebelum transaksi, snapshot diperiksa ulang saat row berkas dikunci.
        $snapshot = $v['lampiran_berkas_id'] ? app(BerkasSurat::class)->periksa($v['lampiran_berkas_id'], $userId, false) : null;
        try {
            return DB::transaction(function () use ($userId, $v, $hash, $snapshot): PermohonanSurat {
                $u = $this->pelaku($userId);
                abort_unless(app(AksesSurat::class)->mahasiswa($u), 403);
                $ulang = PermohonanSurat::query()->where('pemohon_id', $userId)->where('form_token', $v['form_token'])->lockForUpdate()->first();
                if ($ulang) {
                    return $this->ulang($ulang, $hash);
                }
                $k = app(KonteksSurat::class)->kunci($v['registrasi_semester_id'], $userId);
                $j = JenisSurat::query()->whereKey($v['jenis_surat_id'])->lockForUpdate()->firstOrFail();
                if (! $j->aktif || $j->kode !== JenisSurat::AKTIF_KULIAH || ! hash_equals($j->versiForm(), $v['versi_jenis'])) {
                    $this->gagal('jenis_surat_id', 'Layanan aktif kuliah berubah/tidak tersedia. Buka formulir baru.');
                }
                $slot = PermohonanSurat::slot($v['registrasi_semester_id'], $j->id);
                if (PermohonanSurat::query()->where('slot_aktif', $slot)->lockForUpdate()->first()) {
                    $this->gagal('registrasi_semester_id', 'Masih ada permohonan jenis ini yang diajukan/diproses untuk registrasi tersebut.');
                }
                if ($snapshot) {
                    app(BerkasSurat::class)->kunci($v['lampiran_berkas_id'], $snapshot);
                }
                $p = new PermohonanSurat();
                $p->nomor_pengajuan = 'SRT-' . strtoupper((string) Str::ulid());
                $p->registrasi_semester_id = $v['registrasi_semester_id'];
                $p->mahasiswa_id = $k['mahasiswa_id'];
                $p->pemohon_id = $userId;
                $p->jenis_surat_id = $j->id;
                $p->keperluan = $v['keperluan'];
                $p->akademik_snapshot = $k;
                $p->jenis_snapshot = ['kode' => $j->kode, 'nama' => $j->nama, 'syarat' => $j->syarat, 'revisi' => $j->revisi];
                $p->lampiran_berkas_id = $v['lampiran_berkas_id'];
                $p->lampiran_snapshot = $snapshot;
                $p->status = 'diajukan';
                $p->slot_aktif = $slot;
                $p->diajukan_at = now('UTC');
                $p->revisi = 1;
                $p->form_token = $v['form_token'];
                $p->hash_permohonan = $hash;
                $p->save();
                $this->catat($p, $userId, null, null, 'Permohonan diajukan oleh mahasiswa.');
                return $p;
            }, 3);
        } catch (UniqueConstraintViolationException $e) {
            if (PermohonanSurat::query()->where('slot_aktif', PermohonanSurat::slot($v['registrasi_semester_id'], $v['jenis_surat_id']))->exists()) {
                $this->gagal('registrasi_semester_id', 'Permohonan aktif sudah tercatat. Muat ulang daftar.');
            }
            throw $e;
        }
    }
    public function tindakan(int $userId, PermohonanSurat $bound, array $data): PermohonanSurat
    {
        $v = AturanPermohonanSurat::data($data, false);
        $u = User::query()->findOrFail($userId);
        $awal = PermohonanSurat::query()->findOrFail($bound->id);
        abort_unless(app(AksesSurat::class)->tindakan($u, $awal, $v['tujuan']), 403);
        $snapshot = $v['tujuan'] === 'terbit' ? app(BerkasSurat::class)->periksa($v['hasil_berkas_id'], $userId, true) : null;
        try {
            return DB::transaction(function () use ($userId, $bound, $v, $snapshot): PermohonanSurat {
                $u = $this->pelaku($userId);
                // Urutan konteks lalu permohonan mengikuti pengajuan; perubahan registrasi tidak lolos pemeriksaan terbit.
                $k = $v['tujuan'] === 'terbit' ? app(KonteksSurat::class)->kunci($bound->registrasi_semester_id, $bound->pemohon_id) : null;
                $p = PermohonanSurat::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
                abort_unless(app(AksesSurat::class)->tindakan($u, $p, $v['tujuan']), 403);
                if (! hash_equals($p->versiForm(), $v['versi']) || $p->revisi >= 4294967295) {
                    $this->gagal('versi', 'Status/data berubah. Muat ulang halaman sebelum melanjutkan.');
                }
                $terakhir = $p->riwayat()->orderByDesc('revisi_permohonan')->lockForUpdate()->first();
                if (! $terakhir || $terakhir->revisi_permohonan !== $p->revisi || $terakhir->status_baru !== $p->status) {
                    $this->gagal('status', 'Riwayat tidak konsisten. Periksa integritas data sebelum melanjutkan.');
                }
                if ($p->status === 'terbit' && mb_strlen($v['catatan']) < 20) {
                    $this->gagal('catatan', 'Alasan pencabutan surat terbit minimal 20 karakter.');
                }
                $sebelum = $p->ringkasanAudit();
                $asal = $p->status;
                if ($v['tujuan'] === 'terbit') {
                    if (\Illuminate\Support\Arr::sortRecursive($k) !== \Illuminate\Support\Arr::sortRecursive($p->akademik_snapshot)) {
                        $this->gagal('registrasi_semester_id', 'Identitas/konteks akademik berubah. Tolak permohonan ini dengan alasan dan minta pengajuan baru.');
                    }
                    if (PermohonanSurat::query()->where('nomor_surat', $v['nomor_surat'])->lockForUpdate()->first()) {
                        $this->gagal('nomor_surat', 'Nomor surat sudah dipakai, termasuk surat yang dibatalkan.');
                    }
                    app(BerkasSurat::class)->kunci($v['hasil_berkas_id'], $snapshot);
                    $p->nomor_surat = $v['nomor_surat'];
                    $p->hasil_berkas_id = $v['hasil_berkas_id'];
                    $p->hasil_snapshot = $snapshot;
                    $p->terbit_at = now('UTC');
                }
                $p->status = $v['tujuan'];
                $p->slot_aktif = $p->status === 'diproses' ? PermohonanSurat::slot($p->registrasi_semester_id, $p->jenis_surat_id) : null;
                $p->revisi++;
                $p->save();
                $this->catat($p, $userId, $asal, $sebelum, $v['catatan']);
                return $p;
            }, 3);
        } catch (UniqueConstraintViolationException $e) {
            if (isset($v['nomor_surat']) && PermohonanSurat::query()->where('nomor_surat', $v['nomor_surat'])->exists()) {
                $this->gagal('nomor_surat', 'Nomor sudah dipakai permohonan lain. Muat ulang halaman.');
            }
            throw $e;
        }
    }
    private function pelaku(int $id): User
    {
        $u = User::query()->whereKey($id)->lockForUpdate()->firstOrFail();
        $u->roles()->orderBy('roles.id')->lockForUpdate()->get(['roles.id']);
        abort_unless($u->status === 'aktif', 403);
        return $u;
    }
    private function ulang(PermohonanSurat $p, string $hash): PermohonanSurat
    {
        if (! hash_equals($p->hash_permohonan, $hash)) {
            $this->gagal('form_token', 'Token dipakai untuk isi berbeda. Buka formulir baru.');
        }
        return $p;
    }
    private function catat(PermohonanSurat $p, int $userId, ?string $asal, ?array $sebelum, string $catatan): void
    {
        $r = new RiwayatSurat();
        $r->permohonan_surat_id = $p->id;
        $r->pelaku_id = $userId;
        $r->status_lama = $asal;
        $r->status_baru = $p->status;
        $r->revisi_permohonan = $p->revisi;
        $r->catatan = $catatan;
        $r->waktu = now('UTC');
        $r->save();
        $a = new AuditLog();
        $a->pelaku_id = $userId;
        $a->entitas = 'permohonan_surat';
        $a->entitas_id = $p->id;
        $a->versi_entitas = $p->revisi;
        $a->aksi = $p->status;
        $a->sebelum = $sebelum;
        $a->sesudah = $p->ringkasanAudit();
        $a->alasan = $catatan;
        $a->waktu = now('UTC');
        $a->save();
    }
    private function gagal(string $key, string $pesan): never
    {
        throw ValidationException::withMessages([$key => $pesan]);
    }
}
