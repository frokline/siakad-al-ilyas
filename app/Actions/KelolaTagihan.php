<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AturanTagihan;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class KelolaTagihan
{
    public function buat(int $userId, array $data): Tagihan
    {
        $isi = AturanTagihan::isi($data, true);
        $kontrol = AturanTagihan::kontrol($data, true);
        $hash = hash('sha256', json_encode($isi, JSON_THROW_ON_ERROR));
        try {
            return DB::transaction(function () use ($userId, $isi, $kontrol, $hash): Tagihan {
                $this->pelaku($userId);
                $ulang = Tagihan::query()->where('pembuat_id', $userId)->where('form_token', $kontrol['form_token'])->lockForUpdate()->first();
                if ($ulang) {
                    if (! hash_equals($ulang->hash_permohonan, $hash)) {
                        $this->gagal('form_token', 'Formulir telah digunakan dengan isi berbeda. Buka formulir baru.');
                    }
                    return $ulang;
                }
                $k = $this->konteks($isi['registrasi_semester_id'], $isi['jenis_biaya_id']);
                if (Tagihan::query()->where('mahasiswa_id', $k['mahasiswa']->id)->where('jenis_biaya_id', $isi['jenis_biaya_id'])
                    ->where('tahun_tagihan', $isi['tahun_tagihan'])->where('bulan_tagihan', $isi['bulan_tagihan'])->lockForUpdate()->exists()
                ) {
                    $this->gagal('bulan_tagihan', 'Tagihan bulan ini sudah ada, termasuk draf/dibatalkan. Gunakan record lama.');
                }
                $t = new Tagihan();
                foreach ($isi as $key => $value) {
                    $t->{$key} = $value;
                }
                $t->mahasiswa_id = $k['mahasiswa']->id;
                $t->nomor = 'TGH-' . strtoupper((string) Str::ulid());
                $t->status = Tagihan::DRAF;
                $t->revisi = 1;
                $t->pembuat_id = $userId;
                $t->form_token = $kontrol['form_token'];
                $t->hash_permohonan = $hash;
                $t->save();
                $this->audit($t, $userId, 'buat', null, $kontrol['alasan']);
                return $t;
            }, 3);
        } catch (UniqueConstraintViolationException $e) {
            // FK/masalah skema lain tidak disamarkan. Konflik unik dilaporkan tanpa membuka data mahasiswa lain.
            $this->gagal('bulan_tagihan', 'Konflik nomor, formulir, atau tagihan bulanan. Muat ulang daftar; jangan membuat duplikat.');
        }
    }
    public function ubah(int $userId, Tagihan $bound, array $data): Tagihan
    {
        $isi = AturanTagihan::isi($data, false);
        $kontrol = AturanTagihan::kontrol($data, false);
        return DB::transaction(function () use ($userId, $bound, $isi, $kontrol): Tagihan {
            $this->pelaku($userId);
            $t = $this->kunci($bound, $kontrol['versi']);
            if ($t->status !== Tagihan::DRAF) {
                $this->gagal('status', 'Hanya draf dapat diedit.');
            }
            $this->tanpaPembayaran($t);
            if ($t->nominal === $isi['nominal'] && $t->jatuh_tempo->format('Y-m-d') === $isi['jatuh_tempo'] && $t->catatan === $isi['catatan']) {
                return $t;
            }
            $sebelum = $t->ringkasanAudit();
            foreach ($isi as $key => $value) {
                $t->{$key} = $value;
            }
            $t->revisi++;
            $t->save();
            $this->audit($t, $userId, 'ubah_draf', $sebelum, $kontrol['alasan']);
            return $t;
        }, 3);
    }
    public function transisi(int $userId, Tagihan $bound, string $aksi, array $data): Tagihan
    {
        $kontrol = AturanTagihan::kontrol($data, false);
        if (! in_array($aksi, ['terbitkan', 'batalkan', 'buka_draf'], true)) {
            $this->gagal('aksi', 'Tindakan tidak dikenal.');
        }
        return DB::transaction(function () use ($userId, $bound, $aksi, $kontrol): Tagihan {
            $this->pelaku($userId);
            $t = $this->kunci($bound, $kontrol['versi']);
            $this->tanpaPembayaran($t);
            $sebelum = $t->ringkasanAudit();
            if ($aksi === 'terbitkan') {
                if ($t->status !== Tagihan::DRAF) {
                    $this->gagal('status', 'Hanya draf dapat diterbitkan.');
                }
                $k = $this->konteks($t->registrasi_semester_id, $t->jenis_biaya_id);
                if ((int) $k['mahasiswa']->id !== $t->mahasiswa_id) {
                    $this->gagal('registrasi_semester_id', 'Pemilik registrasi berubah. Penerbitan dihentikan.');
                }
                $t->snapshot = [
                    'nim' => $k['mahasiswa']->nim,
                    'nama' => $k['akun']->nama,
                    'jenis_kode' => $k['jenis']->kode,
                    'jenis_nama' => $k['jenis']->nama,
                    'periode_akademik_id' => (int) $k['registrasi']->periode_akademik_id,
                    'semester_studi' => (int) $k['registrasi']->semester_studi
                ];
                $t->status = Tagihan::TERBIT;
                $t->diterbitkan_at = now('UTC');
                $t->dibatalkan_at = null;
            } elseif ($aksi === 'batalkan') {
                if ($t->status === Tagihan::DIBATALKAN) {
                    $this->gagal('status', 'Tagihan sudah dibatalkan.');
                }
                $t->status = Tagihan::DIBATALKAN;
                $t->dibatalkan_at = now('UTC');
            } else {
                if ($t->status !== Tagihan::DIBATALKAN) {
                    $this->gagal('status', 'Batalkan tagihan terlebih dahulu.');
                }
                $t->status = Tagihan::DRAF;
                $t->snapshot = null;
                $t->diterbitkan_at = null;
                $t->dibatalkan_at = null;
            }
            $t->revisi++;
            $t->save();
            $this->audit($t, $userId, $aksi, $sebelum, $kontrol['alasan']);
            return $t;
        }, 3);
    }
    private function pelaku(int $id): void
    {
        $u = User::query()->whereKey($id)->lockForUpdate()->firstOrFail();
        $roles = $u->roles()->orderBy('roles.id')->lockForUpdate()->get(['roles.id', 'roles.kode']);
        abort_unless($u->status === 'aktif' && $roles->contains('kode', 'admin_keuangan'), 403);
    }
    private function kunci(Tagihan $bound, string $versi): Tagihan
    {
        $t = Tagihan::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
        if (! hash_equals($t->versiForm(), $versi) || $t->revisi >= 4294967295) {
            $this->gagal('versi', 'Tagihan telah berubah. Muat ulang sebelum melanjutkan.');
        }
        return $t;
    }
    private function konteks(int $registrasiId, int $jenisId): array
    {
        $r = DB::table('registrasi_semester')->where('id', $registrasiId)->lockForUpdate()->first();
        if (! $r || ! in_array($r->status, ['terdaftar', 'aktif'], true)) {
            $this->gagal('registrasi_semester_id', 'Pilih registrasi terdaftar/aktif yang valid.');
        }
        $s = DB::table('riwayat_studi')->where('id', $r->riwayat_studi_id)->lockForUpdate()->first();
        $m = $s ? DB::table('mahasiswa')->where('id', $s->mahasiswa_id)->lockForUpdate()->first() : null;
        $u = $m ? DB::table('users')->where('id', $m->user_id)->lockForUpdate()->first() : null;
        $j = DB::table('jenis_biaya')->where('id', $jenisId)->lockForUpdate()->first();
        if (! $s || ! $m || ! $u) {
            $this->gagal('registrasi_semester_id', 'Relasi mahasiswa tidak lengkap.');
        }
        if (! $j || ! $j->aktif) {
            $this->gagal('jenis_biaya_id', 'Jenis biaya harus aktif.');
        }
        // Tidak memaksa periode terkini/akun mahasiswa aktif: tagihan historis tetap dapat dicatat.
        return ['registrasi' => $r, 'mahasiswa' => $m, 'akun' => $u, 'jenis' => $j];
    }
    private function tanpaPembayaran(Tagihan $t): void
    {
        // Modul pembayaran belum dibuat. Saat tersedia, semua histori pembayaran memblokir koreksi ini.
        if (! Schema::hasTable('pembayaran')) {
            return;
        }
        if (! Schema::hasColumn('pembayaran', 'tagihan_id')) {
            throw new \LogicException('Struktur pembayaran belum sesuai; perubahan tagihan dihentikan.');
        }
        if (DB::table('pembayaran')->where('tagihan_id', $t->id)->lockForUpdate()->first()) {
            $this->gagal('pembayaran', 'Tagihan memiliki riwayat pembayaran. Koreksi harus melalui prosedur keuangan khusus.');
        }
    }
    private function audit(Tagihan $t, int $userId, string $aksi, ?array $sebelum, string $alasan): void
    {
        $a = new AuditLog();
        $a->pelaku_id = $userId;
        $a->entitas = 'tagihan';
        $a->entitas_id = $t->id;
        $a->versi_entitas = $t->revisi;
        $a->aksi = $aksi;
        $a->sebelum = $sebelum;
        $a->sesudah = $t->ringkasanAudit();
        $a->alasan = trim($alasan);
        $a->waktu = now('UTC');
        $a->save();
    }
    private function gagal(string $key, string $pesan): never
    {
        throw ValidationException::withMessages([$key => $pesan]);
    }
}
