<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Berkas;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AksesPembayaran;
use App\Services\AturanPembayaran;
use App\Services\BuktiPembayaran;
use App\Services\TujuanPembayaran;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class KelolaPembayaran
{
    public function ajukan(
        int $userId,
        Tagihan $bound,
        array $input
    ): Pembayaran {
        $data = AturanPembayaran::data($input);

        $hash = hash(
            'sha256',
            json_encode(
                [
                    'tagihan_id' => (int) $bound->id,
                    'isi' => $data,
                ],
                JSON_THROW_ON_ERROR
            )
        );

        $user = User::query()->findOrFail($userId);
        $tagihan = Tagihan::query()->findOrFail($bound->id);

        abort_unless(
            app(AksesPembayaran::class)->ajukan($user, $tagihan),
            403
        );

        $ulang = Pembayaran::query()
            ->where('pengunggah_id', $userId)
            ->where('form_token', $data['form_token'])
            ->first();

        if ($ulang) {
            return $this->ulang($ulang, $hash);
        }

        $snapshotBukti = app(BuktiPembayaran::class)->periksa(
            $data['bukti_berkas_id'],
            $userId
        );

        try {
            return DB::transaction(
                function () use (
                    $userId,
                    $bound,
                    $data,
                    $hash,
                    $snapshotBukti
                ): Pembayaran {
                    [
                        $user,
                        $petugas,
                        $mahasiswa,
                    ] = $this->pelaku($userId);

                    $tagihan = Tagihan::query()
                        ->whereKey($bound->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $this->izin(
                        $user,
                        $petugas,
                        $mahasiswa,
                        $tagihan
                    );

                    $ulang = Pembayaran::query()
                        ->where('pengunggah_id', $userId)
                        ->where('form_token', $data['form_token'])
                        ->lockForUpdate()
                        ->first();

                    if ($ulang) {
                        return $this->ulang($ulang, $hash);
                    }

                    if (
                        $tagihan->status !== Tagihan::TERBIT
                        || ! hash_equals(
                            $tagihan->versiForm(),
                            $data['versi_tagihan']
                        )
                    ) {
                        $this->gagal(
                            'versi_tagihan',
                            'Tagihan berubah atau tidak terbit. Buka ulang formulir.'
                        );
                    }

                    if (
                        $data['nominal_diajukan'] !== $tagihan->nominal
                    ) {
                        $this->gagal(
                            'nominal_diajukan',
                            'Pembayaran harus sama dengan nominal penuh satu tagihan.'
                        );
                    }

                    if (
                        ! hash_equals(
                            TujuanPembayaran::versi(),
                            $data['versi_tujuan']
                        )
                    ) {
                        $this->gagal(
                            'tujuan_transfer',
                            'Rekening tujuan berubah. Periksa kembali sebelum mengajukan.'
                        );
                    }

                    if (
                        Pembayaran::query()
                            ->where('tagihan_id', $tagihan->id)
                            ->whereIn('status', [
                                Pembayaran::MENUNGGU,
                                Pembayaran::DITERIMA,
                            ])
                            ->lockForUpdate()
                            ->first()
                    ) {
                        $this->gagal(
                            'tagihan',
                            'Tagihan sudah memiliki pengajuan menunggu atau pembayaran diterima.'
                        );
                    }

                    $berkas = Berkas::query()
                        ->whereKey($data['bukti_berkas_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        $berkas->diunggah_oleh !== $userId
                        || $berkas->status !== Berkas::TERSEDIA
                        || BuktiPembayaran::snapshot($berkas) !== $snapshotBukti
                    ) {
                        $this->gagal(
                            'bukti_berkas_id',
                            'Bukti berubah atau tidak lagi tersedia. Pilih ulang.'
                        );
                    }

                    \App\Services\PrivasiBuktiPembayaran::khususKeuangan(
                        $berkas
                    );

                    $riwayatBukti = Pembayaran::query()
                        ->where('bukti_sha256', $berkas->sha256)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get(['id', 'tagihan_id']);

                    if (
                        $riwayatBukti->contains(
                            fn (Pembayaran $pembayaran): bool =>
                                $pembayaran->tagihan_id
                                !== (int) $tagihan->id
                        )
                    ) {
                        $this->gagal(
                            'bukti_berkas_id',
                            'Bukti yang sama telah dikaitkan dengan tagihan lain.'
                        );
                    }

                    $pembayaran = new Pembayaran();
                    $pembayaran->tagihan_id = $tagihan->id;
                    $pembayaran->pengunggah_id = $userId;
                    $pembayaran->bukti_berkas_id = $berkas->id;
                    $pembayaran->nomor_pengajuan =
                        'BYR-' . strtoupper((string) Str::ulid());
                    $pembayaran->nominal_diajukan =
                        $data['nominal_diajukan'];
                    $pembayaran->tanggal_transfer =
                        $data['tanggal_transfer'];
                    $pembayaran->referensi_bank =
                        $data['referensi_bank'];
                    $pembayaran->tujuan_transfer =
                        TujuanPembayaran::teks();

                    $pembayaran->tagihan_snapshot = $tagihan->only([
                        'nomor',
                        'mahasiswa_id',
                        'registrasi_semester_id',
                        'jenis_biaya_id',
                        'tahun_tagihan',
                        'bulan_tagihan',
                        'nominal',
                        'jatuh_tempo',
                        'snapshot',
                        'revisi',
                    ]);

                    $pembayaran->bukti_snapshot = $snapshotBukti;
                    $pembayaran->bukti_sha256 = $berkas->sha256;
                    $pembayaran->status = Pembayaran::MENUNGGU;
                    $pembayaran->tagihan_aktif_id = $tagihan->id;
                    $pembayaran->bukti_aktif_sha256 = $berkas->sha256;
                    $pembayaran->form_token = $data['form_token'];
                    $pembayaran->hash_permohonan = $hash;
                    $pembayaran->revisi = 1;
                    $pembayaran->diajukan_at = now('UTC');
                    $pembayaran->save();

                    $this->audit(
                        $pembayaran,
                        $userId,
                        'ajukan',
                        null,
                        'Pengajuan bukti transfer.'
                    );

                    return $pembayaran;
                },
                3
            );
        } catch (UniqueConstraintViolationException $exception) {
            if (
                Pembayaran::query()
                    ->where('tagihan_aktif_id', $bound->id)
                    ->orWhere(
                        'bukti_aktif_sha256',
                        $snapshotBukti['sha256']
                    )
                    ->exists()
            ) {
                $this->gagal(
                    'pembayaran',
                    'Pengajuan bersamaan sudah tercatat. Muat ulang riwayat sebelum mencoba kembali.'
                );
            }

            throw $exception;
        }
    }

    public function batalkan(
        int $userId,
        Pembayaran $bound,
        array $input
    ): Pembayaran {
        $data = Validator::make($input, [
            'versi' => [
                'required',
                'string',
                'regex:/\A[a-f0-9]{64}\z/',
            ],
            'alasan' => [
                'required',
                'string',
                'min:10',
                'max:1000',
                'regex:/\S.{8,}\S/s',
            ],
            'konfirmasi' => [
                'required',
                'accepted',
            ],
        ])->validate();

        return DB::transaction(
            function () use ($userId, $bound, $data): Pembayaran {
                [
                    $user,
                    $petugas,
                    $mahasiswa,
                ] = $this->pelaku($userId);

                $tagihan = Tagihan::query()
                    ->whereKey($bound->tagihan_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->izin(
                    $user,
                    $petugas,
                    $mahasiswa,
                    $tagihan
                );

                $pembayaran = Pembayaran::query()
                    ->whereKey($bound->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $pembayaran->tagihan_id !== (int) $tagihan->id
                    || ! hash_equals(
                        $pembayaran->versiForm(),
                        $data['versi']
                    )
                    || $pembayaran->revisi >= 4294967295
                ) {
                    $this->gagal(
                        'versi',
                        'Pengajuan berubah. Muat ulang detail.'
                    );
                }

                if ($pembayaran->status !== Pembayaran::MENUNGGU) {
                    $this->gagal(
                        'status',
                        'Hanya pengajuan menunggu dapat dibatalkan.'
                    );
                }

                $sebelum = $pembayaran->ringkasanAudit();

                $pembayaran->status = Pembayaran::DIBATALKAN;
                $pembayaran->tagihan_aktif_id = null;
                $pembayaran->bukti_aktif_sha256 = null;
                $pembayaran->dibatalkan_at = now('UTC');
                $pembayaran->alasan_batal = trim($data['alasan']);
                $pembayaran->revisi++;
                $pembayaran->save();

                $this->audit(
                    $pembayaran,
                    $userId,
                    'batalkan',
                    $sebelum,
                    $data['alasan']
                );

                return $pembayaran;
            },
            3
        );
    }

    private function pelaku(int $userId): array
    {
        $user = User::query()
            ->whereKey($userId)
            ->lockForUpdate()
            ->firstOrFail();

        $roles = $user->roles()
            ->orderBy('roles.id')
            ->lockForUpdate()
            ->get(['roles.id', 'roles.kode']);

        abort_unless($user->status === User::STATUS_AKTIF, 403);

        return [
            $user,
            $roles->contains('kode', 'admin_keuangan'),
            $roles->contains('kode', 'mahasiswa'),
        ];
    }

    private function izin(
        User $user,
        bool $petugas,
        bool $mahasiswa,
        Tagihan $tagihan
    ): void {
        if ($petugas) {
            return;
        }

        $pemilik = DB::table('mahasiswa')
            ->where('id', $tagihan->mahasiswa_id)
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($mahasiswa && $pemilik, 403);
    }

    private function ulang(
        Pembayaran $pembayaran,
        string $hash
    ): Pembayaran {
        if (
            ! hash_equals(
                $pembayaran->hash_permohonan,
                $hash
            )
        ) {
            $this->gagal(
                'form_token',
                'Formulir sudah dipakai untuk isi berbeda. Buka formulir baru.'
            );
        }

        return $pembayaran;
    }

    private function audit(
        Pembayaran $pembayaran,
        int $userId,
        string $aksi,
        ?array $sebelum,
        string $alasan
    ): void {
        $audit = new AuditLog();
        $audit->pelaku_id = $userId;
        $audit->entitas = 'pembayaran';
        $audit->entitas_id = $pembayaran->id;
        $audit->versi_entitas = $pembayaran->revisi;
        $audit->aksi = $aksi;
        $audit->sebelum = $sebelum;
        $audit->sesudah = $pembayaran->ringkasanAudit();
        $audit->alasan = $alasan;
        $audit->waktu = now('UTC');
        $audit->save();
    }

    private function gagal(string $key, string $pesan): never
    {
        throw ValidationException::withMessages([
            $key => $pesan,
        ]);
    }
}