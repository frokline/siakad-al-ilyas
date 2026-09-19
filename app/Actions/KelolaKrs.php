<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\DetailKrs;
use App\Models\DetailPaket;
use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\PaketSemester;
use App\Models\PeriodeAkademik;
use App\Models\ProgramStudi;
use App\Models\RegistrasiSemester;
use App\Models\RiwayatStudi;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use LogicException;

class KelolaKrs
{
    public function execute(int $pelakuId, string $operasi, array $data, ?Krs $bound = null): Krs
    {
        if (! isset(Krs::OPERASI[$operasi]) || (($operasi === 'buat') !== ($bound === null))) {
            throw new LogicException('Operasi KRS atau parameter target tidak sesuai.');
        }

        $petunjuk = $this->petunjukRegistrasi(
            $bound?->registrasi_semester_id ?? (int) ($data['registrasi_semester_id'] ?? 0)
        );

        try {
            return DB::transaction(function () use ($pelakuId, $operasi, $data, $bound, $petunjuk): Krs {
                $c = $this->muatTerkunci($pelakuId, $petunjuk);
                $krs = $c['krs'];

                if ($operasi === 'buat' && $krs !== null) {
                    $this->gagal('registrasi_semester_id', 'Registrasi ini sudah memiliki KRS. Buka KRS tersebut; pemulihan memakai baris yang sama.');
                }

                if ($bound !== null) {
                    abort_unless($krs && $krs->id === $bound->id, 404);
                    $token = $data['versi_form'] ?? null;
                    if (! is_string($token) || ! hash_equals($krs->versiForm(), $token)) {
                        $this->gagal('versi_form', 'KRS, registrasi, atau kelas telah berubah. Muat ulang halaman sebelum melakukan tindakan.');
                    }

                    $diizinkan = $operasi === 'ubah'
                        ? $krs->status === Krs::DRAF
                        : array_key_exists($operasi, $krs->operasiTersedia());

                    if (! $diizinkan) {
                        $this->gagal('krs', 'Tindakan tidak sesuai status KRS terbaru.');
                    }
                    if ($krs->versi >= 4294967295) {
                        $this->gagal('krs', 'Batas versi tercapai. Hubungi pengelola sistem.');
                    }
                }

                $alasan = $data['alasan'] ?? null;
                if (
                    in_array($operasi, ['kembalikan', 'revisi', 'pulihkan', 'batalkan'], true)
                    && (! is_string($alasan) || mb_strlen(trim($alasan)) < 10 || mb_strlen($alasan) > 2000)
                ) {
                    $this->gagal('alasan', 'Alasan tindakan wajib diisi 10–2.000 karakter.');
                }

                if ($operasi !== 'batalkan') {
                    $roleMahasiswa = $c['pemilik']->roles()->where('roles.kode', Role::MAHASISWA)
                        ->lockForUpdate()->first(['roles.id']);
                    if (! $c['pemilik']->isAktif() || $roleMahasiswa === null) {
                        $this->gagal('krs', 'Akun pemilik harus aktif dan memiliki role mahasiswa.');
                    }
                }

                // Waktu diperiksa setelah seluruh lock yang diperlukan diperoleh.
                $waktu = CarbonImmutable::now('UTC');
                $this->pastikanKelayakan($c, $operasi, $waktu);
                $sebelum = $krs?->ringkasanAudit();

                if ($krs === null) {
                    $krs = new Krs();
                    $krs->registrasi_semester_id = $c['registrasi']->id;
                    $krs->versi = 1;
                    $krs->setRelation('registrasiSemester', $c['registrasi']);
                } else {
                    $krs->versi++;
                }

                if (in_array($operasi, ['buat', 'ubah'], true)) {
                    $catatan = $data['catatan'] ?? null;
                    if ($catatan !== null && (! is_string($catatan) || mb_strlen($catatan) > 2000)) {
                        $this->gagal('catatan', 'Catatan maksimal 2.000 karakter.');
                    }
                    $krs->catatan = $catatan === null ? null : (trim($catatan) ?: null);
                }

                $krs->status = match ($operasi) {
                    'buat', 'ubah', 'kembalikan', 'revisi', 'pulihkan' => Krs::DRAF,
                    'ajukan' => Krs::DIAJUKAN,
                    'sahkan' => Krs::DISAHKAN,
                    'batalkan' => Krs::DIBATALKAN,
                };

                if ($krs->status === Krs::DRAF) {
                    $krs->diajukan_at = null;
                    $krs->disahkan_at = null;
                    $krs->disahkan_oleh = null;
                } elseif ($operasi === 'ajukan') {
                    $krs->diajukan_at = $waktu;
                } elseif ($operasi === 'sahkan') {
                    $krs->disahkan_at = $waktu;
                    $krs->disahkan_oleh = $c['pelaku']->id;
                }

                $krs->save();

                $details = $c['details'];
                if ($operasi === 'buat') {
                    foreach ($c['kelas'] as $kelas) {
                        $detail = new DetailKrs();
                        $detail->krs_id = $krs->id;
                        $detail->kelas_kuliah_id = $kelas->id;
                        $detail->status = DetailKrs::TERDAFTAR;
                        $detail->aktif_at = null;
                        $detail->batal_at = null;
                        $detail->save();
                        $detail->setRelation('kelasKuliah', $kelas);
                        $details->push($detail);
                    }
                } else {
                    foreach ($details as $detail) {
                        if ($krs->status === Krs::DISAHKAN) {
                            $detail->status = DetailKrs::AKTIF;
                            $detail->aktif_at = $waktu;
                            $detail->batal_at = null;
                        } elseif ($krs->status === Krs::DIBATALKAN) {
                            $detail->status = DetailKrs::DIBATALKAN;
                            $detail->batal_at = $waktu;
                        } else {
                            $detail->status = DetailKrs::TERDAFTAR;
                            $detail->aktif_at = null;
                            $detail->batal_at = null;
                        }

                        if ($detail->isDirty()) {
                            $detail->save();
                        }
                    }
                }

                $krs->setRelation('details', $details);
                $this->pastikanStatusDetail($krs, $details);

                $audit = new AuditLog();
                $audit->pelaku_id = $c['pelaku']->id;
                $audit->entitas = 'krs';
                $audit->entitas_id = $krs->id;
                $audit->versi_entitas = $krs->versi;
                $audit->aksi = $operasi;
                $audit->sebelum = $sebelum;
                $audit->sesudah = $krs->ringkasanAudit();
                $audit->alasan = is_string($alasan) ? trim($alasan) : null;
                $audit->waktu = $waktu;
                $audit->save();

                return $krs;
            }, 3);
        } catch (QueryException $exception) {
            $kode = (int) ($exception->errorInfo[1] ?? 0);
            if ($kode === 1062) {
                $this->gagal('krs', 'Data KRS atau versinya sudah tersimpan. Muat ulang halaman; jangan mengirim ulang formulir lama.');
            }
            if (in_array($kode, [1205, 1213], true)) {
                $this->gagal('krs', 'Data sedang diproses bersamaan. Muat ulang halaman lalu coba kembali.');
            }
            throw $exception;
        }
    }

