<?php

namespace App\Observers;

use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\PengajarKelas;
use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PengajarKelasIntegrityObserver
{
    public function updating(KelasKuliah $kelas): void
    {
        if (! $kelas->isDirty('status') || $kelas->status !== KelasKuliah::AKTIF) {
            return;
        }

        if ($kelas->getConnection()->transactionLevel() < 1) {
            $this->gagal('Aktivasi kelas harus menggunakan transaksi SimpanKelasKuliah.');
        }

        $terkini = KelasKuliah::query()->whereKey($kelas->id)->lockForUpdate()->firstOrFail();
        if ($terkini->revisi !== (int) $kelas->getRawOriginal('revisi')) {
            $this->gagal('Kelas telah berubah. Muat ulang formulir.');
        }

        $tim = PengajarKelas::query()->where('kelas_kuliah_id', $kelas->id)
            ->orderBy('id')->lockForUpdate()->get();
        $koordinator = $tim->filter(fn(PengajarKelas $row): bool => $row->isKoordinatorAktif());

        if ($koordinator->count() !== 1) {
            $this->gagal('Tetapkan satu koordinator aktif pada menu Pengajar Kelas sebelum mengaktifkan kelas.');
        }

        $dosen = Dosen::query()->whereKey($koordinator->first()->dosen_id)->lockForUpdate()->firstOrFail();
        $pemilik = User::query()->whereKey($dosen->user_id)->lockForUpdate()->firstOrFail();
        $roleDosen = $pemilik->roles()->where('roles.kode', Role::DOSEN)->lockForUpdate()->first(['roles.id']);

        if ($dosen->status !== Dosen::AKTIF || ! $pemilik->isAktif() || $roleDosen === null) {
            $this->gagal('Koordinator harus berstatus dosen aktif dengan akun aktif dan role dosen.');
        }
    }

    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['status' => $pesan]);
    }
}
