<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Berkas;
use App\Models\Dosen;
use App\Models\Kegiatan;
use App\Models\KegiatanBerkas;
use App\Models\KelasKuliah;
use App\Models\PengajarKelas;
use App\Models\PeriodeAkademik;
use App\Models\Pertemuan;
use App\Models\Rombel;
use App\Models\User;
use App\Services\WaktuKegiatan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class KelolaKegiatan
{
    public function buat(int $userId, array $data): Kegiatan
    {
        return DB::transaction(function () use ($userId, $data): Kegiatan {
            [$user, $kelas] = $this->kunciKonteks($userId, (int) $data['kelas_kuliah_id']);
            Gate::forUser($user)->authorize('create', [Kegiatan::class, $kelas]);
            $isi = $this->dataIsi($data);
            $ids = $this->ids($data);
            $hash = hash('sha256', json_encode(['kelas' => $kelas->id, 'isi' => $isi, 'berkas' => $ids], JSON_THROW_ON_ERROR));
            $ada = Kegiatan::query()->where('pembuat_id', $userId)->where('form_token', $data['form_token'])->lockForUpdate()->first();
            if ($ada) {
                if (! hash_equals($ada->hash_permohonan, $hash)) {
                    $this->gagal('Formulir ini sudah digunakan untuk data berbeda. Buka formulir baru.');
                }
                return $ada;
            }
            $this->pertemuan($kelas->id, $isi['pertemuan_id']);
            $k = new Kegiatan();
            $k->kelas_kuliah_id = $kelas->id;
            $k->pembuat_id = $userId;
            $k->form_token = $data['form_token'];
            $k->hash_permohonan = $hash;
            foreach ($isi as $field => $value) {
                $k->{$field} = $value;
            }
            $k->status = Kegiatan::DRAF;
            $k->revisi = 1;
            $k->save();
            $this->lampiran($k, $ids, $userId);
            $this->audit($k, $userId, 'buat', null, null);
            return $k;
        }, 3);
    }
    public function ubah(int $userId, Kegiatan $bound, array $data): Kegiatan
    {
        return DB::transaction(function () use ($userId, $bound, $data): Kegiatan {
            [$user] = $this->kunciKonteks($userId, $bound->kelas_kuliah_id);
            $k = Kegiatan::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($user)->authorize('update', $k);
            $this->versi($k, $data);
            $isi = $this->dataIsi($data);
            $this->pertemuan($k->kelas_kuliah_id, $isi['pertemuan_id']);
            $sebelum = $k->ringkasanAudit();
            $this->lampiran($k, $this->ids($data), $userId);
            foreach ($isi as $field => $value) {
                $k->{$field} = $value;
            }
            $k->revisi++;
            $k->save();
            $this->audit($k, $userId, 'ubah', $sebelum, $data['alasan']);
            return $k;
        }, 3);
    }
    public function status(string $aksi, int $userId, Kegiatan $bound, array $data): Kegiatan
    {
        abort_unless(in_array($aksi, ['terbitkan', 'tutup', 'bukaKembali', 'perpanjang', 'arsipkan', 'pulihkan'], true), 404);
        return DB::transaction(function () use ($aksi, $userId, $bound, $data): Kegiatan {
            [$user] = $this->kunciKonteks($userId, $bound->kelas_kuliah_id);
            $k = Kegiatan::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($user)->authorize($aksi, $k);
            $this->versi($k, $data);
            $sebelum = $k->ringkasanAudit();
            $waktu = now('UTC')->startOfSecond();
            if (in_array($aksi, ['terbitkan', 'bukaKembali'], true)) {
                $this->pertemuan($k->kelas_kuliah_id, $k->pertemuan_id);
                if (! $k->tenggat_at->gt($waktu)) {
                    $this->gagal('Tenggat sudah lewat. Perbaiki jadwal draf atau perpanjang tenggat sebelum membuka kembali.');
                }
                $this->lampiranSiap($k);
                $k->status = Kegiatan::TERBIT;
                if ($k->terbit_at === null) {
                    $k->terbit_at = $waktu;
                }
                $k->ditutup_at = null;
            } elseif ($aksi === 'tutup') {
                $k->status = Kegiatan::DITUTUP;
                $k->ditutup_at = $waktu;
            } elseif ($aksi === 'perpanjang') {
                $baru = WaktuKegiatan::dariForm($data['tenggat_baru'] ?? null, 'tenggat_baru');
                if (! $baru->gt($k->tenggat_at) || ! $baru->gt($waktu)) {
                    $this->gagal('Tenggat baru harus lebih akhir daripada tenggat lama dan waktu server sekarang.');
                }
                $k->tenggat_at = $baru;
            } elseif ($aksi === 'arsipkan') {
                $k->status = Kegiatan::ARSIP;
                $k->diarsipkan_at = $waktu;
            } else {
                $k->diarsipkan_at = null;
                $k->status = $k->terbit_at === null ? Kegiatan::DRAF : Kegiatan::DITUTUP;
                $k->ditutup_at = $k->terbit_at === null ? null : $waktu;
            }
            $k->revisi++;
            $k->save();
            $this->audit($k, $userId, $aksi, $sebelum, $data['alasan']);
            return $k;
        }, 3);
    }
    private function dataIsi(array $d): array
    {
        $buka = WaktuKegiatan::dariForm($d['buka_lokal'] ?? null, 'buka_lokal');
        $tenggat = WaktuKegiatan::dariForm($d['tenggat_lokal'] ?? null, 'tenggat_lokal');
        if (! $buka->lt($tenggat)) {
            $this->gagal('Tenggat harus lebih akhir daripada waktu mulai.');
        }
        $ext = $d['ekstensi_diizinkan'];
        sort($ext, SORT_STRING);
        return [
            'pertemuan_id' => ! empty($d['pertemuan_id']) ? (int) $d['pertemuan_id'] : null,
            'jenis' => $d['jenis'],
            'metode' => 'pengumpulan_berkas',
            'judul' => $d['judul'],
            'instruksi' => $d['instruksi'],
            'buka_at' => $buka->format('Y-m-d H:i:s'),
            'tenggat_at' => $tenggat->format('Y-m-d H:i:s'),
            'maks_ukuran_byte' => (int) $d['maks_mb'] * 1048576,
            'maks_berkas' => (int) $d['maks_berkas'],
            'ekstensi_diizinkan' => $ext
        ];
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
    private function pertemuan(int $kelasId, mixed $id): void
    {
        if ($id === null || $id === '') {
            return;
        }
        $s = Pertemuan::query()->whereKey((int) $id)->where('kelas_kuliah_id', $kelasId)->lockForUpdate()->first();
        if (! $s || $s->status === 'batal') {
            $this->gagal('Pertemuan harus berada di kelas ini dan tidak dibatalkan.');
        }
    }
    private function versi(Kegiatan $k, array $d): void
    {
        if (! is_string($d['versi'] ?? null) || ! hash_equals($k->versiForm(), $d['versi'])) {
            $this->gagal('Data sudah berubah. Muat ulang halaman sebelum menyimpan.');
        }
        if ($k->revisi >= 4294967295) {
            $this->gagal('Batas revisi kegiatan tercapai.');
        }
    }
    private function ids(array $d): array
    {
        $ids = array_map('intval', $d['berkas_ids'] ?? []);
        sort($ids, SORT_NUMERIC);
        if (
            count($ids) > (int) config('kegiatan.maks_lampiran_instruksi', 10) || count(array_unique($ids)) !== count($ids)
            || ($ids !== [] && min($ids) < 1)
        ) {
            $this->gagal('Daftar lampiran instruksi tidak valid.');
        }
        return $ids;
    }
    private function lampiran(Kegiatan $k, array $ids, int $userId): void
    {
        $lama = $k->semuaLampiran()->get()->keyBy('berkas_id');
        $semua = array_values(array_unique([...$ids, ...$lama->keys()->map(fn($id) => (int) $id)->all()]));
        sort($semua, SORT_NUMERIC);
        $files = Berkas::query()->whereIn('id', $semua)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        foreach ($ids as $id) {
            $file = $files->get($id);
            $row = $lama->get($id);
            if (
                ! $file || $file->status !== Berkas::TERSEDIA
                || (! ($row && $row->aktif) && $file->diunggah_oleh !== $userId)
            ) {
                $this->gagal('Lampiran tidak tersedia atau tidak boleh dipasang oleh akun Anda.');
            }
            if (! $row) {
                $row = new KegiatanBerkas();
                $row->kegiatan_id = $k->id;
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
    private function lampiranSiap(Kegiatan $k): void
    {
        $ids = $k->lampiran()->orderBy('berkas_id')->pluck('berkas_id')->all();
        $files = Berkas::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        if ($files->count() !== count($ids) || $files->contains(fn(Berkas $b) => $b->status !== Berkas::TERSEDIA)) {
            $this->gagal('Lampiran instruksi belum tersedia.');
        }
        if (! app()->environment(['local', 'testing']) && $files->contains(fn(Berkas $b) => $b->pemeriksaan !== 'clamav')) {
            $this->gagal('Lampiran produksi harus sudah dipindai antivirus.');
        }
    }
    private function audit(Kegiatan $k, int $userId, string $aksi, ?array $sebelum, ?string $alasan): void
    {
        $a = new AuditLog();
        $a->pelaku_id = $userId;
        $a->entitas = 'kegiatan';
        $a->entitas_id = $k->id;
        $a->versi_entitas = $k->revisi;
        $a->aksi = $aksi;
        $a->sebelum = $sebelum;
        $a->sesudah = $k->ringkasanAudit();
        $a->alasan = $alasan;
        $a->waktu = now('UTC');
        $a->save();
    }
    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['kegiatan' => $pesan]);
    }
}