    public function untukCetak(int $pelakuId, Krs $bound): Krs
    {
        $petunjuk = $this->petunjukRegistrasi($bound->registrasi_semester_id);

        return DB::transaction(function () use ($pelakuId, $bound, $petunjuk): Krs {
            $c = $this->muatTerkunci($pelakuId, $petunjuk);
            $krs = $c['krs'];
            abort_unless($krs && $krs->id === $bound->id, 404);
            abort_unless($krs->status === Krs::DISAHKAN, 409, 'Hanya KRS yang sedang disahkan dapat dicetak.');

            // Identitas, kelas, header, dan detail dibaca dalam transaksi yang sama.
            $pengesah = User::query()->whereKey($krs->disahkan_oleh)->lockForUpdate()->firstOrFail();
            $krs->setRelation('pengesah', $pengesah);

            return $krs;
        }, 3);
    }

    private function petunjukRegistrasi(int $id): RegistrasiSemester
    {
        return RegistrasiSemester::query()->with(['riwayatStudi', 'rombel'])->findOrFail($id);
    }

    private function muatTerkunci(int $pelakuId, RegistrasiSemester $petunjuk): array
    {
        $pelaku = User::query()->whereKey($pelakuId)->lockForUpdate()->firstOrFail();
        $roleAdmin = $pelaku->roles()->where('roles.kode', Role::ADMIN_AKADEMIK)
            ->lockForUpdate()->first(['roles.id']);
        abort_unless($pelaku->isAktif() && $roleAdmin !== null, 403);
        Gate::forUser($pelaku)->authorize('kelola-krs');

        $mahasiswa = Mahasiswa::query()->whereKey($petunjuk->riwayatStudi->mahasiswa_id)
            ->lockForUpdate()->firstOrFail();
        $riwayat = RiwayatStudi::query()->whereKey($petunjuk->riwayat_studi_id)
            ->where('mahasiswa_id', $mahasiswa->id)->lockForUpdate()->firstOrFail();
        $kurikulum = Kurikulum::query()->whereKey($riwayat->kurikulum_id)->lockForUpdate()->firstOrFail();
        $prodi = ProgramStudi::query()->whereKey($kurikulum->program_studi_id)->lockForUpdate()->firstOrFail();
        $paket = PaketSemester::query()->whereKey($petunjuk->rombel->paket_semester_id)
            ->lockForUpdate()->firstOrFail();
        $periode = PeriodeAkademik::query()->whereKey($petunjuk->periode_akademik_id)
            ->lockForUpdate()->firstOrFail();
        $rombel = Rombel::query()->whereKey($petunjuk->rombel_id)->lockForUpdate()->firstOrFail();
        $isiPaket = DetailPaket::query()->where('paket_semester_id', $paket->id)
            ->orderBy('id')->lockForUpdate()->get();
        $kelas = KelasKuliah::query()->where('rombel_id', $rombel->id)
            ->orderBy('id')->lockForUpdate()->get();
        $registrasi = RegistrasiSemester::query()->whereKey($petunjuk->id)->lockForUpdate()->firstOrFail();

        if (
            (int) $registrasi->riwayat_studi_id !== (int) $riwayat->id
            || (int) $registrasi->rombel_id !== (int) $rombel->id
            || (int) $registrasi->periode_akademik_id !== (int) $periode->id
            || (int) $rombel->periode_akademik_id !== (int) $periode->id
            || (int) $rombel->paket_semester_id !== (int) $paket->id
            || (int) $paket->kurikulum_id !== (int) $kurikulum->id
            || (int) $registrasi->semester_studi !== (int) $paket->semester_studi
        ) {
            $this->gagal('registrasi_semester_id', 'Hubungan registrasi, riwayat, periode, atau paket berubah/tidak sesuai. Muat ulang halaman.');
        }

        if (
            $isiPaket->isEmpty()
            || $this->urutId($isiPaket->modelKeys()) !== $this->urutId($kelas->pluck('detail_paket_id')->all())
        ) {
            $this->gagal('krs', 'Seluruh mata kuliah paket harus memiliki tepat satu kelas pada rombel ini. Lengkapi Kelas Kuliah dahulu.');
        }

        $krs = Krs::query()->where('registrasi_semester_id', $registrasi->id)->lockForUpdate()->first();
        $details = $krs === null ? new Collection() : DetailKrs::query()
            ->where('krs_id', $krs->id)->orderBy('id')->lockForUpdate()->get();
        $pemilik = User::query()->whereKey($mahasiswa->user_id)->lockForUpdate()->firstOrFail();

        $mahasiswa->setRelation('user', $pemilik);
        $kurikulum->setRelation('programStudi', $prodi);
        $riwayat->setRelation('mahasiswa', $mahasiswa)->setRelation('kurikulum', $kurikulum);
        $paket->setRelation('kurikulum', $kurikulum)->setRelation('details', $isiPaket);
        $rombel->setRelation('paketSemester', $paket)->setRelation('periodeAkademik', $periode);
        $registrasi->setRelation('riwayatStudi', $riwayat)
            ->setRelation('periodeAkademik', $periode)->setRelation('rombel', $rombel);

        if ($krs !== null) {
            if ($this->urutId($details->pluck('kelas_kuliah_id')->all()) !== $this->urutId($kelas->modelKeys())) {
                $this->gagal('krs', 'Detail KRS tidak sama dengan paket lengkap. Hubungi pengelola sistem untuk memeriksa integritas data.');
            }
            $kelasPerId = $kelas->keyBy('id');
            foreach ($details as $detail) {
                $detail->setRelation('kelasKuliah', $kelasPerId->get($detail->kelas_kuliah_id));
            }
            $krs->setRelation('registrasiSemester', $registrasi)->setRelation('details', $details);
            $this->pastikanStatusDetail($krs, $details);
        }

        return compact(
            'pelaku',
            'mahasiswa',
            'riwayat',
            'kurikulum',
            'prodi',
            'paket',
            'periode',
            'rombel',
            'registrasi',
            'kelas',
            'krs',
            'details',
            'pemilik'
        );
    }

