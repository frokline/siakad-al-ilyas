<?php

namespace App\Actions;

use App\Models\Kurikulum;
use App\Models\KurikulumMataKuliah;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\PaketSemester;
use App\Models\PeriodeAkademik;
use App\Models\ProgramStudi;
use App\Models\RegistrasiSemester;
use App\Models\RiwayatStudi;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SimpanRegistrasiSemester
{
    public function execute(
        int $actorId,
        array $data,
        ?RegistrasiSemester $bound = null
    ): RegistrasiSemester {
        // Hanya identitas parent yang immutable dipakai sebagai petunjuk penguncian.
        $riwayatId = $bound?->riwayat_studi_id ?? (int) $data['riwayat_studi_id'];
        $rombelId = isset($data['rombel_id'])
            ? (int) $data['rombel_id']
            : $bound?->rombel_id;

        $petunjukRiwayat = RiwayatStudi::query()->findOrFail($riwayatId);
        $petunjukRombel = Rombel::query()->findOrFail($rombelId);

        if ($bound && $petunjukRombel->periode_akademik_id !== $bound->periode_akademik_id) {
            $this->gagal('rombel_id', 'Rombel pengganti harus berada pada periode yang sama.');
        }

        try {
            return DB::transaction(function () use (
                $actorId,
                $data,
                $bound,
                $petunjukRiwayat,
                $petunjukRombel
            ): RegistrasiSemester {
                $actor = User::query()->whereKey($actorId)->lockForUpdate()->firstOrFail();
                Gate::forUser($actor)->authorize('kelola-registrasi-semester');

                $mahasiswa = Mahasiswa::query()
                    ->whereKey($petunjukRiwayat->mahasiswa_id)
                    ->lockForUpdate()->firstOrFail();

                // Mencegah registrasi ganda lintas riwayat untuk mahasiswa yang sama.
                $semuaRiwayat = RiwayatStudi::query()
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->orderBy('id')->lockForUpdate()->get();

                $riwayat = $semuaRiwayat->firstWhere('id', $petunjukRiwayat->id);
                abort_unless($riwayat, 404);

                if ($riwayat->status !== RiwayatStudi::AKTIF) {
                    $this->gagal('riwayat_studi_id', 'Riwayat studi sudah ditutup dan tidak dapat diubah.');
                }

                $kurikulum = Kurikulum::query()
                    ->whereKey($riwayat->kurikulum_id)
                    ->lockForUpdate()->firstOrFail();

                $prodi = ProgramStudi::query()
                    ->whereKey($kurikulum->program_studi_id)
                    ->lockForUpdate()->firstOrFail();

                $paket = PaketSemester::query()
                    ->whereKey($petunjukRombel->paket_semester_id)
                    ->lockForUpdate()->firstOrFail();

                $periodeIds = array_values(array_unique([
                    (int) $riwayat->periode_mulai_id,
                    (int) $petunjukRombel->periode_akademik_id,
                ]));
                sort($periodeIds);

                $periodeTerkunci = PeriodeAkademik::query()
                    ->whereKey($periodeIds)->orderBy('id')
                    ->lockForUpdate()->get()->keyBy('id');

                $periode = $periodeTerkunci->get($petunjukRombel->periode_akademik_id);
                $mulaiStudi = $periodeTerkunci->get($riwayat->periode_mulai_id);
                abort_unless($periode && $mulaiStudi, 404);

                if (! in_array($periode->status, Rombel::STATUS_PERIODE_TERBUKA, true)) {
                    $this->gagal('rombel_id', 'Periode telah diarsipkan.');
                }

                if ($periode->mulai->lt($mulaiStudi->mulai)) {
                    $this->gagal('rombel_id', 'Periode registrasi mendahului periode mulai studi.');
                }

                $rombelIds = [$petunjukRombel->id];
                if ($bound) {
                    $rombelIds[] = $bound->rombel_id;
                }
                $rombelIds = array_values(array_unique($rombelIds));
                sort($rombelIds);

                $rombels = Rombel::query()->whereKey($rombelIds)
                    ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                $rombel = $rombels->get($petunjukRombel->id);
                abort_unless($rombel, 404);

                if (
                    $rombel->periode_akademik_id !== $periode->id
                    || $rombel->paket_semester_id !== $paket->id
                    || $paket->kurikulum_id !== $riwayat->kurikulum_id
                ) {
                    $this->gagal('rombel_id', 'Periode atau kurikulum rombel tidak sesuai riwayat studi.');
                }

                $registrasiPeriode = RegistrasiSemester::query()
                    ->whereIn('riwayat_studi_id', $semuaRiwayat->modelKeys())
                    ->where('periode_akademik_id', $periode->id)
                    ->orderBy('id')->lockForUpdate()->get();

                if ($bound) {
                    $registrasi = $registrasiPeriode->firstWhere('id', $bound->id);
                    abort_unless($registrasi, 404);

                    if (! hash_equals($registrasi->versiForm(), (string) ($data['versi'] ?? ''))) {
                        $this->gagal('versi', 'Data sudah berubah. Muat ulang halaman sebelum menyimpan.');
                    }

                    $status = (string) $data['status'];

                    if (! array_key_exists($status, $registrasi->pilihanStatus())) {
                        $this->gagal('status', 'Perubahan status tidak diizinkan.');
                    }

                    if (
                        $registrasi->rombel_id !== $rombel->id
                        && ! $registrasi->penempatanDapatDiubah()
                    ) {
                        $this->gagal('rombel_id', 'Penempatan terkunci sejak aktivasi pertama.');
                    }
                } else {
                    if ($registrasiPeriode->contains('riwayat_studi_id', $riwayat->id)) {
                        $this->gagal(
                            'riwayat_studi_id',
                            'Riwayat ini sudah memiliki registrasi pada periode tersebut. Buka registrasi yang ada.'
                        );
                    }

                    $registrasi = new RegistrasiSemester();
                    $registrasi->riwayat_studi_id = $riwayat->id;
                    $registrasi->periode_akademik_id = $periode->id;
                    $status = RegistrasiSemester::TERDAFTAR;
                }

                $penempatanBaru = ! $registrasi->exists || $registrasi->rombel_id !== $rombel->id;

                if ($penempatanBaru) {
                    if (
                        $kurikulum->status !== 'aktif' || ! $prodi->aktif
                        || $paket->status !== PaketSemester::DITERBITKAN
                    ) {
                        $this->gagal('rombel_id', 'Penempatan baru memerlukan prodi dan kurikulum aktif serta paket terbit.');
                    }

                    $this->periksaIsiPaket($paket, $prodi->id);
                }

                if ($status !== RegistrasiSemester::BATAL) {
                    $ganda = $registrasiPeriode->first(
                        fn(RegistrasiSemester $lain): bool =>
                        $lain->id !== $registrasi->id
                            && $lain->riwayat_studi_id !== $riwayat->id
                            && $lain->status !== RegistrasiSemester::BATAL
                    );

                    if ($ganda) {
                        $this->gagal(
                            'riwayat_studi_id',
                            'Mahasiswa masih memiliki registrasi pada riwayat lain dalam periode yang sama.'
                        );
                    }
                }

                if (in_array($status, RegistrasiSemester::MENGISI_KURSI, true)) {
                    $akun = User::query()->whereKey($mahasiswa->user_id)
                        ->lockForUpdate()->firstOrFail();

                    $role = $akun->roles()->where('roles.kode', Role::MAHASISWA)
                        ->lockForUpdate()->first(['roles.id']);

                    if (! $akun->isAktif() || $role === null) {
                        $this->gagal('riwayat_studi_id', 'Akun mahasiswa harus aktif dan memiliki peran mahasiswa.');
                    }

                    if (
                        $rombel->kapasitas !== null
                        && $rombel->hitungKursiTerkunci($registrasi->exists ? $registrasi->id : null)
                        >= $rombel->kapasitas
                    ) {
                        $this->gagal('rombel_id', 'Kapasitas rombel telah penuh. Pilih rombel lain.');
                    }
                }

                if ($registrasi->exists && $registrasi->revisi >= 4294967295) {
                    $this->gagal('versi', 'Batas revisi tercapai. Hubungi pengelola sistem.');
                }

                $registrasi->rombel_id = $rombel->id;
                $registrasi->semester_studi = (int) $paket->semester_studi;
                $registrasi->status = $status;
                $registrasi->alasan_status = in_array(
                    $status,
                    [RegistrasiSemester::CUTI, RegistrasiSemester::BATAL],
                    true
                ) ? trim((string) ($data['alasan_status'] ?? '')) : null;

                if (
                    $status === RegistrasiSemester::AKTIF
                    && $registrasi->penempatan_dikunci_at === null
                ) {
                    $registrasi->penempatan_dikunci_at = now('UTC');
                }

                $registrasi->revisi = $registrasi->exists ? $registrasi->revisi + 1 : 1;
                $registrasi->save();

                return $registrasi;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            $this->gagal('riwayat_studi_id', 'Registrasi periode ini sudah dibuat. Buka daftar dan muat ulang.');
        }
    }

    private function periksaIsiPaket(PaketSemester $paket, int $programStudiId): void
    {
        $details = $paket->details()->orderBy('id')->lockForUpdate()->get();

        if ($details->isEmpty()) {
            $this->gagal('rombel_id', 'Paket rombel belum memiliki mata kuliah.');
        }

        $kmk = KurikulumMataKuliah::query()
            ->whereKey($details->pluck('kurikulum_mata_kuliah_id')->all())
            ->where('kurikulum_id', $paket->kurikulum_id)
            ->orderBy('id')->lockForUpdate()->get();

        if ($kmk->count() !== $details->count()) {
            $this->gagal('rombel_id', 'Isi paket tidak sesuai kurikulum.');
        }

        $mataKuliah = MataKuliah::query()
            ->whereKey($kmk->pluck('mata_kuliah_id')->unique()->all())
            ->orderBy('id')->lockForUpdate()->get();

        if (
            $mataKuliah->count() !== $kmk->pluck('mata_kuliah_id')->unique()->count()
            || $mataKuliah->contains(
                fn(MataKuliah $mk): bool => ! $mk->aktif || $mk->program_studi_id !== $programStudiId
            )
        ) {
            $this->gagal('rombel_id', 'Paket memuat mata kuliah yang tidak aktif atau berbeda program studi.');
        }
    }

    private function gagal(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
