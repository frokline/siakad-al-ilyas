<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Berkas;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AksesPembayaran;
use App\Services\AturanPembayaran;
use App\Services\BuktiPembayaran;
use App\Services\TujuanPembayaran;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class KelolaPembayaran
{
    public function ajukan(int $userId, Tagihan $bound, array $input): Pembayaran
    {
        $v = AturanPembayaran::data($input);
        $hash = hash('sha256', json_encode(['tagihan_id' => (int) $bound->id, 'isi' => $v], JSON_THROW_ON_ERROR));
        $u = User::query()->findOrFail($userId);
        $t = Tagihan::query()->findOrFail($bound->id);
        abort_unless(app(AksesPembayaran::class)->ajukan($u, $t), 403);
        // Retry permintaan sukses tidak perlu mengunduh objek lagi atau membuat record baru.
        $ulang = Pembayaran::query()->where('pengunggah_id', $userId)->where('form_token', $v['form_token'])->first();
        if ($ulang) {
            return $this->ulang($ulang, $hash);
        }
        // I/O cloud DI LUAR transaksi. Metadata diperiksa ulang setelah row terkunci.
        $snapshotBukti = app(BuktiPembayaran::class)->periksa($v['bukti_berkas_id'], $userId);
        try {
            return DB::transaction(function () use ($userId, $bound, $v, $hash, $snapshotBukti): Pembayaran {
                [$u, $petugas] = $this->pelaku($userId);
                $t = Tagihan::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
                $this->izin($u, $petugas, $t);
                $ulang = Pembayaran::query()->where('pengunggah_id', $userId)->where('form_token', $v['form_token'])->lockForUpdate()->first();
                if ($ulang) {
                    return $this->ulang($ulang, $hash);
                }
                if ($t->status !== Tagihan::TERBIT || ! hash_equals($t->versiForm(), $v['versi_tagihan'])) {
                    $this->gagal('versi_tagihan', 'Tagihan berubah atau tidak terbit. Buka ulang formulir.');
                }
                if ($v['nominal_diajukan'] !== $t->nominal) {
                    $this->gagal('nominal_diajukan', 'Pembayaran harus sama dengan nominal penuh satu tagihan.');
                }
                if (! hash_equals(TujuanPembayaran::versi(), $v['versi_tujuan'])) {
                    $this->gagal('tujuan_transfer', 'Rekening tujuan berubah. Periksa kembali sebelum mengajukan.');
                }
                if (Pembayaran::query()->where('tagihan_id', $t->id)->whereIn('status', [Pembayaran::MENUNGGU, Pembayaran::DITERIMA])->lockForUpdate()->first()) {
                    $this->gagal('tagihan', 'Tagihan sudah memiliki pengajuan menunggu atau pembayaran diterima.');
                }
                $b = Berkas::query()->whereKey($v['bukti_berkas_id'])->lockForUpdate()->firstOrFail();
                if ($b->diunggah_oleh !== $userId || $b->status !== Berkas::TERSEDIA || BuktiPembayaran::snapshot($b) !== $snapshotBukti) {
                    $this->gagal('bukti_berkas_id', 'Bukti berubah atau tidak lagi tersedia. Pilih ulang.');
                }
                \App\Services\PrivasiBuktiPembayaran::khususKeuangan($b);
                $riwayatBukti = Pembayaran::query()->where('bukti_sha256', $b->sha256)->orderBy('id')->lockForUpdate()->get(['id', 'tagihan_id']);
                if ($riwayatBukti->contains(fn(Pembayaran $p): bool => $p->tagihan_id !== (int) $t->id)) {
                    $this->gagal('bukti_berkas_id', 'Bukti yang sama telah dikaitkan dengan tagihan lain.');
                }
                $p = new Pembayaran();
                $p->tagihan_id = $t->id;
                $p->pengunggah_id = $userId;
                $p->bukti_berkas_id = $b->id;
                $p->nomor_pengajuan = 'BYR-' . strtoupper((string) Str::ulid());
                $p->nominal_diajukan = $v['nominal_diajukan'];
                $p->tanggal_transfer = $v['tanggal_transfer'];
                $p->referensi_bank = $v['referensi_bank'];
                $p->tujuan_transfer = TujuanPembayaran::teks();
                $p->tagihan_snapshot = $t->only([
                    'nomor',
                    'mahasiswa_id',
                    'registrasi_semester_id',
                    'jenis_biaya_id',
                    'tahun_tagihan',
                    'bulan_tagihan',
                    'nominal',
                    'jatuh_tempo',
                    'snapshot',
                    'revisi'
                ]);
                $p->bukti_snapshot = $snapshotBukti;
                $p->bukti_sha256 = $b->sha256;
                $p->status = Pembayaran::MENUNGGU;
                $p->tagihan_aktif_id = $t->id;
                $p->bukti_aktif_sha256 = $b->sha256;
                $p->form_token = $v['form_token'];
                $p->hash_permohonan = $hash;
                $p->revisi = 1;
                $p->diajukan_at = now('UTC');
                $p->save();
                $this->audit($p, $userId, 'ajukan', null, 'Pengajuan bukti transfer.');
                return $p;
            }, 3);
        } catch (UniqueConstraintViolationException $e) {
            if (Pembayaran::query()->where('tagihan_aktif_id', $bound->id)->orWhere('bukti_aktif_sha256', $snapshotBukti['sha256'])->exists()) {
                $this->gagal('pembayaran', 'Pengajuan bersamaan sudah tercatat. Muat ulang riwayat sebelum mencoba kembali.');
            }
            throw $e;
        }
    }
    public function batalkan(int $userId, Pembayaran $bound, array $data): Pembayaran
    {
        $v = Validator::make($data, [
            'versi' => ['required', 'string', 'regex:/\A[a-f0-9]{64}\z/'],
            'alasan' => ['required', 'string', 'min:10', 'max:1000', 'regex:/\S.{8,}\S/s'],
            'konfirmasi' => ['required', 'accepted']
        ])->validate();
        return DB::transaction(function () use ($userId, $bound, $v): Pembayaran {
            [$u, $petugas] = $this->pelaku($userId);
            // Urutan yang sama wajib digunakan modul verifikasi: tagihan -> pembayaran -> berkas bila perlu.
            $t = Tagihan::query()->whereKey($bound->tagihan_id)->lockForUpdate()->firstOrFail();
            $this->izin($u, $petugas, $t);
            $p = Pembayaran::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
            if ($p->tagihan_id !== (int) $t->id || ! hash_equals($p->versiForm(), $v['versi']) || $p->revisi >= 4294967295) {
                $this->gagal('versi', 'Pengajuan berubah. Muat ulang detail.');
            }
            if ($p->status !== Pembayaran::MENUNGGU) {
                $this->gagal('status', 'Hanya pengajuan menunggu dapat dibatalkan.');
            }
            $sebelum = $p->ringkasanAudit();
            $p->status = Pembayaran::DIBATALKAN;
            $p->tagihan_aktif_id = null;
            $p->bukti_aktif_sha256 = null;
            $p->dibatalkan_at = now('UTC');
            $p->alasan_batal = trim($v['alasan']);
            $p->revisi++;
            $p->save();
            $this->audit($p, $userId, 'batalkan', $sebelum, $v['alasan']);
            return $p;
        }, 3);
    }
    private function pelaku(int $id): array
    {
        $u = User::query()->whereKey($id)->lockForUpdate()->firstOrFail();
        $roles = $u->roles()->orderBy('roles.id')->lockForUpdate()->get(['roles.id', 'roles.kode']);
        abort_unless($u->status === 'aktif', 403);
        return [$u, $roles->contains('kode', 'admin_keuangan')];
    }
    private function izin(User $u, bool $petugas, Tagihan $t): void
    {
        $m = DB::table('mahasiswa')->where('id', $t->mahasiswa_id)->lockForUpdate()->first();
        abort_unless($petugas || ($m && (int) $m->user_id === (int) $u->id), 403);
    }
    private function ulang(Pembayaran $p, string $hash): Pembayaran
    {
        if (! hash_equals($p->hash_permohonan, $hash)) {
            $this->gagal('form_token', 'Formulir sudah dipakai untuk isi berbeda. Buka formulir baru.');
        }
        return $p;
    }
    private function audit(Pembayaran $p, int $userId, string $aksi, ?array $sebelum, string $alasan): void
    {
        $a = new AuditLog();
        $a->pelaku_id = $userId;
        $a->entitas = 'pembayaran';
        $a->entitas_id = $p->id;
        $a->versi_entitas = $p->revisi;
        $a->aksi = $aksi;
        $a->sebelum = $sebelum;
        $a->sesudah = $p->ringkasanAudit();
        $a->alasan = $alasan;
        $a->waktu = now('UTC');
        $a->save();
    }
    private function gagal(string $key, string $pesan): never
    {
        throw ValidationException::withMessages([$key => $pesan]);
    }
}
