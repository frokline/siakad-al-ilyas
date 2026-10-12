<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Berkas;
use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\Materi;
use App\Models\MateriBerkas;
use App\Models\PengajarKelas;
use App\Models\PeriodeAkademik;
use App\Models\Pertemuan;
use App\Models\Rombel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class KelolaMateri
{
    // Data berasal dari MateriRequest / MateriStatusRequest, bukan request()->all().
    public function buat(int $userId, array $data): Materi
    {
        return DB::transaction(function () use ($userId, $data): Materi {
            [$user, $kelas] = $this->kunciKonteks($userId, (int) $data['kelas_kuliah_id']);
            Gate::forUser($user)->authorize('create', [Materi::class, $kelas]);
            $ids = $this->ids($data);
            $hash = hash('sha256', json_encode([
                'kelas' => $kelas->id,
                'pertemuan' => ! empty($data['pertemuan_id']) ? (int) $data['pertemuan_id'] : null,
                'judul' => $data['judul'],
                'isi' => $data['isi'] ?? null,
                'tautan' => $data['tautan_eksternal'] ?? null,
                'berkas' => $ids,
            ], JSON_THROW_ON_ERROR));
            $ada = Materi::query()->where('pembuat_id', $userId)->where('form_token', $data['form_token'])->lockForUpdate()->first();
            if ($ada !== null) {
                if (! hash_equals($ada->hash_permohonan, $hash)) {
                    $this->gagal('Formulir ini sudah dipakai untuk data lain. Buka formulir baru.');
                }
                return $ada;
            }
            $this->pertemuan($kelas->id, $data['pertemuan_id'] ?? null);
            $m = new Materi();
            $m->kelas_kuliah_id = $kelas->id;
            $m->pembuat_id = $user->id;
            $m->form_token = $data['form_token'];
            $m->hash_permohonan = $hash;
            $this->isi($m, $data);
            $m->status = Materi::DRAF;
            $m->revisi = 1;
            $m->save();
            $this->lampiran($m, $ids, $userId);
            $this->audit($m, $userId, 'buat', null, null);
            return $m;
        }, 3);
    }

    public function ubah(int $userId, Materi $bound, array $data): Materi
    {
        return DB::transaction(function () use ($userId, $bound, $data): Materi {
            [$user] = $this->kunciKonteks($userId, $bound->kelas_kuliah_id);
            $m = Materi::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($user)->authorize('update', $m);
            $this->versi($m, $data);
            $this->pertemuan($m->kelas_kuliah_id, $data['pertemuan_id'] ?? null);
            $sebelum = $m->ringkasanAudit();
            $this->lampiran($m, $this->ids($data), $userId);
            $this->isi($m, $data);
            $m->revisi++;
            $m->save();
            $this->audit($m, $userId, 'ubah', $sebelum, $data['alasan']);
            return $m;
        }, 3);
    }

    public function status(string $aksi, int $userId, Materi $bound, array $data): Materi
    {
        abort_unless(in_array($aksi, ['terbitkan', 'tarik', 'arsipkan', 'pulihkan'], true), 404);
        return DB::transaction(function () use ($aksi, $userId, $bound, $data): Materi {
            [$user] = $this->kunciKonteks($userId, $bound->kelas_kuliah_id);
            $m = Materi::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($user)->authorize($aksi, $m);
            $this->versi($m, $data);
            $sebelum = $m->ringkasanAudit();
            if ($aksi === 'terbitkan') {
                $this->pertemuan($m->kelas_kuliah_id, $m->pertemuan_id);
                $ids = $m->lampiran()->orderBy('berkas_id')->pluck('berkas_id')->map(fn($id) => (int) $id)->all();
                $files = Berkas::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
                if ($files->count() !== count($ids) || $files->contains(fn(Berkas $b) => $b->status !== Berkas::TERSEDIA)) {
                    $this->gagal('Semua lampiran harus tersedia sebelum materi diterbitkan.');
                }
                if (! app()->environment(['local', 'testing']) && $files->contains(fn(Berkas $b) => $b->pemeriksaan !== 'clamav')) {
                    $this->gagal('Lampiran produksi harus sudah melalui pemindaian antivirus.');
                }
                if (blank($m->isi) && blank($m->tautan_eksternal) && $ids === []) {
                    $this->gagal('Materi harus berisi uraian, tautan, atau minimal satu lampiran.');
                }
                $m->status = Materi::TERBIT;
                $m->terbit_at = now('UTC')->startOfSecond();
            } elseif ($aksi === 'arsipkan') {
                $m->status = Materi::ARSIP;
                $m->diarsipkan_at = now('UTC')->startOfSecond();
            } else {
                $m->status = Materi::DRAF;
                $m->terbit_at = null;
                $m->diarsipkan_at = null;
            }
            $m->revisi++;
            $m->save();
            $this->audit($m, $userId, $aksi, $sebelum, $data['alasan']);
            return $m;
        }, 3);
    }

    private function kunciKonteks(int $userId, int $kelasId): array
    {
        $user = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
        abort_unless($user->status === 'aktif', 403);
        $roles = $user->roles()->orderBy('roles.id')->lockForUpdate()->get(['roles.id', 'roles.kode']);
        $admin = $roles->contains('kode', 'admin_akademik');
        abort_unless($admin || $roles->contains('kode', 'dosen'), 403);
        $petunjuk = KelasKuliah::query()->with('rombel')->findOrFail($kelasId);
        PeriodeAkademik::query()->whereKey($petunjuk->rombel->periode_akademik_id)->lockForUpdate()->firstOrFail();
        Rombel::query()->whereKey($petunjuk->rombel_id)->lockForUpdate()->firstOrFail();
        $kelas = KelasKuliah::query()->whereKey($kelasId)->lockForUpdate()->firstOrFail();
        $tim = PengajarKelas::query()->where('kelas_kuliah_id', $kelasId)->orderBy('id')->lockForUpdate()->get();
        if (! $admin) {
            $dosen = Dosen::query()->where('user_id', $userId)->lockForUpdate()->first();
            abort_unless($dosen && $dosen->status === 'aktif'
                && $tim->contains(fn(PengajarKelas $p) => $p->aktif && $p->dosen_id === $dosen->id), 403);
        }
        return [$user, $kelas];
    }
    private function isi(Materi $m, array $data): void
    {
        $m->judul = $data['judul'];
        $m->isi = $data['isi'] ?? null;
        $m->tautan_eksternal = $data['tautan_eksternal'] ?? null;
        $m->pertemuan_id = ! empty($data['pertemuan_id']) ? (int) $data['pertemuan_id'] : null;
    }
    private function ids(array $data): array
    {
        $ids = array_map('intval', $data['berkas_ids'] ?? []);
        sort($ids, SORT_NUMERIC);
        if (
            count($ids) > (int) config('materi.maks_lampiran', 10) || count(array_unique($ids)) !== count($ids)
            || ($ids !== [] && min($ids) < 1)
        ) {
            $this->gagal('Daftar lampiran tidak valid.');
        }
        return $ids;
    }
    private function pertemuan(int $kelasId, mixed $id): void
    {
        if ($id === null || $id === '') {
            return;
        }
        $sesi = Pertemuan::query()->whereKey((int) $id)->where('kelas_kuliah_id', $kelasId)->lockForUpdate()->first();
        if (! $sesi || $sesi->status === 'batal') {
            $this->gagal('Pertemuan harus berasal dari kelas ini dan tidak dibatalkan.');
        }
    }
    private function versi(Materi $m, array $data): void
    {
        if (! is_string($data['versi'] ?? null) || ! hash_equals($m->versiForm(), $data['versi'])) {
            $this->gagal('Data sudah berubah. Muat ulang halaman sebelum menyimpan.');
        }
        if ($m->revisi >= 4294967295) {
            $this->gagal('Batas revisi materi tercapai.');
        }
    }
    private function lampiran(Materi $m, array $ids, int $userId): void
    {
        // Semua penulis harus mengunci materi lalu berkas berurutan, sebelum mengubah relasi.
        $lama = $m->semuaLampiran()->get()->keyBy('berkas_id');
        $seluruh = array_values(array_unique([...$ids, ...$lama->keys()->map(fn($id) => (int) $id)->all()]));
        sort($seluruh, SORT_NUMERIC);
        $files = Berkas::query()->whereIn('id', $seluruh)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        foreach ($ids as $id) {
            $file = $files->get($id);
            $row = $lama->get($id);
            // Lampiran tim yang masih aktif boleh dipertahankan. Lampiran baru/diaktifkan ulang wajib milik pelaku.
            if (
                ! $file || $file->status !== Berkas::TERSEDIA
                || (! ($row && $row->aktif) && $file->diunggah_oleh !== $userId)
            ) {
                $this->gagal('Lampiran tidak tersedia atau tidak boleh dipasang oleh akun Anda.');
            }
            if (! $row) {
                $row = new MateriBerkas();
                $row->materi_id = $m->id;
                $row->berkas_id = $id;
                $row->aktif = true;
                $row->dilepas_at = null;
                $row->save();
            } elseif (! $row->aktif) {
                $row->aktif = true;
                $row->dilepas_at = null;
                $row->save();
            }
        }
        foreach ($lama as $row) {
            if ($row->aktif && ! in_array($row->berkas_id, $ids, true)) {
                $row->aktif = false;
                $row->dilepas_at = now('UTC');
                $row->save();
            }
        }
    }
    private function audit(Materi $m, int $userId, string $aksi, ?array $sebelum, ?string $alasan): void
    {
        $a = new AuditLog();
        $a->pelaku_id = $userId;
        $a->entitas = 'materi';
        $a->entitas_id = $m->id;
        $a->versi_entitas = $m->revisi;
        $a->aksi = $aksi;
        $a->sebelum = $sebelum;
        $a->sesudah = $m->ringkasanAudit();
        $a->alasan = $alasan;
        $a->waktu = now('UTC');
        $a->save();
    }
    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['materi' => $pesan]);
    }
}
