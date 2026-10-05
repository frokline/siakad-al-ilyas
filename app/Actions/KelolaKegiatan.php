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
    public function buat(
        int $userId,
        array $data
    ): Kegiatan {
        return DB::transaction(
            function () use ($userId, $data): Kegiatan {
                [$user, $kelas] = $this->kunciKonteks(
                    $userId,
                    (int) $data['kelas_kuliah_id']
                );

                Gate::forUser($user)->authorize(
                    'create',
                    [Kegiatan::class, $kelas]
                );

                $isi = $this->dataIsi($data);
                $ids = $this->ids($data);

                $hash = hash(
                    'sha256',
                    json_encode(
                        [
                            'kelas' => $kelas->id,
                            'isi' => $isi,
                            'berkas' => $ids,
                        ],
                        JSON_THROW_ON_ERROR
                    )
                );

                $ada = Kegiatan::query()
                    ->where('pembuat_id', $userId)
                    ->where(
                        'form_token',
                        $data['form_token']
                    )
                    ->lockForUpdate()
                    ->first();

                if ($ada) {
                    if (
                        ! hash_equals(
                            $ada->hash_permohonan,
                            $hash
                        )
                    ) {
                        $this->gagal(
                            'Formulir sudah digunakan untuk data berbeda. Buka formulir baru.'
                        );
                    }

                    return $ada;
                }

                $this->pertemuan(
                    $kelas->id,
                    $isi['pertemuan_id']
                );

                $waktu = now('UTC')->startOfSecond();

                $kegiatan = new Kegiatan();
                $kegiatan->kelas_kuliah_id = $kelas->id;
                $kegiatan->pembuat_id = $userId;
                $kegiatan->form_token =
                    $data['form_token'];
                $kegiatan->hash_permohonan = $hash;

                foreach ($isi as $field => $value) {
                    $kegiatan->{$field} = $value;
                }

                $kegiatan->status = Kegiatan::TERBIT;
                $kegiatan->terbit_at = $waktu;
                $kegiatan->ditutup_at = null;
                $kegiatan->diarsipkan_at = null;
                $kegiatan->revisi = 1;
                $kegiatan->save();

                $this->lampiran(
                    $kegiatan,
                    $ids,
                    $userId
                );

                $this->lampiranSiap($kegiatan);

                $this->audit(
                    $kegiatan,
                    $userId,
                    'buat_dan_terbitkan',
                    null,
                    null
                );

                return $kegiatan;
            },
            3
        );
    }

    public function ubah(
        int $userId,
        Kegiatan $bound,
        array $data
    ): Kegiatan {
        return DB::transaction(
            function () use (
                $userId,
                $bound,
                $data
            ): Kegiatan {
                [$user] = $this->kunciKonteks(
                    $userId,
                    $bound->kelas_kuliah_id
                );

                $kegiatan = Kegiatan::query()
                    ->whereKey($bound->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                Gate::forUser($user)->authorize(
                    'update',
                    $kegiatan
                );

                $this->versi($kegiatan, $data);

                $isi = $this->dataIsi($data);

                $this->pertemuan(
                    $kegiatan->kelas_kuliah_id,
                    $isi['pertemuan_id']
                );

                $sebelum =
                    $kegiatan->ringkasanAudit();

                $this->lampiran(
                    $kegiatan,
                    $this->ids($data),
                    $userId
                );

                foreach ($isi as $field => $value) {
                    $kegiatan->{$field} = $value;
                }

                $kegiatan->revisi++;
                $kegiatan->save();

                $this->lampiranSiap($kegiatan);

                $this->audit(
                    $kegiatan,
                    $userId,
                    'ubah',
                    $sebelum,
                    $data['alasan'] ?? null
                );

                return $kegiatan;
            },
            3
        );
    }

    public function status(
        string $aksi,
        int $userId,
        Kegiatan $bound,
        array $data
    ): Kegiatan {
        abort_unless(
            in_array(
                $aksi,
                [
                    'tutup',
                    'bukaKembali',
                    'perpanjang',
                    'arsipkan',
                    'pulihkan',
                ],
                true
            ),
            404
        );

        return DB::transaction(
            function () use (
                $aksi,
                $userId,
                $bound,
                $data
            ): Kegiatan {
                [$user] = $this->kunciKonteks(
                    $userId,
                    $bound->kelas_kuliah_id
                );

                $kegiatan = Kegiatan::query()
                    ->whereKey($bound->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                Gate::forUser($user)->authorize(
                    $aksi,
                    $kegiatan
                );

                $this->versi($kegiatan, $data);

                $sebelum =
                    $kegiatan->ringkasanAudit();

                $waktu = now('UTC')->startOfSecond();

                if ($aksi === 'tutup') {
                    $kegiatan->status =
                        Kegiatan::DITUTUP;

                    $kegiatan->ditutup_at = $waktu;
                    $kegiatan->diarsipkan_at = null;
                } elseif ($aksi === 'bukaKembali') {
                    if (
                        $kegiatan->memerlukanPengumpulan()
                        && (
                            $kegiatan->tenggat_at === null
                            || ! $kegiatan->tenggat_at
                                ->gt($waktu)
                        )
                    ) {
                        $this->gagal(
                            'Tenggat sudah lewat. Perpanjang tenggat sebelum membuka kembali.'
                        );
                    }

                    $kegiatan->status =
                        Kegiatan::TERBIT;

                    $kegiatan->ditutup_at = null;
                    $kegiatan->diarsipkan_at = null;
                } elseif ($aksi === 'perpanjang') {
                    if (
                        ! $kegiatan
                            ->memerlukanPengumpulan()
                    ) {
                        $this->gagal(
                            'Materi tidak memiliki tenggat pengumpulan.'
                        );
                    }

                    $baru = WaktuKegiatan::dariForm(
                        $data['tenggat_baru'] ?? null,
                        'tenggat_baru'
                    );

                    if (
                        $kegiatan->tenggat_at === null
                        || ! $baru->gt(
                            $kegiatan->tenggat_at
                        )
                        || ! $baru->gt($waktu)
                    ) {
                        $this->gagal(
                            'Tenggat baru harus lebih akhir dari tenggat lama dan waktu sekarang.'
                        );
                    }

                    $kegiatan->tenggat_at = $baru;
                } elseif ($aksi === 'arsipkan') {
                    $kegiatan->status =
                        Kegiatan::ARSIP;

                    $kegiatan->diarsipkan_at =
                        $waktu;
                } else {
                    $kegiatan->diarsipkan_at = null;

                    if (
                        $kegiatan
                            ->memerlukanPengumpulan()
                        && (
                            $kegiatan->tenggat_at === null
                            || ! $kegiatan->tenggat_at
                                ->gt($waktu)
                        )
                    ) {
                        $kegiatan->status =
                            Kegiatan::DITUTUP;

                        $kegiatan->ditutup_at =
                            $waktu;
                    } else {
                        $kegiatan->status =
                            Kegiatan::TERBIT;

                        $kegiatan->ditutup_at = null;
                    }
                }

                $kegiatan->revisi++;
                $kegiatan->save();

                $this->audit(
                    $kegiatan,
                    $userId,
                    $aksi,
                    $sebelum,
                    $data['alasan'] ?? null
                );

                return $kegiatan;
            },
            3
        );
    }

    private function dataIsi(array $data): array
    {
        $jenis = (string) $data['jenis'];

        $instruksi = trim(
            (string) ($data['instruksi'] ?? '')
        );

        $umum = [
            'pertemuan_id' =>
                ! empty($data['pertemuan_id'])
                    ? (int) $data['pertemuan_id']
                    : null,

            'jenis' => $jenis,

            'judul' => trim(
                (string) $data['judul']
            ),

            'instruksi' =>
                $instruksi === ''
                    ? null
                    : $instruksi,

            'tautan_eksternal' =>
                filled($data['tautan_eksternal'] ?? null)
                    ? trim(
                        (string)
                        $data['tautan_eksternal']
                    )
                    : null,
        ];

        if ($jenis === Kegiatan::MATERI) {
            return $umum + [
                'metode' => 'informasi',
                'buka_at' => now('UTC')
                    ->startOfSecond()
                    ->format('Y-m-d H:i:s'),
                'tenggat_at' => null,
                'maks_ukuran_byte' => null,
                'maks_berkas' => null,
                'ekstensi_diizinkan' => null,
            ];
        }

        $buka = WaktuKegiatan::dariForm(
            $data['buka_lokal'] ?? null,
            'buka_lokal'
        );

        $tenggat = WaktuKegiatan::dariForm(
            $data['tenggat_lokal'] ?? null,
            'tenggat_lokal'
        );

        if (! $buka->lt($tenggat)) {
            $this->gagal(
                'Tenggat harus lebih akhir daripada waktu mulai.'
            );
        }

        $ekstensi = array_values(
            array_unique(
                array_map(
                    'strtolower',
                    (array)
                    ($data['ekstensi_jawaban'] ?? $data['ekstensi_diizinkan'] ?? [])
                )
            )
        );

        sort($ekstensi, SORT_STRING);

        return $umum + [
            'metode' => 'pengumpulan_berkas',
            'buka_at' =>
                $buka->format('Y-m-d H:i:s'),
            'tenggat_at' =>
                $tenggat->format('Y-m-d H:i:s'),
            'maks_ukuran_byte' =>
                (int) ($data['maksimal_mb_per_berkas'] ?? $data['maks_mb'] ?? 20) * 1048576,
            'maks_berkas' =>
                (int) ($data['maksimal_berkas'] ?? $data['maks_berkas'] ?? 5),
            'ekstensi_diizinkan' => $ekstensi,
        ];
    }

    private function kunciKonteks(
        int $userId,
        int $kelasId
    ): array {
        $user = User::query()
            ->whereKey($userId)
            ->lockForUpdate()
            ->firstOrFail();

        abort_unless(
            $user->status === 'aktif',
            403
        );

        $roles = $user->roles()
            ->orderBy('roles.id')
            ->lockForUpdate()
            ->get([
                'roles.id',
                'roles.kode',
            ]);

        $admin = $roles->contains(
            'kode',
            'admin_akademik'
        );

        abort_unless(
            $admin
            || $roles->contains('kode', 'dosen'),
            403
        );

        $petunjuk = KelasKuliah::query()
            ->with('rombel')
            ->findOrFail($kelasId);

        PeriodeAkademik::query()
            ->whereKey(
                $petunjuk->rombel
                    ->periode_akademik_id
            )
            ->lockForUpdate()
            ->firstOrFail();

        Rombel::query()
            ->whereKey($petunjuk->rombel_id)
            ->lockForUpdate()
            ->firstOrFail();

        $kelas = KelasKuliah::query()
            ->whereKey($kelasId)
            ->lockForUpdate()
            ->firstOrFail();

        $tim = PengajarKelas::query()
            ->where('kelas_kuliah_id', $kelasId)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if (! $admin) {
            $dosen = Dosen::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            abort_unless(
                $dosen
                && $dosen->status === 'aktif'
                && $tim->contains(
                    fn (PengajarKelas $penugasan):
                        bool =>
                        $penugasan->aktif
                        && $penugasan->dosen_id
                            === $dosen->id
                ),
                403
            );
        }

        return [$user, $kelas];
    }

    private function pertemuan(
        int $kelasId,
        mixed $pertemuanId
    ): void {
        if (
            $pertemuanId === null
            || $pertemuanId === ''
        ) {
            return;
        }

        $pertemuan = Pertemuan::query()
            ->whereKey((int) $pertemuanId)
            ->where(
                'kelas_kuliah_id',
                $kelasId
            )
            ->lockForUpdate()
            ->first();

        if (
            ! $pertemuan
            || $pertemuan->status === 'batal'
        ) {
            $this->gagal(
                'Pertemuan harus berada di kelas ini dan tidak dibatalkan.'
            );
        }
    }

    private function versi(
        Kegiatan $kegiatan,
        array $data
    ): void {
        if (
            ! is_string($data['versi'] ?? null)
            || ! hash_equals(
                $kegiatan->versiForm(),
                $data['versi']
            )
        ) {
            $this->gagal(
                'Data sudah berubah. Muat ulang halaman sebelum menyimpan.'
            );
        }

        if ($kegiatan->revisi >= 4294967295) {
            $this->gagal(
                'Batas revisi pembelajaran tercapai.'
            );
        }
    }

    private function ids(array $data): array
    {
        $ids = array_map(
            'intval',
            $data['berkas_ids'] ?? []
        );

        sort($ids, SORT_NUMERIC);

        if (
            count($ids) >
                (int) config(
                    'kegiatan.maks_lampiran_instruksi',
                    10
                )
            || count(array_unique($ids))
                !== count($ids)
            || (
                $ids !== []
                && min($ids) < 1
            )
        ) {
            $this->gagal(
                'Daftar lampiran pembelajaran tidak valid.'
            );
        }

        return $ids;
    }

    private function lampiran(
        Kegiatan $kegiatan,
        array $ids,
        int $userId
    ): void {
        $lama = $kegiatan
            ->semuaLampiran()
            ->orderBy('berkas_id')
            ->lockForUpdate()
            ->get()
            ->keyBy('berkas_id');

        $semua = array_values(
            array_unique([
                ...$ids,
                ...$lama->keys()
                    ->map(
                        fn ($id): int => (int) $id
                    )
                    ->all(),
            ])
        );

        sort($semua, SORT_NUMERIC);

        $berkas = Berkas::query()
            ->whereIn('id', $semua)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($ids as $id) {
            $file = $berkas->get($id);
            $row = $lama->get($id);

            if (
                ! $file
                || $file->status !== Berkas::TERSEDIA
                || (
                    ! ($row && $row->aktif)
                    && $file->diunggah_oleh !== $userId
                )
            ) {
                $this->gagal(
                    'Lampiran tidak tersedia atau bukan milik akun Anda.'
                );
            }

            if (! $row) {
                $row = new KegiatanBerkas();
                $row->kegiatan_id = $kegiatan->id;
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
            if (
                $row->aktif
                && ! in_array(
                    $row->berkas_id,
                    $ids,
                    true
                )
            ) {
                $row->aktif = false;
                $row->dilepas_at =
                    now('UTC')->startOfSecond();
                $row->save();
            }
        }
    }

    private function lampiranSiap(
        Kegiatan $kegiatan
    ): void {
        $ids = $kegiatan->lampiran()
            ->orderBy('berkas_id')
            ->pluck('berkas_id')
            ->all();

        $berkas = Berkas::query()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if (
            $berkas->count() !== count($ids)
            || $berkas->contains(
                fn (Berkas $file): bool =>
                    $file->status
                    !== Berkas::TERSEDIA
            )
        ) {
            $this->gagal(
                'Salah satu lampiran belum tersedia.'
            );
        }

        if (
            ! app()->environment([
                'local',
                'testing',
            ])
            && $berkas->contains(
                fn (Berkas $file): bool =>
                    $file->pemeriksaan !== 'clamav'
            )
        ) {
            $this->gagal(
                'Lampiran produksi harus dipindai antivirus.'
            );
        }
    }

    private function audit(
        Kegiatan $kegiatan,
        int $userId,
        string $aksi,
        ?array $sebelum,
        ?string $alasan
    ): void {
        $audit = new AuditLog();
        $audit->pelaku_id = $userId;
        $audit->entitas = 'kegiatan';
        $audit->entitas_id = $kegiatan->id;
        $audit->versi_entitas =
            $kegiatan->revisi;
        $audit->aksi = $aksi;
        $audit->sebelum = $sebelum;
        $audit->sesudah =
            $kegiatan->ringkasanAudit();
        $audit->alasan = $alasan;
        $audit->waktu =
            now('UTC')->startOfSecond();
        $audit->save();
    }

    private function gagal(
        string $pesan
    ): never {
        throw ValidationException::withMessages([
            'kegiatan' => $pesan,
        ]);
    }
}