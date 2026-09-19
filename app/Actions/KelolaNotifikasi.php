<?php

namespace App\Actions;

use App\Models\{Notifikasi, User};
use App\Services\{AksesNotifikasi, SumberNotifikasi};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class KelolaNotifikasi
{
    public function __construct(private SumberNotifikasi $sumber, private AksesNotifikasi $akses) {}
    // Hanya layanan server/command. Tidak ada endpoint kirim dari browser.
    public function kirim(int $penerimaId, string $jenis, int $sumberId): bool
    {
        $def = $this->sumber->definisi($jenis);
        if ($penerimaId < 1 || $sumberId < 1) {
            throw new \InvalidArgumentException('ID harus positif.');
        }
        return DB::transaction(function () use ($penerimaId, $jenis, $sumberId, $def): bool {
            $u = $this->pengguna($penerimaId);
            if (! $u || ! $this->akses->masuk($u)) {
                return false;
            }
            // Sumber terbaru diperiksa lagi; hasil pemindaian bukan otorisasi final.
            $s = $this->sumber->query($jenis, $u)->whereKey($sumberId)->lockForUpdate()->first();
            if (! $s) {
                return false;
            }
            // KRS lama menggunakan kolom versi, modul lain menggunakan revisi.
            $revisi = (int) $s->getAttribute($jenis === 'krs' ? 'versi' : 'revisi');
            if ($revisi < 1 || $revisi > 4294967295) {
                throw new \LogicException('Revisi sumber tidak valid.');
            }
            $key = Notifikasi::kunci($jenis, $sumberId, $revisi);
            if (Notifikasi::query()->where('penerima_id', $u->id)->where('kunci_peristiwa', $key)->lockForUpdate()->exists()) {
                return false;
            }
            $n = new Notifikasi();
            $n->forceFill([
                'penerima_id' => $u->id,
                'jenis' => $jenis,
                'judul' => $def['judul'],
                'sumber_tabel' => $def['tabel'],
                'sumber_id' => $sumberId,
                'sumber_revisi' => $revisi,
                'kunci_peristiwa' => $key,
                'dibaca_at' => null
            ])->save();
            return true;
        }, 3);
    }
    public function baca(int $aktor, int $id, bool $buka = false): ?string
    {
        return DB::transaction(function () use ($aktor, $id, $buka): ?string {
            $u = $this->pengguna($aktor);
            abort_unless($u && $this->akses->masuk($u), 403);
            $n = Notifikasi::query()->where('penerima_id', $u->id)->whereKey($id)->lockForUpdate()->first();
            abort_unless($n && $this->akses->lihat($u, $n), 404);
            // Susun tujuan sebelum mutasi; route yang belum terpasang tidak menandai dibaca.
            $tujuan = $buka ? $this->sumber->tujuan($n->jenis, $n->sumber_id) : null;
            if ($n->dibaca_at === null) {
                $n->dibaca_at = now('UTC');
                $n->save();
            }
            return $tujuan;
        }, 3);
    }
    public function bacaHalaman(int $aktor, array $input): int
    {
        $v = Validator::make($input, [
            'ids' => ['required', 'array', 'min:1', 'max:20'],
            'ids.*' => ['required', 'integer', 'min:1', 'distinct']
        ])->validate();
        $ids = array_map('intval', $v['ids']);
        sort($ids);
        return DB::transaction(function () use ($aktor, $ids): int {
            $u = $this->pengguna($aktor);
            abort_unless($u && $this->akses->masuk($u), 403);
            $rows = $this->akses->batasi(Notifikasi::query(), $u)->whereIn('notifikasi.id', $ids)->orderBy('notifikasi.id')->lockForUpdate()->get();
            if ($rows->count() !== count($ids)) {
                throw ValidationException::withMessages(['ids' => 'Daftar atau akses sudah berubah. Muat ulang halaman; tidak ada yang ditandai.']);
            }
            $jumlah = 0;
            foreach ($rows as $n) {
                if ($n->dibaca_at === null) {
                    $n->dibaca_at = now('UTC');
                    $n->save();
                    $jumlah++;
                }
            }
            return $jumlah;
        }, 3);
    }
    private function pengguna(int $id): ?User
    {
        $u = User::query()->whereKey($id)->lockForUpdate()->first();
        if ($u) {
            DB::table('user_roles')->where('user_id', $id)->orderBy('role_id')->lockForUpdate()->get();
            $u->roles()->orderBy('roles.id')->lockForUpdate()->get();
        }
        return $u;
    }
}
