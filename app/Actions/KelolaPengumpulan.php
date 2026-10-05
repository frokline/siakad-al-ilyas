<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Berkas;
use App\Models\Kegiatan;
use App\Models\Pengumpulan;
use App\Models\PengumpulanBerkas;
use App\Models\User;
use App\Services\AksesPengumpulan;
use App\Services\AturanJawaban;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class KelolaPengumpulan
{
    public function __construct(
        private readonly AksesPengumpulan $akses
    ) {
    }

    /**
     * Menyimpan jawaban mahasiswa secara langsung.
     *
     * Jika belum ada pengumpulan, sistem membuat pengumpulan baru.
     * Jika sudah ada, jawaban diperbarui selama tenggat belum lewat.
     */
    public function simpan(
        int $userId,
        Kegiatan $bound,
        array $data
    ): Pengumpulan {
        $teks = AturanJawaban::teks(
            $data['jawaban_teks'] ?? null
        );

        $ids = AturanJawaban::ids(
            $data['berkas_ids'] ?? []
        );

        if ($teks === null && $ids === []) {
            AturanJawaban::gagal(
                'Isi pesan jawaban atau unggah minimal satu berkas.'
            );
        }

        return DB::transaction(
            function () use (
                $userId,
                $bound,
                $data,
                $teks,
                $ids
            ): Pengumpulan {
                $user = User::query()->findOrFail($userId);

                $detailId = $this->akses->detail(
                    $user,
                    $bound
                );

                abort_unless(
                    $detailId !== null,
                    403,
                    'Keikutsertaan aktif tidak tersedia atau ganda.'
                );

                $konteks = $this->kunci(
                    $userId,
                    (int) $bound->getKey(),
                    $detailId
                );

                $waktu = $this->bolehMenulis($konteks);

                $pengumpulan = Pengumpulan::query()
                    ->where(
                        'kegiatan_id',
                        $konteks['kegiatan']->getKey()
                    )
                    ->where(
                        'detail_krs_id',
                        $konteks['detail']->id
                    )
                    ->lockForUpdate()
                    ->first();

                if ($pengumpulan !== null) {
                    return $this->perbarui(
                        $pengumpulan,
                        $userId,
                        $konteks,
                        $data,
                        $teks,
                        $ids,
                        $waktu
                    );
                }

                $pengumpulan = new Pengumpulan();
                $pengumpulan->kegiatan_id =
                    $konteks['kegiatan']->getKey();
                $pengumpulan->detail_krs_id =
                    $konteks['detail']->id;
                $pengumpulan->pemilik_id = $userId;
                $pengumpulan->jawaban_teks = $teks;
                $pengumpulan->status = Pengumpulan::TERKIRIM;
                $pengumpulan->dikirim_at =
                    $waktu->startOfSecond();
                $pengumpulan->diubah_at = null;
                $pengumpulan->dibatalkan_at = null;
                $pengumpulan->tenggat_snapshot =
                    $konteks['kegiatan']->tenggat_at;
                $pengumpulan->revisi_kegiatan =
                    $konteks['kegiatan']->revisi;
                $pengumpulan->hash_jawaban =
                    $this->buatHash($teks, $ids);
                $pengumpulan->revisi = 1;
                $pengumpulan->save();

                $this->lampiran(
                    $pengumpulan,
                    $konteks['kegiatan'],
                    $ids
                );

                $this->audit(
                    $pengumpulan,
                    $userId,
                    'kirim_jawaban',
                    null
                );

                return $pengumpulan->fresh([
                    'lampiran',
                    'kegiatan',
                ]);
            },
            3
        );
    }

    /**
     * Membatalkan jawaban sebelum tenggat berakhir.
     *
     * Data dan lampiran tetap dipertahankan untuk kebutuhan audit,
     * tetapi tidak dianggap sebagai pengumpulan aktif.
     */
    public function batalkan(
        int $userId,
        Pengumpulan $bound,
        array $data = []
    ): Pengumpulan {
        return DB::transaction(
            function () use (
                $userId,
                $bound,
                $data
            ): Pengumpulan {
                $konteks = $this->kunci(
                    $userId,
                    (int) $bound->kegiatan_id,
                    (int) $bound->detail_krs_id
                );

                $pengumpulan = $this->temukanPengumpulan(
                    (int) $bound->getKey(),
                    $userId,
                    $konteks
                );

                if (
                    $pengumpulan->status ===
                    Pengumpulan::DIBATALKAN
                ) {
                    return $pengumpulan;
                }

                $this->periksaVersi(
                    $pengumpulan,
                    $data
                );

                $waktu = $this->bolehMenulis($konteks);
                $sebelum = $pengumpulan->ringkasanAudit();

                $pengumpulan->status =
                    Pengumpulan::DIBATALKAN;
                $pengumpulan->dibatalkan_at =
                    $waktu->startOfSecond();
                $pengumpulan->diubah_at =
                    $waktu->startOfSecond();
                $pengumpulan->revisi++;

                $pengumpulan->save();

                $this->audit(
                    $pengumpulan,
                    $userId,
                    'batalkan_jawaban',
                    $sebelum
                );

                return $pengumpulan;
            },
            3
        );
    }

    private function perbarui(
        Pengumpulan $pengumpulan,
        int $userId,
        array $konteks,
        array $data,
        ?string $teks,
        array $ids,
        CarbonImmutable $waktu
    ): Pengumpulan {
        abort_unless(
            (int) $pengumpulan->pemilik_id === $userId,
            403
        );

        if (
            $pengumpulan->status ===
            Pengumpulan::DIBATALKAN
        ) {
            AturanJawaban::gagal(
                'Jawaban ini sudah dihapus. Muat ulang halaman kegiatan.'
            );
        }

        $this->periksaVersi(
            $pengumpulan,
            $data
        );

        $sebelum = $pengumpulan->ringkasanAudit();

        $this->lampiran(
            $pengumpulan,
            $konteks['kegiatan'],
            $ids
        );

        /*
         * Waktu diperiksa kembali setelah seluruh berkas dikunci.
         * Ini mencegah perubahan yang mulai sebelum tenggat tetapi
         * selesai setelah tenggat.
         */
        $waktu = $this->bolehMenulis($konteks);

        $pengumpulan->jawaban_teks = $teks;
        $pengumpulan->status = Pengumpulan::TERKIRIM;
        $pengumpulan->diubah_at = $waktu->startOfSecond();
        $pengumpulan->dibatalkan_at = null;
        $pengumpulan->tenggat_snapshot =
            $konteks['kegiatan']->tenggat_at;
        $pengumpulan->revisi_kegiatan =
            $konteks['kegiatan']->revisi;
        $pengumpulan->hash_jawaban =
            $this->buatHash($teks, $ids);
        $pengumpulan->revisi++;

        $pengumpulan->save();

        $this->audit(
            $pengumpulan,
            $userId,
            'ubah_jawaban',
            $sebelum
        );

        return $pengumpulan->fresh([
            'lampiran',
            'kegiatan',
        ]);
    }

    /**
     * Mengunci seluruh hubungan akademik yang menentukan hak
     * mahasiswa atas kegiatan.
     */
    private function kunci(
        int $userId,
        int $kegiatanId,
        int $detailId
    ): array {
        $user = User::query()
            ->whereKey($userId)
            ->lockForUpdate()
            ->firstOrFail();

        $roles = $user->roles()
            ->orderBy('roles.id')
            ->lockForUpdate()
            ->get([
                'roles.id',
                'roles.kode',
            ]);

        abort_unless(
            $user->status === 'aktif'
                && $roles->contains(
                    'kode',
                    'mahasiswa'
                ),
            403
        );

        $petunjuk = DB::table('detail_krs as d')
            ->join(
                'krs as k',
                'k.id',
                '=',
                'd.krs_id'
            )
            ->join(
                'registrasi_semester as r',
                'r.id',
                '=',
                'k.registrasi_semester_id'
            )
            ->join(
                'riwayat_studi as h',
                'h.id',
                '=',
                'r.riwayat_studi_id'
            )
            ->join(
                'mahasiswa as m',
                'm.id',
                '=',
                'h.mahasiswa_id'
            )
            ->where('d.id', $detailId)
            ->where('m.user_id', $userId)
            ->first([
                'd.krs_id',
                'd.kelas_kuliah_id',
                'k.registrasi_semester_id',
                'r.riwayat_studi_id',
                'h.mahasiswa_id',
            ]);

        abort_unless(
            $petunjuk !== null,
            403,
            'Data peserta tidak ditemukan.'
        );

        $mahasiswa = $this->baris(
            'mahasiswa',
            (int) $petunjuk->mahasiswa_id
        );

        $riwayat = $this->baris(
            'riwayat_studi',
            (int) $petunjuk->riwayat_studi_id
        );

        $petunjukKelas = DB::table('kelas_kuliah')
            ->where(
                'id',
                $petunjuk->kelas_kuliah_id
            )
            ->firstOrFail();

        $petunjukRombel = DB::table('rombel')
            ->where(
                'id',
                $petunjukKelas->rombel_id
            )
            ->firstOrFail();

        $periode = $this->baris(
            'periode_akademik',
            (int) $petunjukRombel->periode_akademik_id
        );

        $rombel = $this->baris(
            'rombel',
            (int) $petunjukKelas->rombel_id
        );

        $kelas = $this->baris(
            'kelas_kuliah',
            (int) $petunjuk->kelas_kuliah_id
        );

        $registrasi = $this->baris(
            'registrasi_semester',
            (int) $petunjuk->registrasi_semester_id
        );

        $krs = $this->baris(
            'krs',
            (int) $petunjuk->krs_id
        );

        $detail = $this->baris(
            'detail_krs',
            $detailId
        );

        $kegiatan = Kegiatan::query()
            ->whereKey($kegiatanId)
            ->lockForUpdate()
            ->firstOrFail();

        $pertemuan = $kegiatan->pertemuan_id === null
            ? null
            : $this->baris(
                'pertemuan',
                (int) $kegiatan->pertemuan_id
            );

        $sesuai =
            (int) $mahasiswa->user_id === $userId
            && (int) $riwayat->mahasiswa_id
                === (int) $mahasiswa->id
            && (int) $registrasi->riwayat_studi_id
                === (int) $riwayat->id
            && (int) $krs->registrasi_semester_id
                === (int) $registrasi->id
            && (int) $detail->krs_id
                === (int) $krs->id
            && (int) $detail->kelas_kuliah_id
                === (int) $kelas->id
            && (int) $kegiatan->kelas_kuliah_id
                === (int) $kelas->id
            && (int) $kelas->rombel_id
                === (int) $rombel->id
            && (int) $rombel->periode_akademik_id
                === (int) $periode->id
            && (int) $registrasi->rombel_id
                === (int) $rombel->id
            && (int) $registrasi->periode_akademik_id
                === (int) $periode->id
            && (
                $pertemuan === null
                || (int) $pertemuan->kelas_kuliah_id
                    === (int) $kelas->id
            );

        if (! $sesuai) {
            AturanJawaban::gagal(
                'Hubungan peserta, kegiatan, kelas, atau periode tidak sesuai. Hubungi akademik.'
            );
        }

        return compact(
            'user',
            'mahasiswa',
            'riwayat',
            'periode',
            'rombel',
            'kelas',
            'registrasi',
            'krs',
            'detail',
            'kegiatan',
            'pertemuan'
        );
    }

    private function baris(
        string $tabel,
        int $id
    ): object {
        return DB::table($tabel)
            ->where('id', $id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function temukanPengumpulan(
        int $id,
        int $userId,
        array $konteks
    ): Pengumpulan {
        return Pengumpulan::query()
            ->whereKey($id)
            ->where('pemilik_id', $userId)
            ->where(
                'kegiatan_id',
                $konteks['kegiatan']->getKey()
            )
            ->where(
                'detail_krs_id',
                $konteks['detail']->id
            )
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function bolehMenulis(
        array $konteks
    ): CarbonImmutable {
        $waktu = CarbonImmutable::now('UTC');

        $boleh =
            $konteks['detail']->status === 'aktif'
            && $konteks['krs']->status === 'disahkan'
            && $konteks['registrasi']->status === 'aktif'
            && $konteks['riwayat']->status === 'aktif'
            && $konteks['kelas']->status === 'aktif'
            && $konteks['periode']->status === 'aktif'
            && (
                $konteks['pertemuan'] === null
                || $konteks['pertemuan']->status !== 'batal'
            )
            && $konteks['kegiatan']->metode
                === 'pengumpulan_berkas'
            && $konteks['kegiatan']->jendelaTerbuka(
                $waktu
            );

        if (! $boleh) {
            AturanJawaban::gagal(
                'Pengumpulan belum dibuka, sudah ditutup, tenggat telah lewat, atau status akademik tidak aktif.'
            );
        }

        return $waktu;
    }

    private function periksaVersi(
        Pengumpulan $pengumpulan,
        array $data
    ): void {
        if (! isset($data['versi_form'])) {
            return;
        }

        if (
            ! is_string($data['versi_form'])
            || ! hash_equals(
                $pengumpulan->versiForm(),
                $data['versi_form']
            )
            || $pengumpulan->revisi >= 4294967295
        ) {
            AturanJawaban::gagal(
                'Jawaban berubah di halaman lain. Muat ulang halaman sebelum menyimpan kembali.'
            );
        }
    }

    private function lampiran(
        Pengumpulan $pengumpulan,
        Kegiatan $kegiatan,
        array $ids
    ): void {
        if (count($ids) > $kegiatan->maks_berkas) {
            AturanJawaban::gagal(
                'Jumlah berkas melebihi batas kegiatan.'
            );
        }

        $lama = $pengumpulan
            ->semuaLampiran()
            ->orderBy('berkas_id')
            ->lockForUpdate()
            ->get()
            ->keyBy('berkas_id');

        $semuaId = array_values(
            array_unique([
                ...$ids,
                ...$lama->keys()
                    ->map(
                        fn ($id): int => (int) $id
                    )
                    ->all(),
            ])
        );

        sort($semuaId, SORT_NUMERIC);

        $berkas = Berkas::query()
            ->whereIn('id', $semuaId)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($ids as $id) {
            $file = $berkas->get($id);

            if ($file === null) {
                AturanJawaban::gagal(
                    'Salah satu berkas tidak tersedia.'
                );
            }

            AturanJawaban::berkas(
                $file,
                $kegiatan,
                (int) $pengumpulan->pemilik_id
            );

            $baris = $lama->get($id);

            if (
                $baris !== null
                && ! $baris->cocok($file)
            ) {
                AturanJawaban::gagal(
                    'Data berkas berubah. Unggah kembali berkas tersebut.'
                );
            }

            if ($baris === null) {
                $baris = new PengumpulanBerkas();
                $baris->pengumpulan_id =
                    $pengumpulan->getKey();
                $baris->berkas_id = $id;

                foreach (
                    PengumpulanBerkas::SNAPSHOT
                    as $kolom
                ) {
                    $baris->{$kolom} = $file->{$kolom};
                }

                $baris->aktif = true;
                $baris->dilepas_at = null;
                $baris->save();

                continue;
            }

            if (! $baris->aktif) {
                $baris->aktif = true;
                $baris->dilepas_at = null;
                $baris->save();
            }
        }

        foreach ($lama as $baris) {
            if (
                $baris->aktif
                && ! in_array(
                    (int) $baris->berkas_id,
                    $ids,
                    true
                )
            ) {
                $baris->aktif = false;
                $baris->dilepas_at = now('UTC');
                $baris->save();
            }
        }
    }

    private function buatHash(
        ?string $teks,
        array $ids
    ): string {
        sort($ids, SORT_NUMERIC);

        $json = json_encode(
            [
                'jawaban_teks' => $teks,
                'berkas_ids' => $ids,
            ],
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
        );

        return hash('sha256', $json);
    }

    private function audit(
        Pengumpulan $pengumpulan,
        int $userId,
        string $aksi,
        ?array $sebelum
    ): void {
        $audit = new AuditLog();
        $audit->pelaku_id = $userId;
        $audit->entitas = 'pengumpulan';
        $audit->entitas_id = $pengumpulan->getKey();
        $audit->versi_entitas = $pengumpulan->revisi;
        $audit->aksi = $aksi;
        $audit->sebelum = $sebelum;
        $audit->sesudah =
            $pengumpulan->ringkasanAudit();
        $audit->alasan = null;
        $audit->waktu = now('UTC');
        $audit->save();
    }
}