    private function pastikanKelayakan(array $c, string $operasi, CarbonImmutable $waktu): void
    {
        if ($c['riwayat']->status !== RiwayatStudi::AKTIF) {
            $this->gagal('krs', 'Riwayat studi sudah ditutup. KRS hanya dapat dibaca.');
        }

        if ($operasi === 'batalkan') {
            if (! in_array($c['periode']->status, Rombel::STATUS_PERIODE_TERBUKA, true)) {
                $this->gagal('krs', 'Periode sudah diarsipkan. Pembatalan tidak diperbolehkan.');
            }
            return;
        }

        if (
            $c['registrasi']->status !== RegistrasiSemester::AKTIF
            || $c['registrasi']->penempatan_dikunci_at === null
        ) {
            $this->gagal('krs', 'Registrasi semester harus aktif dengan penempatan yang sudah terkunci.');
        }
        if ($c['periode']->status !== 'aktif') {
            $this->gagal('krs', 'Periode akademik harus aktif untuk tindakan ini.');
        }

        if (in_array($operasi, ['buat', 'ubah', 'ajukan', 'revisi', 'pulihkan'], true)) {
            $mulai = $c['periode']->krs_mulai;
            $selesai = $c['periode']->krs_selesai;
            if ($mulai === null || $selesai === null || $waktu->lt($mulai) || $waktu->gt($selesai)) {
                $this->gagal('krs', 'Jendela pengisian KRS belum dibuka atau sudah ditutup. Periksa jadwal periode akademik.');
            }
        }

        if ($operasi !== 'kembalikan') {
            if (
                $c['kurikulum']->status !== Kurikulum::AKTIF || ! $c['prodi']->aktif
                || ! in_array($c['paket']->status, [PaketSemester::DITERBITKAN, PaketSemester::ARSIP], true)
            ) {
                $this->gagal('krs', 'Kurikulum/prodi harus aktif dan paket rombel harus sudah diterbitkan.');
            }

            $statusKelas = in_array($operasi, ['ajukan', 'sahkan'], true)
                ? [KelasKuliah::AKTIF] : [KelasKuliah::PERSIAPAN, KelasKuliah::AKTIF];
            if ($c['kelas']->contains(fn(KelasKuliah $kelas): bool => ! in_array($kelas->status, $statusKelas, true))) {
                $this->gagal('krs', in_array($operasi, ['ajukan', 'sahkan'], true)
                    ? 'Seluruh kelas paket harus aktif sebelum pengajuan/pengesahan.'
                    : 'Kelas paket sudah selesai/diarsipkan; draf atau revisi tidak dapat dibuka.');
            }
        }
    }

    private function pastikanStatusDetail(Krs $krs, Collection $details): void
    {
        $status = match ($krs->status) {
            Krs::DRAF, Krs::DIAJUKAN => DetailKrs::TERDAFTAR,
            Krs::DISAHKAN => DetailKrs::AKTIF,
            Krs::DIBATALKAN => DetailKrs::DIBATALKAN,
            default => null,
        };

        if (
            $status === null || $details->isEmpty()
            || $details->contains(fn(DetailKrs $detail): bool => $detail->status !== $status)
        ) {
            $this->gagal('krs', 'Status header dan seluruh detail KRS harus konsisten.');
        }
    }

    private function urutId(array $ids): array
    {
        $ids = array_map('intval', $ids);
        sort($ids, SORT_NUMERIC);
        return $ids;
    }

    private function gagal(string $field, string $pesan): never
    {
        throw ValidationException::withMessages([$field => $pesan]);
    }
}
