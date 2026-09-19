<?php

namespace App\Actions;

use App\Models\Berkas;
use App\Models\User;
use App\Services\AksesBerkas;
use App\Services\LogBerkas;
use App\Services\PemeriksaBerkas;
use App\Services\PenyimpananBerkas;
use App\Services\ReferensiBerkas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class KelolaBerkas
{
    public function __construct(
        private readonly PemeriksaBerkas $pemeriksa,
        private readonly PenyimpananBerkas $storage,
        private readonly AksesBerkas $akses,
        private readonly LogBerkas $audit,
        private readonly ReferensiBerkas $referensi
    ) {}

    public function unggah(int $userId, UploadedFile $upload, array $data): Berkas
    {
        $user = User::query()->findOrFail($userId);
        Gate::forUser($user)->authorize('create', Berkas::class);
        $info = $this->pemeriksa->periksa($upload);
        try {
            $disk = $this->storage->diskBaru();
        } catch (\RuntimeException) {
            $this->gagal('Penyimpanan privat belum dikonfigurasi. Hubungi pengelola.');
        }
        [$file, $baru] = DB::transaction(function () use ($userId, $info, $data, $disk): array {
            $user = $this->kunciUser($userId);
            $ada = Berkas::query()->where('diunggah_oleh', $userId)->where('upload_token', $data['upload_token'])
                ->lockForUpdate()->first();
            if ($ada !== null) {
                if (
                    ! hash_equals($ada->sha256, $info['sha256']) || $ada->ukuran_byte !== $info['ukuran_byte']
                    || $ada->nama_asli !== $info['nama_asli'] || $ada->mime_type !== $info['mime_type']
                    || $ada->label !== $data['label'] || $ada->keterangan !== ($data['keterangan'] ?? null)
                ) {
                    $this->gagal('Formulir unggahan ini sudah digunakan untuk data berbeda. Buka halaman unggah baru.');
                }
                return [$ada, false];
            }
            // Kuota menghitung semua reservasi, termasuk objek yang dinonaktifkan/gagal.
            // Objek tersebut belum dihapus fisik; jangan membuat kuota seolah sudah bebas.
            $terpakai = (int) Berkas::query()->where('diunggah_oleh', $userId)->sum('ukuran_byte');
            if ($terpakai + $info['ukuran_byte'] > (int) config('berkas.kuota_byte')) {
                $this->gagal('Kuota penyimpanan akun tidak mencukupi. Hubungi pengelola sistem.');
            }
            $file = new Berkas();
            $file->diunggah_oleh = $user->id;
            $file->upload_token = $data['upload_token'];
            $file->storage_disk = $disk;
            $file->object_key = 'siakad/berkas/' . now('UTC')->format('Y/m') . '/' . Str::uuid() . '.' . $info['ekstensi'];
            foreach ($info as $key => $value) {
                $file->{$key} = $value;
            }
            $file->label = $data['label'];
            $file->keterangan = $data['keterangan'] ?? null;
            $file->diperiksa_at = now('UTC');
            $file->status = Berkas::MENUNGGU;
            $file->revisi = 1;
            $file->save();
            $this->audit->tulis($file, $userId, 'reservasi', null, null);
            return [$file, true];
        }, 3);
        if (! $baru) {
            return $file;
        }

        // Efek storage di luar callback transaksi: retry deadlock tidak mengunggah ulang objek.
        try {
            $this->storage->tulis($file, $upload->getRealPath());
        } catch (\Throwable $error) {
            Log::warning('Unggahan berkas tidak selesai.', ['berkas_id' => $file->id, 'jenis' => $error::class]);
            return $this->gagalUnggah($file->id, $userId);
        }

        // Jika proses/DB terputus di sini, metadata tetap menunggu dan tak bisa diunduh.
        // Perintah berkas:rekonsiliasi akan menutup proses lama; tidak menghapus objek.
        return DB::transaction(function () use ($file, $userId): Berkas {
            $user = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $row = Berkas::query()->whereKey($file->id)->lockForUpdate()->firstOrFail();
            if ($row->status !== Berkas::MENUNGGU) {
                return $row;
            }
            $sebelum = $row->ringkasanAudit();
            if (! $this->akses->masuk($user)) {
                $row->status = Berkas::DITOLAK;
                $row->pesan_status = 'Akses akun berubah sebelum proses unggah selesai.';
            } else {
                $row->status = Berkas::TERSEDIA;
                $row->tersedia_at = now('UTC');
            }
            $row->revisi++;
            $row->save();
            $this->audit->tulis($row, $userId, 'selesai_unggah', $sebelum, $row->pesan_status);
            return $row;
        }, 3);
    }

    public function ubah(string $aksi, int $userId, Berkas $bound, array $data): Berkas
    {
        abort_unless(in_array($aksi, ['ubah', 'nonaktifkan', 'pulihkan'], true), 404);
        return DB::transaction(function () use ($aksi, $userId, $bound, $data): Berkas {
            $user = $this->kunciUser($userId);
            $file = Berkas::query()->whereKey($bound->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($user)->authorize($aksi === 'ubah' ? 'update' : $aksi, $file);
            if (! hash_equals($file->versiForm(), $data['versi'])) {
                $this->gagal('Data sudah berubah. Muat ulang formulir sebelum menyimpan.');
            }
            if ($file->revisi >= 4294967295) {
                $this->gagal('Batas revisi tercapai.');
            }
            $sebelum = $file->ringkasanAudit();
            if (in_array($aksi, ['ubah', 'nonaktifkan'], true)) {
                $this->referensi->pastikanBelumDipakai($file);
            }
            if ($aksi === 'ubah') {
                $file->label = $data['label'];
                $file->keterangan = $data['keterangan'] ?? null;
                if (! $file->isDirty(['label', 'keterangan'])) {
                    return $file;
                }
            } elseif ($aksi === 'nonaktifkan') {
                $file->status = Berkas::DIHAPUS;
                $file->dinonaktifkan_at = now('UTC');
            } else {
                // Pemulihan tidak mengubah objek/isi. Objek tetap diverifikasi saat unduh.
                $file->status = Berkas::TERSEDIA;
                $file->dinonaktifkan_at = null;
            }
            $file->revisi++;
            $file->save();
            $this->audit->tulis($file, $userId, $aksi, $sebelum, $data['alasan']);
            return $file;
        }, 3);
    }

    private function gagalUnggah(int $id, int $userId): Berkas
    {
        return DB::transaction(function () use ($id, $userId): Berkas {
            $file = Berkas::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            if ($file->status !== Berkas::MENUNGGU) {
                return $file;
            }
            $sebelum = $file->ringkasanAudit();
            $file->status = Berkas::DITOLAK;
            $file->pesan_status = 'Penyimpanan tidak menyelesaikan proses. Coba unggah melalui formulir baru.';
            $file->revisi++;
            $file->save();
            $this->audit->tulis($file, $userId, 'gagal_unggah', $sebelum, $file->pesan_status);
            return $file;
        }, 3);
    }
    private function kunciUser(int $id): User
    {
        $user = User::query()->whereKey($id)->lockForUpdate()->firstOrFail();
        $user->roles()->orderBy('roles.id')->lockForUpdate()->get(['roles.id']);
        $user->dosen()->lockForUpdate()->get();
        $user->mahasiswa()->lockForUpdate()->get();
        abort_unless($this->akses->masuk($user), 403);
        return $user;
    }
    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['berkas' => $pesan]);
    }
}
