<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\KalenderAkademik;
use App\Models\User;
use App\Services\AturanKalender;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class KelolaKalenderAkademik
{
    public function buat(int $userId, array $data): KalenderAkademik
    {
        $v = AturanKalender::isi($data, true);
        $isi = $v['isi'];
        $c = $v['kontrol'];
        $hash = hash('sha256', json_encode($isi, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        return DB::transaction(function () use ($userId, $isi, $c, $hash): KalenderAkademik {
            $this->pelaku($userId);
            $lama = KalenderAkademik::query()->where('pembuat_id', $userId)->where('form_token', $c['form_token'])->lockForUpdate()->first();
            if ($lama) {
                if (! hash_equals($lama->hash_permohonan, $hash)) {
                    $this->gagal('form_token', 'Formulir sudah dipakai untuk isi berbeda. Buka formulir baru.');
                }
                return $lama;
            }
            $this->konteks($isi['periode_akademik_id'], $isi['program_studi_id']);
            $k = new KalenderAkademik();
            foreach ($isi as $key => $value) {
                $k->{$key} = $value;
            }
            $k->pembuat_id = $userId;
            $k->status = 'draf';
            $k->revisi = 1;
            $k->form_token = $c['form_token'];
            $k->hash_permohonan = $hash;
            $k->save();
            $this->audit($k, $userId, 'buat', null, $c['alasan'] ?? 'Membuat draf agenda akademik.');
            return $k;
        }, 3);
    }
    public function ubah(int $userId, KalenderAkademik $bound, array $data): KalenderAkademik
    {
        $v = AturanKalender::isi($data, false);
        $isi = $v['isi'];
        $c = $v['kontrol'];
        return DB::transaction(function () use ($userId, $bound, $isi, $c): KalenderAkademik {
            $this->pelaku($userId);
            $k = $this->kunci($bound, $c['versi']);
            if ($k->status === 'batal') {
                $this->gagal('status', 'Agenda dibatalkan tidak dapat diedit. Buat agenda baru jika diperlukan.');
            }
            if ($k->diterbitkan_at && ($k->periode_akademik_id !== $isi['periode_akademik_id'] || $k->program_studi_id !== $isi['program_studi_id'])) {
                $this->gagal('periode_akademik_id', 'Periode dan sasaran tidak dapat diganti setelah publikasi. Batalkan lalu buat agenda yang benar.');
            }
            $this->konteks($isi['periode_akademik_id'], $isi['program_studi_id']);
            $sebelum = $k->ringkasanAudit();
            foreach ($isi as $key => $value) {
                $k->{$key} = $value;
            }
            if (! $k->isDirty(array_keys($isi))) {
                return $k;
            }
            $k->catatan_perubahan = $c['alasan'];
            $k->revisi++;
            $k->save();
            $this->audit($k, $userId, 'ubah', $sebelum, $c['alasan']);
            return $k;
        }, 3);
    }
    public function tindakan(int $userId, KalenderAkademik $bound, array $data): KalenderAkademik
    {
        $v = AturanKalender::tindakan($data);
        return DB::transaction(function () use ($userId, $bound, $v): KalenderAkademik {
            $this->pelaku($userId);
            $k = $this->kunci($bound, $v['versi']);
            $sebelum = $k->ringkasanAudit();
            if ($v['aksi'] === 'terbitkan') {
                if ($k->status !== 'draf') {
                    $this->gagal('status', 'Hanya draf dapat diterbitkan.');
                }
                $this->konteks($k->periode_akademik_id, $k->program_studi_id);
                $k->status = 'terbit';
                $k->diterbitkan_at = now('UTC');
            } else {
                if ($k->status === 'batal') {
                    $this->gagal('status', 'Agenda sudah dibatalkan.');
                }
                // Pembatalan tetap dapat dilakukan ketika periode/prodi telah diarsipkan/nonaktif.
                $k->status = 'batal';
                $k->dibatalkan_at = now('UTC');
            }
            $k->catatan_perubahan = $v['alasan'];
            $k->revisi++;
            $k->save();
            $this->audit($k, $userId, $v['aksi'], $sebelum, $v['alasan']);
            return $k;
        }, 3);
    }
    private function pelaku(int $id): void
    {
        $u = User::query()->whereKey($id)->lockForUpdate()->firstOrFail();
        $roles = $u->roles()->orderBy('roles.id')->lockForUpdate()->get(['roles.id', 'roles.kode']);
        abort_unless($u->status === 'aktif' && $roles->contains('kode', 'admin_akademik'), 403);
    }
    private function kunci(KalenderAkademik $bound, string $versi): KalenderAkademik
    {
        $k = KalenderAkademik::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
        if (! hash_equals($k->versiForm(), $versi) || $k->revisi >= 4294967295) {
            $this->gagal('versi', 'Agenda berubah. Muat ulang sebelum menyimpan.');
        }
        return $k;
    }
    private function konteks(int $periodeId, ?int $prodiId): void
    {
        $p = DB::table('periode_akademik')->where('id', $periodeId)->lockForUpdate()->first();
        if (! $p || ! in_array($p->status, ['persiapan', 'aktif'], true)) {
            $this->gagal('periode_akademik_id', 'Pilih periode persiapan/aktif. Periode arsip tidak menerima perubahan agenda.');
        }
        if ($prodiId !== null) {
            $s = DB::table('program_studi')->where('id', $prodiId)->lockForUpdate()->first();
            if (! $s || ! $s->aktif) {
                $this->gagal('program_studi_id', 'Pilih program studi aktif.');
            }
        }
    }
    private function audit(KalenderAkademik $k, int $userId, string $aksi, ?array $sebelum, string $alasan): void
    {
        $a = new AuditLog();
        $a->pelaku_id = $userId;
        $a->entitas = 'kalender_akademik';
        $a->entitas_id = $k->id;
        $a->versi_entitas = $k->revisi;
        $a->aksi = $aksi;
        $a->sebelum = $sebelum;
        $a->sesudah = $k->ringkasanAudit();
        $a->alasan = $alasan;
        $a->waktu = now('UTC');
        $a->save();
    }
    private function gagal(string $key, string $pesan): never
    {
        throw ValidationException::withMessages([$key => $pesan]);
    }
}
