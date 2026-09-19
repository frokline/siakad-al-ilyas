<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Dosen;
use App\Models\JadwalKuliah;
use App\Models\KelasKuliah;
use App\Models\Kurikulum;
use App\Models\PaketSemester;
use App\Models\PengajarKelas;
use App\Models\PeriodeAkademik;
use App\Models\Pertemuan;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\User;
use App\Services\PemeriksaBenturanPertemuan;
use App\Support\WaktuPertemuan;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class KelolaPertemuan
{
    public function __construct(private readonly PemeriksaBenturanPertemuan $pemeriksa) {}

    public function execute(string $aksi, int $pelakuId, array $data, ?Pertemuan $bound = null): Pertemuan
    {
        abort_unless(isset(Pertemuan::AKSI[$aksi]), 404);
        $baru = $aksi === 'buat';
        abort_if(($baru && $bound !== null) || (! $baru && $bound === null), 404);
        $petunjuk = KelasKuliah::query()->with('rombel.paketSemester')->findOrFail(
            $bound?->kelas_kuliah_id ?? (int) ($data['kelas_kuliah_id'] ?? 0)
        );

        try {
            return DB::transaction(function () use ($aksi, $pelakuId, $data, $bound, $baru, $petunjuk): Pertemuan {
                $pelaku = User::query()->whereKey($pelakuId)->lockForUpdate()->firstOrFail();
                $role = $pelaku->roles()->where('roles.kode', Role::ADMIN_AKADEMIK)
                    ->lockForUpdate()->first(['roles.id']);
                abort_unless($pelaku->isAktif() && $role !== null, 403);
                Gate::forUser($pelaku)->authorize('kelola-pertemuan');

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
                $pola = JadwalKuliah::query()->where('kelas_kuliah_id', $kelas->id)
                    ->orderBy('id')->lockForUpdate()->get();
                $semuaSesi = Pertemuan::query()->where('kelas_kuliah_id', $kelas->id)
                    ->orderBy('id')->lockForUpdate()->get();
                $rombel->setRelation('periodeAkademik', $periode);
                $kelas->setRelation('rombel', $rombel);
                $kelas->setRelation('pengajarKelas', $tim);
                $kelas->setRelation('jadwalKuliah', $pola);
                $kelas->setRelation('pertemuan', $semuaSesi);

                $token = $data['versi_pertemuan'] ?? null;
                if (! is_string($token) || ! hash_equals($kelas->versiPertemuan(), $token)) {
                    $this->gagal('versi_pertemuan', 'Pertemuan, tim, jadwal, kelas, atau periode telah berubah. Muat ulang halaman.');
                }
                $alasan = $data['alasan'] ?? null;
                if (! is_string($alasan) || mb_strlen(trim($alasan)) < 10 || mb_strlen($alasan) > 2000) {
                    $this->gagal('alasan', 'Alasan wajib diisi 10–2.000 karakter.');
                }
                if (! in_array($data['konfirmasi'] ?? null, [true, 1, '1', 'on', 'yes', 'true'], true)) {
                    $this->gagal('konfirmasi', 'Konfirmasi tindakan belum diberikan.');
                }

                $sesi = $baru ? new Pertemuan() : $semuaSesi->firstWhere('id', $bound->id);
                abort_unless($sesi, 404);
                if ($baru) {
                    $sesi->kelas_kuliah_id = $kelas->id;
                    $sesi->nomor = (int) ($data['nomor'] ?? 0);
                    $sesi->status = Pertemuan::TERJADWAL;
                    if ($semuaSesi->contains('nomor', $sesi->nomor)) {
                        $this->gagal('nomor', 'Nomor sudah digunakan, termasuk pada sesi batal. Pilih nomor lain atau pulihkan sesi sebelumnya.');
                    }
                }
                $sesi->setRelation('kelasKuliah', $kelas);
                $sebelum = $baru ? null : $sesi->ringkasanAudit();
                $editRencana = in_array($aksi, ['buat', 'ubah'], true);
                $butuhLayak = in_array($aksi, ['buat', 'ubah', 'pulihkan', 'mulai'], true);

                if ($butuhLayak && (! $sesi->konteksTerbuka() || ! $prodi->aktif
                    || $kurikulum->status !== Kurikulum::AKTIF
                    || ! in_array($paket->status, [PaketSemester::DITERBITKAN, PaketSemester::ARSIP], true))) {
                    $this->gagal('pertemuan', 'Kelas/periode harus terbuka, prodi/kurikulum aktif, dan paket sudah diterbitkan.');
                }
                $asalYangDiizinkan = [
                    'ubah' => Pertemuan::TERJADWAL,
                    'mulai' => Pertemuan::TERJADWAL,
                    'selesai' => Pertemuan::BERLANGSUNG,
                    'batalkan' => Pertemuan::TERJADWAL,
                    'pulihkan' => Pertemuan::BATAL,
                ];
                if (! $baru && $sesi->status !== $asalYangDiizinkan[$aksi]) {
                    $this->gagal('pertemuan', 'Tindakan tidak sesuai dengan status pertemuan saat ini.');
                }

                $pengajarId = $editRencana ? (int) ($data['pengajar_kelas_id'] ?? 0) : $sesi->pengajar_kelas_id;
                $pengajar = $tim->firstWhere('id', $pengajarId);
                if ($pengajar === null) {
                    $this->gagal('pengajar_kelas_id', 'Penanggung jawab harus berasal dari tim kelas ini.');
                }
                $pengajarLama = $baru ? null : $tim->firstWhere('id', $sesi->pengajar_kelas_id);
                $dosenIds = collect([$pengajar->dosen_id, $pengajarLama?->dosen_id])->filter()->unique()->sort()->values()->all();
                $dosenTerkunci = Dosen::query()->whereIn('id', $dosenIds)->orderBy('id')->lockForUpdate()->get();
                $dosen = $dosenTerkunci->firstWhere('id', $pengajar->dosen_id);
                abort_unless($dosen, 404);
                $pemilik = User::query()->whereKey($dosen->user_id)->lockForUpdate()->firstOrFail();
                if ($butuhLayak) {
                    $roleDosen = $pemilik->roles()->where('roles.kode', Role::DOSEN)->lockForUpdate()->first(['roles.id']);
                    if (! $pengajar->aktif || $dosen->status !== Dosen::AKTIF || ! $pemilik->isAktif() || $roleDosen === null) {
                        $this->gagal('pengajar_kelas_id', 'Penanggung jawab harus memiliki penugasan, dosen, akun, dan role dosen yang aktif.');
                    }
                }

                $tautanDiubah = false;
                if ($editRencana) {
                    try {
                        [$mulai, $selesai] = WaktuPertemuan::dariForm(
                            $data['tanggal'],
                            $data['jam_mulai'],
                            $data['jam_selesai'],
                            $this->zona()
                        );
                    } catch (InvalidArgumentException $exception) {
                        $this->gagal('tanggal', $exception->getMessage());
                    }
                    $sumberId = ! empty($data['jadwal_kuliah_id']) ? (int) $data['jadwal_kuliah_id'] : null;
                    $sumber = $sumberId === null ? null : $pola->firstWhere('id', $sumberId);
                    if ($sumberId !== null && $sumber === null) {
                        $this->gagal('jadwal_kuliah_id', 'Pola sumber harus berasal dari kelas ini.');
                    }
                    if ($baru || $sesi->jadwal_kuliah_id !== $sumberId) {
                        if ($sumber !== null && ! $sumber->aktif) {
                            $this->gagal('jadwal_kuliah_id', 'Pilih pola aktif untuk referensi baru.');
                        }
                        $sesi->jadwal_kuliah_id = $sumberId;
                        $sesi->jadwal_snapshot = $sumber === null ? null : array_merge($sumber->ringkasanAudit(), [
                            'id' => $sumber->id,
                            'zona_waktu' => $this->zona(),
                        ]);
                    }
                    if ($sumber !== null) {
                        $lokalMulai = $mulai->setTimezone($this->zona());
                        $lokalSelesai = $selesai->setTimezone($this->zona());
                        if (
                            $lokalMulai->dayOfWeekIso !== (int) $sumber->hari
                            || $lokalMulai->toDateString() < $sumber->berlaku_mulai->toDateString()
                            || $lokalMulai->toDateString() > $sumber->berlaku_selesai->toDateString()
                            || $lokalMulai->format('H:i:s') < $sumber->jam_mulai
                            || $lokalSelesai->format('H:i:s') > $sumber->jam_selesai
                        ) {
                            $this->gagal('jadwal_kuliah_id', 'Tanggal dan jam sesi harus berada pada hari, rentang tanggal, dan jam pola sumber. Kosongkan pola untuk pertemuan tambahan.');
                        }
                    }
                    if ($baru || $sesi->pengajar_kelas_id !== $pengajar->id) {
                        $sesi->pengajar_snapshot = $this->snapshotPengajar($pengajar, $dosen, $pemilik);
                    }
                    $sesi->pengajar_kelas_id = $pengajar->id;
                    $sesi->jenis = $data['jenis'];
                    $sesi->topik = $data['topik'];
                    $sesi->rencana = $data['rencana'] ?? null;
                    $sesi->mulai_rencana = $mulai;
                    $sesi->selesai_rencana = $selesai;
                    $sesi->metode = $data['metode'];
                    $sesi->lokasi = $data['lokasi'] ?? null;
                    $modeTautan = $data['aksi_tautan'] ?? '';
                    $tautan = $sesi->tautan_pertemuan;
                    if ($modeTautan === 'ganti') {
                        $tautan = $data['tautan_pertemuan'] ?? null;
                        if (! is_string($tautan) || $tautan === '') {
                            $this->gagal('tautan_pertemuan', 'Masukkan tautan pengganti.');
                        }
                    } elseif ($modeTautan === 'hapus') {
                        $tautan = null;
                    } elseif ($modeTautan === 'salin_jadwal') {
                        if ($sumber === null || ! $sumber->aktif || ! $sumber->memilikiTautan()) {
                            $this->gagal('aksi_tautan', 'Pola sumber harus aktif dan memiliki tautan untuk disalin.');
                        }
                        $tautan = $sumber->tautan_pertemuan;
                    } elseif ($modeTautan !== 'pertahankan') {
                        $this->gagal('aksi_tautan', 'Tindakan tautan tidak valid.');
                    }
                    if ($sesi->tautan_pertemuan !== $tautan) {
                        $sesi->tautan_pertemuan = $tautan;
                        $tautanDiubah = true;
                    }
                }

                $tanggal = $sesi->mulai_rencana->setTimezone($this->zona())->toDateString();
                $tanggalAkhir = $sesi->selesai_rencana->setTimezone($this->zona())->toDateString();
                if ($tanggal < $periode->mulai->toDateString() || $tanggalAkhir > $periode->selesai->toDateString()) {
                    $this->gagal('tanggal', 'Tanggal rencana harus berada di dalam periode akademik.');
                }
                if ($aksi === 'pulihkan') {
                    $sesi->status = Pertemuan::TERJADWAL;
                    $sesi->dibatalkan_at = null;
                }
                if ($butuhLayak) {
                    $this->pemeriksa->periksaRencana($sesi, $rombel, $dosen->id);
                }
                if ($aksi === 'mulai') {
                    $this->pemeriksa->pastikanTidakSedangMengajar($sesi, $rombel, $dosen->id);
                }
                // Ambil waktu sesudah seluruh penantian lock/pemeriksaan selesai.
                $waktu = CarbonImmutable::now('UTC')->startOfSecond();
                if ($aksi === 'mulai') {
                    if ($kelas->status !== KelasKuliah::AKTIF || $periode->status !== 'aktif') {
                        $this->gagal('pertemuan', 'Mulai memerlukan kelas dan periode aktif.');
                    }
                    if ($waktu->lt($sesi->mulai_rencana) || ! $waktu->lt($sesi->selesai_rencana)) {
                        $this->gagal('pertemuan', 'Mulai hanya dapat dilakukan dalam rentang waktu rencana. Perbaiki rencana bila pelaksanaan bergeser.');
                    }
                    $sesi->status = Pertemuan::BERLANGSUNG;
                    $sesi->mulai_aktual = $waktu;
                    $sesi->pengajar_snapshot = $this->snapshotPengajar($pengajar, $dosen, $pemilik);
                } elseif ($aksi === 'selesai') {
                    $realisasi = $data['realisasi'] ?? null;
                    if (! is_string($realisasi) || mb_strlen(trim($realisasi)) < 10 || mb_strlen($realisasi) > 20000) {
                        $this->gagal('realisasi', 'Catatan realisasi wajib diisi 10–20.000 karakter.');
                    }
                    if (! $waktu->gt($sesi->mulai_aktual)) {
                        $this->gagal('pertemuan', 'Waktu selesai harus setelah waktu mulai. Tunggu setidaknya satu detik dan periksa jam server.');
                    }
                    $sesi->status = Pertemuan::SELESAI;
                    $sesi->selesai_aktual = $waktu;
                    $sesi->realisasi = trim($realisasi);
                } elseif ($aksi === 'batalkan') {
                    $sesi->status = Pertemuan::BATAL;
                    $sesi->dibatalkan_at = $waktu;
                }
                if (! $baru && $sesi->revisi >= 4294967295) {
                    $this->gagal('pertemuan', 'Batas revisi tercapai. Hubungi pengelola sistem.');
                }
                $sesi->revisi = $baru ? 1 : $sesi->revisi + 1;
                $sesi->save();

                $audit = new AuditLog();
                $audit->pelaku_id = $pelaku->id;
                $audit->entitas = 'pertemuan';
                $audit->entitas_id = $sesi->id;
                $audit->versi_entitas = $sesi->revisi;
                $audit->aksi = $aksi;
                $audit->sebelum = $sebelum;
                $audit->sesudah = array_merge($sesi->ringkasanAudit(), ['tautan_diubah' => $tautanDiubah]);
                $audit->alasan = trim($alasan);
                $audit->waktu = $waktu;
                $audit->save();
                return $sesi;
            }, 3);
        } catch (QueryException $exception) {
            if (in_array((int) ($exception->errorInfo[1] ?? 0), [1062, 1205, 1213], true)) {
                $this->gagal('pertemuan', 'Nomor/revisi telah digunakan atau data berubah bersamaan. Muat ulang halaman.');
            }
            throw $exception;
        }
    }

    private function snapshotPengajar(PengajarKelas $pengajar, Dosen $dosen, User $user): array
    {
        return [
            'pengajar_kelas_id' => $pengajar->id,
            'revisi_penugasan' => $pengajar->revisi,
            'dosen_id' => $dosen->id,
            'kode_dosen' => $dosen->kode_dosen,
            'nama' => $user->nama,
            'gelar' => $dosen->gelar,
            'peran' => $pengajar->peran,
        ];
    }

    private function zona(): string
    {
        return (string) config('siakad.timezone', 'Asia/Makassar');
    }

    private function gagal(string $field, string $pesan): never
    {
        throw ValidationException::withMessages([$field => $pesan]);
    }
}
