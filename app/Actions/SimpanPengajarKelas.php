<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\Kurikulum;
use App\Models\PaketSemester;
use App\Models\PengajarKelas;
use App\Models\PeriodeAkademik;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SimpanPengajarKelas
{
    public function execute(int $pelakuId, array $data, ?PengajarKelas $bound = null): PengajarKelas
    {
        // Hanya petunjuk FK immutable; keadaan terbaru dibaca lagi dengan lock.
        $petunjuk = KelasKuliah::query()->with('rombel.paketSemester')->findOrFail(
            $bound?->kelas_kuliah_id ?? (int) ($data['kelas_kuliah_id'] ?? 0)
        );
        $dosenId = $bound?->dosen_id ?? (int) ($data['dosen_id'] ?? 0);

        try {
            return DB::transaction(function () use ($pelakuId, $data, $bound, $petunjuk, $dosenId): PengajarKelas {
                $pelaku = User::query()->whereKey($pelakuId)->lockForUpdate()->firstOrFail();
                $roleAdmin = $pelaku->roles()->where('roles.kode', Role::ADMIN_AKADEMIK)
                    ->lockForUpdate()->first(['roles.id']);
                abort_unless($pelaku->isAktif() && $roleAdmin !== null, 403);
                Gate::forUser($pelaku)->authorize('kelola-pengajar-kelas');

                $kurikulum = Kurikulum::query()->whereKey($petunjuk->rombel->paketSemester->kurikulum_id)
                    ->lockForUpdate()->firstOrFail();
                $prodi = ProgramStudi::query()->whereKey($kurikulum->program_studi_id)
                    ->lockForUpdate()->firstOrFail();
                $paket = PaketSemester::query()->whereKey($petunjuk->rombel->paket_semester_id)
                    ->lockForUpdate()->firstOrFail();
                $periode = PeriodeAkademik::query()->whereKey($petunjuk->rombel->periode_akademik_id)
                    ->lockForUpdate()->firstOrFail();
                $rombel = Rombel::query()->whereKey($petunjuk->rombel_id)->lockForUpdate()->firstOrFail();

                // Mengikuti penguncian rombel/kelas pada SimpanKelasKuliah.
                $kelasRombel = KelasKuliah::query()->where('rombel_id', $rombel->id)
                    ->orderBy('id')->lockForUpdate()->get();
                $kelas = $kelasRombel->firstWhere('id', $petunjuk->id);
                abort_unless($kelas, 404);

                if (
                    (int) $rombel->periode_akademik_id !== (int) $periode->id
                    || (int) $rombel->paket_semester_id !== (int) $paket->id
                    || (int) $paket->kurikulum_id !== (int) $kurikulum->id
                ) {
                    $this->gagal('kelas_kuliah_id', 'Hubungan kelas, rombel, periode, dan paket tidak sesuai.');
                }

                if (
                    ! in_array($periode->status, Rombel::STATUS_PERIODE_TERBUKA, true)
                    || ! in_array($kelas->status, KelasKuliah::BELUM_TUNTAS, true)
                ) {
                    $this->gagal('penugasan', 'Tim hanya dapat diubah pada kelas persiapan/aktif dan periode yang belum diarsipkan.');
                }

                $tim = PengajarKelas::query()->where('kelas_kuliah_id', $kelas->id)
                    ->orderBy('id')->lockForUpdate()->get();
                $kelas->setRelation('pengajarKelas', $tim);

                $token = $data['versi_tim'] ?? null;
                if (! is_string($token) || ! hash_equals($kelas->versiTimPengajar(), $token)) {
                    $this->gagal('versi_tim', 'Kelas atau tim pengajar telah berubah. Muat ulang formulir sebelum menyimpan.');
                }

                if ($bound) {
                    $penugasan = $tim->firstWhere('id', $bound->id);
                    abort_unless($penugasan && (int) $penugasan->dosen_id === (int) $dosenId, 404);
                } else {
                    $duplikat = $tim->firstWhere('dosen_id', $dosenId);
                    if ($duplikat !== null) {
                        $this->gagal('dosen_id', 'Dosen sudah memiliki penugasan #' . $duplikat->id . '. Buka penugasan tersebut untuk mengubah atau mengaktifkannya kembali.');
                    }
                    $penugasan = new PengajarKelas();
                    $penugasan->kelas_kuliah_id = $kelas->id;
                    $penugasan->dosen_id = $dosenId;
                }

                $peran = $data['peran'] ?? null;
                $nilaiAktif = $bound ? ($data['aktif'] ?? null) : true;
                $alasan = $data['alasan'] ?? null;

                if (! is_string($peran) || ! isset(PengajarKelas::PERAN[$peran])) {
                    $this->gagal('peran', 'Pilih peran koordinator atau pengajar.');
                }
                if (! in_array($nilaiAktif, [true, false, 0, 1, '0', '1'], true)) {
                    $this->gagal('aktif', 'Status penugasan tidak valid.');
                }
                $aktif = (bool) $nilaiAktif;
                if (! is_string($alasan) || mb_strlen(trim($alasan)) < 10 || mb_strlen($alasan) > 2000) {
                    $this->gagal('alasan', 'Alasan wajib diisi 10–2.000 karakter.');
                }

                if ($penugasan->exists) {
                    if ($penugasan->aktif === $aktif && $penugasan->peran === $peran) {
                        $this->gagal('penugasan', 'Tidak ada perubahan peran atau status untuk disimpan.');
                    }
                    if (! $aktif && $penugasan->peran !== $peran) {
                        $this->gagal('peran', 'Pertahankan peran sebelumnya ketika menonaktifkan penugasan.');
                    }
                    if (! $penugasan->aktif && ! $aktif) {
                        $this->gagal('aktif', 'Penugasan nonaktif hanya dapat diaktifkan kembali.');
                    }
                }

                $dosen = Dosen::query()->whereKey($dosenId)->lockForUpdate()->firstOrFail();
                $pemilik = User::query()->whereKey($dosen->user_id)->lockForUpdate()->firstOrFail();

                if ($aktif) {
                    $roleDosen = $pemilik->roles()->where('roles.kode', Role::DOSEN)
                        ->lockForUpdate()->first(['roles.id']);
                    if ($dosen->status !== Dosen::AKTIF || ! $pemilik->isAktif() || $roleDosen === null) {
                        $this->gagal('dosen_id', 'Dosen harus aktif, akunnya aktif, dan memiliki role dosen.');
                    }
                    if (
                        ! $prodi->aktif || $kurikulum->status !== Kurikulum::AKTIF
                        || ! in_array($paket->status, [PaketSemester::DITERBITKAN, PaketSemester::ARSIP], true)
                    ) {
                        $this->gagal('penugasan', 'Program studi/kurikulum harus aktif dan paket kelas harus sudah diterbitkan.');
                    }
                }

                $waktu = CarbonImmutable::now('UTC');
                $perubahan = [];

                // Lepaskan indeks unik koordinator lama sebelum menetapkan yang baru.
                if ($aktif && $peran === PengajarKelas::KOORDINATOR) {
                    foreach ($tim as $anggota) {
                        if ($anggota->id === $penugasan->id || ! $anggota->isKoordinatorAktif()) {
                            continue;
                        }
                        $sebelum = $anggota->ringkasanAudit();
                        $anggota->peran = PengajarKelas::PENGAJAR;
                        $anggota->revisi = $this->revisiBerikutnya($anggota);
                        $anggota->save();
                        $perubahan[] = [$anggota, $sebelum, 'ganti_koordinator'];
                    }
                }

                $sebelum = $penugasan->exists ? $penugasan->ringkasanAudit() : null;
                $baru = ! $penugasan->exists;
                if ($baru || (! $penugasan->aktif && $aktif)) {
                    $penugasan->diaktifkan_at = $waktu;
                    $penugasan->dinonaktifkan_at = null;
                } elseif ($penugasan->aktif && ! $aktif) {
                    $penugasan->dinonaktifkan_at = $waktu;
                }

                $penugasan->peran = $peran;
                $penugasan->aktif = $aktif;
                $penugasan->revisi = $baru ? 1 : $this->revisiBerikutnya($penugasan);
                $penugasan->save();
                $perubahan[] = [$penugasan, $sebelum, $baru ? 'buat' : 'ubah'];
                if ($baru) {
                    $tim->push($penugasan);
                }

                $jumlahKoordinator = $tim->filter(fn(PengajarKelas $row): bool => $row->isKoordinatorAktif())->count();
                if ($jumlahKoordinator > 1 || ($kelas->status === KelasKuliah::AKTIF && $jumlahKoordinator !== 1)) {
                    $this->gagal('peran', 'Kelas aktif harus memiliki satu koordinator aktif. Tetapkan dosen pengganti sebagai koordinator sebelum menonaktifkan koordinator lama.');
                }

                foreach ($perubahan as [$row, $keadaanAwal, $aksi]) {
                    $this->catatAudit(
                        $row,
                        $keadaanAwal,
                        $aksi,
                        $pelaku->id,
                        trim($alasan),
                        $waktu,
                        $aksi === 'ganti_koordinator' ? $penugasan->id : null
                    );
                }

                return $penugasan;
            }, 3);
        } catch (QueryException $exception) {
            $kode = (int) ($exception->errorInfo[1] ?? 0);
            if ($kode === 1062) {
                $this->gagal('penugasan', 'Dosen, koordinator, atau revisi penugasan sudah tersimpan. Muat ulang tim pengajar.');
            }
            if (in_array($kode, [1205, 1213], true)) {
                $this->gagal('penugasan', 'Tim sedang diproses bersamaan. Muat ulang halaman lalu coba kembali.');
            }
            throw $exception;
        }
    }

    private function revisiBerikutnya(PengajarKelas $penugasan): int
    {
        if ($penugasan->revisi >= 4294967295) {
            $this->gagal('penugasan', 'Batas revisi tercapai. Hubungi pengelola sistem.');
        }
        return $penugasan->revisi + 1;
    }

    private function catatAudit(
        PengajarKelas $penugasan,
        ?array $sebelum,
        string $aksi,
        int $pelakuId,
        string $alasan,
        CarbonImmutable $waktu,
        ?int $penggantiId
    ): void {
        $audit = new AuditLog();
        $audit->pelaku_id = $pelakuId;
        $audit->entitas = 'pengajar_kelas';
        $audit->entitas_id = $penugasan->id;
        $audit->versi_entitas = $penugasan->revisi;
        $audit->aksi = $aksi;
        $audit->sebelum = $sebelum;
        $audit->sesudah = array_merge($penugasan->ringkasanAudit(), [
            'koordinator_pengganti_id' => $penggantiId,
        ]);
        $audit->alasan = $alasan;
        $audit->waktu = $waktu;
        $audit->save();
    }

    private function gagal(string $field, string $pesan): never
    {
        throw ValidationException::withMessages([$field => $pesan]);
    }
}
