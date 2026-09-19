<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\JenisSurat;
use App\Models\User;
use App\Services\AturanJenisSurat;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class KelolaJenisSurat
{
    public function buat(int $userId, array $data): JenisSurat
    {
        $data = AturanJenisSurat::normalisasi($data);
        $isi = AturanJenisSurat::validasiIsi($data, true);
        $isi = ['kode' => $isi['kode'], 'nama' => $isi['nama'], 'syarat' => $isi['syarat'] ?? null];
        Validator::make($data, ['form_token' => ['required', 'uuid'], 'alasan' => ['nullable', 'string', 'max:1000']])->validate();
        $hash = hash('sha256', json_encode($isi, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        try {
            return DB::transaction(function () use ($userId, $data, $isi, $hash): JenisSurat {
                $this->kunciPelaku($userId);
                $lama = JenisSurat::query()->where('pembuat_id', $userId)->where('form_token', $data['form_token'])->lockForUpdate()->first();
                if ($lama) {
                    if (! hash_equals($lama->hash_permohonan, $hash)) {
                        $this->gagal('form_token', 'Formulir sudah digunakan untuk isi berbeda. Buka formulir baru.');
                    }
                    return $lama;
                }
                if (JenisSurat::query()->where('kode', $isi['kode'])->lockForUpdate()->first()) {
                    $this->gagal('kode', 'Kode sudah ada, termasuk jenis surat nonaktif. Gunakan data lama atau kode lain.');
                }
                $j = new JenisSurat();
                $j->kode = $isi['kode'];
                $j->nama = $isi['nama'];
                $j->syarat = $isi['syarat'];
                $j->aktif = true;
                $j->dinonaktifkan_at = null;
                $j->pembuat_id = $userId;
                $j->form_token = $data['form_token'];
                $j->hash_permohonan = $hash;
                $j->revisi = 1;
                $j->save();
                $this->audit($j, $userId, 'buat', null, $data['alasan'] ?? null);
                return $j;
            }, 3);
        } catch (UniqueConstraintViolationException $e) {
            // Penjaga terakhir jika dua penulis bersaing pada kode yang sama. Transaksi sudah rollback.
            if (JenisSurat::query()->where('kode', $isi['kode'])->exists()) {
                $this->gagal('kode', 'Kode sudah digunakan. Muat ulang daftar jenis surat.');
            }
            throw $e;
        }
    }
    public function ubah(int $userId, JenisSurat $bound, array $data): JenisSurat
    {
        $data = AturanJenisSurat::normalisasi($data);
        $isi = AturanJenisSurat::validasiIsi($data, false);
        $this->validasiPerubahan($data);
        return DB::transaction(function () use ($userId, $bound, $data, $isi): JenisSurat {
            $this->kunciPelaku($userId);
            $j = JenisSurat::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
            if (! $j->aktif) {
                $this->gagal('jenis_surat', 'Aktifkan kembali jenis surat sebelum mengubah syaratnya.');
            }
            $this->versi($j, $data['versi']);
            $ket = $isi['syarat'] ?? null;
            if ($j->nama === $isi['nama'] && $j->syarat === $ket) {
                return $j;
            }
            $sebelum = $j->ringkasanAudit();
            $j->nama = $isi['nama'];
            $j->syarat = $ket;
            $j->revisi++;
            $j->save();
            $this->audit($j, $userId, 'ubah', $sebelum, $data['alasan']);
            return $j;
        }, 3);
    }
    public function status(int $userId, JenisSurat $bound, bool $aktif, array $data): JenisSurat
    {
        $data = AturanJenisSurat::normalisasi($data);
        $this->validasiPerubahan($data);
        Validator::make($data, ['konfirmasi' => ['required', 'accepted']])->validate();
        return DB::transaction(function () use ($userId, $bound, $aktif, $data): JenisSurat {
            $this->kunciPelaku($userId);
            $j = JenisSurat::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
            $this->versi($j, $data['versi']);
            if ($j->aktif === $aktif) {
                return $j;
            }
            $sebelum = $j->ringkasanAudit();
            $j->aktif = $aktif;
            $j->dinonaktifkan_at = $aktif ? null : now('UTC');
            $j->revisi++;
            $j->save();
            $this->audit($j, $userId, $aktif ? 'aktifkan' : 'nonaktifkan', $sebelum, $data['alasan']);
            return $j;
        }, 3);
    }
    private function kunciPelaku(int $userId): void
    {
        $u = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
        $roles = $u->roles()->orderBy('roles.id')->lockForUpdate()->get(['roles.id', 'roles.kode']);
        abort_unless($u->status === 'aktif' && $roles->contains('kode', 'admin_akademik'), 403);
    }
    private function validasiPerubahan(array $data): void
    {
        Validator::make($data, [
            'versi' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000']
        ])->validate();
    }
    private function versi(JenisSurat $j, string $versi): void
    {
        if (! hash_equals($j->versiForm(), $versi) || $j->revisi >= 4294967295) {
            $this->gagal('versi', 'Data telah berubah atau batas revisi tercapai. Muat ulang sebelum menyimpan.');
        }
    }
    private function audit(JenisSurat $j, int $pelaku, string $aksi, ?array $sebelum, ?string $alasan): void
    {
        $a = new AuditLog();
        $a->pelaku_id = $pelaku;
        $a->entitas = 'jenis_surat';
        $a->entitas_id = $j->id;
        $a->versi_entitas = $j->revisi;
        $a->aksi = $aksi;
        $a->sebelum = $sebelum;
        $a->sesudah = $j->ringkasanAudit();
        $a->alasan = $alasan;
        $a->waktu = now('UTC');
        $a->save();
    }
    private function gagal(string $field, string $pesan): never
    {
        throw ValidationException::withMessages([$field => $pesan]);
    }
}
