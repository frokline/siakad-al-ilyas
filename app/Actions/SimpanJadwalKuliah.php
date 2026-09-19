<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\JadwalKuliah;
use App\Models\KelasKuliah;
use App\Models\Kurikulum;
use App\Models\PaketSemester;
use App\Models\PengajarKelas;
use App\Models\PeriodeAkademik;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\User;
use App\Services\PemeriksaJadwalKuliah;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class SimpanJadwalKuliah
{
    public function __construct(private readonly PemeriksaJadwalKuliah $pemeriksa) {}

    public function execute(
        int $pelakuId,
        array $data,
        ?JadwalKuliah $bound = null,
        bool $nonaktifSaja = false
    ): JadwalKuliah {
        abort_if($nonaktifSaja && $bound === null, 404);
        // Di luar transaksi hanya membaca rantai FK yang immutable.
        $petunjuk = KelasKuliah::query()->with('rombel.paketSemester')->findOrFail(
            $bound?->kelas_kuliah_id ?? (int) ($data['kelas_kuliah_id'] ?? 0)
        );

        try {
            return DB::transaction(function () use ($pelakuId, $data, $bound, $nonaktifSaja, $petunjuk): JadwalKuliah {
                $pelaku = User::query()->whereKey($pelakuId)->lockForUpdate()->firstOrFail();
                $role = $pelaku->roles()->where('roles.kode', Role::ADMIN_AKADEMIK)
                    ->lockForUpdate()->first(['roles.id']);
                abort_unless($pelaku->isAktif() && $role !== null, 403);
                Gate::forUser($pelaku)->authorize('kelola-jadwal-kuliah');

                // Urutan parent mengikuti modul Kelas/Pengajar/KRS.
                $kurikulum = Kurikulum::query()->whereKey($petunjuk->rombel->paketSemester->kurikulum_id)
                    ->lockForUpdate()->firstOrFail();
                $prodi = ProgramStudi::query()->whereKey($kurikulum->program_studi_id)
                    ->lockForUpdate()->firstOrFail();
                $paket = PaketSemester::query()->whereKey($petunjuk->rombel->paket_semester_id)
                    ->lockForUpdate()->firstOrFail();
                $periode = PeriodeAkademik::query()->whereKey($petunjuk->rombel->periode_akademik_id)
                    ->lockForUpdate()->firstOrFail();
                $rombel = Rombel::query()->whereKey($petunjuk->rombel_id)->lockForUpdate()->firstOrFail();
                $semuaKelas = KelasKuliah::query()->where('rombel_id', $rombel->id)
                    ->orderBy('id')->lockForUpdate()->get();
                $kelas = $semuaKelas->firstWhere('id', $petunjuk->id);
                abort_unless($kelas, 404);

                $tim = PengajarKelas::query()->where('kelas_kuliah_id', $kelas->id)
                    ->orderBy('id')->lockForUpdate()->get();
                $semuaJadwal = JadwalKuliah::query()->where('kelas_kuliah_id', $kelas->id)
                    ->orderBy('id')->lockForUpdate()->get();
                $rombel->setRelation('periodeAkademik', $periode);
                $kelas->setRelation('rombel', $rombel);
                $kelas->setRelation('pengajarKelas', $tim);
                $kelas->setRelation('jadwalKuliah', $semuaJadwal);

                $token = $data['versi_jadwal'] ?? null;
                if (! is_string($token) || ! hash_equals($kelas->versiJadwalKuliah(), $token)) {
                    $this->gagal('versi_jadwal', 'Jadwal, tim, kelas, atau periode telah berubah. Muat ulang formulir.');
                }
                $alasan = $data['alasan'] ?? null;
                if (! is_string($alasan) || mb_strlen(trim($alasan)) < 10 || mb_strlen($alasan) > 2000) {
                    $this->gagal('alasan', 'Alasan wajib diisi 10–2.000 karakter.');
                }
                if (! in_array($data['konfirmasi'] ?? null, [true, 1, '1', 'yes', 'on', 'true'], true)) {
                    $this->gagal('konfirmasi', 'Konfirmasi perubahan belum diberikan.');
                }

                $jadwal = $bound ? $semuaJadwal->firstWhere('id', $bound->id) : new JadwalKuliah();
                abort_unless($jadwal, 404);
                $baru = ! $jadwal->exists;
                if ($baru) {
                    $jadwal->kelas_kuliah_id = $kelas->id;
                }
                $jadwal->setRelation('kelasKuliah', $kelas);
                $sebelum = $baru ? null : $jadwal->ringkasanAudit();
                $aktifSebelum = $baru ? false : $jadwal->aktif;
                $tautanDiubah = false;

                if ($nonaktifSaja) {
                    if (! $jadwal->aktif) {
                        $this->gagal('jadwal', 'Jadwal sudah nonaktif. Muat ulang halaman.');
                    }
                    $jadwal->aktif = false;
                } else {
                    if (! $jadwal->dapatDiubah()) {
                        $this->gagal('jadwal', 'Isi pola hanya dapat diubah pada kelas persiapan/aktif dan periode belum arsip.');
                    }
                    $nilaiAktif = $baru ? true : ($data['aktif'] ?? null);
                    if (! in_array($nilaiAktif, [true, false, 0, 1, '0', '1'], true)) {
                        $this->gagal('aktif', 'Status jadwal tidak valid.');
                    }
                    $jadwal->aktif = (bool) $nilaiAktif;
                    $jadwal->hari = (int) ($data['hari'] ?? 0);
                    $jadwal->jam_mulai = ($data['jam_mulai'] ?? '') . ':00';
                    $jadwal->jam_selesai = ($data['jam_selesai'] ?? '') . ':00';
                    $jadwal->berlaku_mulai = $data['berlaku_mulai'];
                    $jadwal->berlaku_selesai = $data['berlaku_selesai'];
                    $jadwal->metode = $data['metode'];
                    $jadwal->lokasi = $data['lokasi'] ?? null;

                    $aksiTautan = $data['aksi_tautan'] ?? '';
                    if (! in_array($aksiTautan, ['pertahankan', 'ganti', 'hapus'], true)) {
                        $this->gagal('aksi_tautan', 'Tindakan tautan tidak valid.');
                    }
                    if ($aksiTautan === 'ganti') {
                        $tautan = $data['tautan_pertemuan'] ?? null;
                        if (! is_string($tautan) || $tautan === '') {
                            $this->gagal('tautan_pertemuan', 'Masukkan tautan pengganti.');
                        }
                        if ($jadwal->tautan_pertemuan !== $tautan) {
                            $jadwal->tautan_pertemuan = $tautan;
                            $tautanDiubah = true;
                        }
                    } elseif ($aksiTautan === 'hapus' && $jadwal->memilikiTautan()) {
                        $jadwal->tautan_pertemuan = null;
                        $tautanDiubah = true;
                    }

                    if ($jadwal->aktif && (! $prodi->aktif || $kurikulum->status !== Kurikulum::AKTIF
                        || ! in_array($paket->status, [PaketSemester::DITERBITKAN, PaketSemester::ARSIP], true))) {
                        $this->gagal('jadwal', 'Jadwal aktif membutuhkan prodi/kurikulum aktif dan paket yang sudah diterbitkan.');
                    }
                }

                $jadwal->pastikanPolaValid();
                if (
                    $jadwal->berlaku_mulai->toDateString() < $periode->mulai->toDateString()
                    || $jadwal->berlaku_selesai->toDateString() > $periode->selesai->toDateString()
                ) {
                    $this->gagal('berlaku_selesai', 'Seluruh rentang pola harus berada di dalam tanggal periode akademik.');
                }
                if (! $baru && ! $jadwal->isDirty(JadwalKuliah::KOLOM_POLA)) {
                    $this->gagal('jadwal', 'Tidak ada perubahan jadwal untuk disimpan.');
                }
                if (! $baru && $jadwal->revisi >= 4294967295) {
                    $this->gagal('jadwal', 'Batas revisi tercapai. Hubungi pengelola sistem.');
                }

                $dosenIds = $this->pemeriksa->kunciDosenKelas($kelas->id);
                $this->pemeriksa->periksa($jadwal, $rombel, $dosenIds);
                $waktu = CarbonImmutable::now('UTC');
                if ($jadwal->aktif) {
                    $jadwal->dinonaktifkan_at = null;
                } elseif ($aktifSebelum) {
                    $jadwal->dinonaktifkan_at = $waktu;
                }
                $jadwal->revisi = $baru ? 1 : $jadwal->revisi + 1;
                $jadwal->save();

                $aksi = $baru ? 'buat' : ($aktifSebelum === $jadwal->aktif ? 'ubah'
                    : ($jadwal->aktif ? 'aktifkan' : 'nonaktifkan'));
                $audit = new AuditLog();
                $audit->pelaku_id = $pelaku->id;
                $audit->entitas = 'jadwal_kuliah';
                $audit->entitas_id = $jadwal->id;
                $audit->versi_entitas = $jadwal->revisi;
                $audit->aksi = $aksi;
                $audit->sebelum = $sebelum;
                $audit->sesudah = array_merge($jadwal->ringkasanAudit(), ['tautan_diubah' => $tautanDiubah]);
                $audit->alasan = trim($alasan);
                $audit->waktu = $waktu;
                $audit->save();

                return $jadwal;
            }, 3);
        } catch (QueryException $exception) {
            $kode = (int) ($exception->errorInfo[1] ?? 0);
            if (in_array($kode, [1062, 1205, 1213], true)) {
                $this->gagal('jadwal', 'Data sedang berubah bersamaan. Muat ulang halaman lalu coba kembali.');
            }
            throw $exception;
        }
    }

    private function gagal(string $field, string $pesan): never
    {
        throw ValidationException::withMessages([$field => $pesan]);
    }
}
