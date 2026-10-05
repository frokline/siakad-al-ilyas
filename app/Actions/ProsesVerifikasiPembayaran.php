<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Models\VerifikasiPembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class ProsesVerifikasiPembayaran
{
    public function terima(
        int $userId,
        Pembayaran $pembayaran,
        array $input
    ): Pembayaran {
        return $this->proses(
            $userId,
            $pembayaran,
            VerifikasiPembayaran::TERIMA,
            $input
        );
    }

    public function tolak(
        int $userId,
        Pembayaran $pembayaran,
        array $input
    ): Pembayaran {
        return $this->proses(
            $userId,
            $pembayaran,
            VerifikasiPembayaran::TOLAK,
            $input
        );
    }

    private function proses(
        int $userId,
        Pembayaran $bound,
        string $tindakan,
        array $input
    ): Pembayaran {
        $data = Validator::make($input, [
            'versi' => [
                'required',
                'string',
                'regex:/\A[a-f0-9]{64}\z/',
            ],
            'catatan' => [
                'required',
                'string',
                'min:10',
                'max:2000',
                'not_regex:/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',
            ],
            'konfirmasi' => [
                'required',
                'accepted',
            ],
        ])->validate();

        if (! in_array($tindakan, [
            VerifikasiPembayaran::TERIMA,
            VerifikasiPembayaran::TOLAK,
        ], true)) {
            throw ValidationException::withMessages([
                'tindakan' => 'Tindakan verifikasi tidak dikenali.',
            ]);
        }

        return DB::transaction(function () use (
            $userId,
            $bound,
            $tindakan,
            $data
        ): Pembayaran {
            $petugas = User::query()
                ->whereKey($userId)
                ->lockForUpdate()
                ->firstOrFail();

            $peranPetugas = $petugas->roles()
                ->orderBy('roles.id')
                ->lockForUpdate()
                ->get([
                    'roles.id',
                    'roles.kode',
                ]);

            abort_unless(
                $petugas->status === User::STATUS_AKTIF
                && $peranPetugas->contains(
                    'kode',
                    'admin_keuangan'
                ),
                403
            );

            // Urutan penguncian:
            // tagihan -> pembayaran.
            $tagihan = Tagihan::query()
                ->whereKey($bound->tagihan_id)
                ->lockForUpdate()
                ->firstOrFail();

            $pembayaran = Pembayaran::query()
                ->whereKey($bound->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $pembayaran->tagihan_id !== (int) $tagihan->id
                || ! hash_equals(
                    $pembayaran->versiForm(),
                    (string) $data['versi']
                )
            ) {
                $this->gagal(
                    'versi',
                    'Pengajuan pembayaran telah berubah. Muat ulang halaman.'
                );
            }

            if ($pembayaran->status !== Pembayaran::MENUNGGU) {
                $this->gagal(
                    'status',
                    'Hanya pembayaran berstatus menunggu yang dapat diverifikasi.'
                );
            }

            if ($pembayaran->revisi >= 4294967295) {
                $this->gagal(
                    'revisi',
                    'Batas revisi pembayaran telah tercapai.'
                );
            }

            if ($tagihan->status !== Tagihan::TERBIT) {
                $this->gagal(
                    'tagihan',
                    'Tagihan sudah tidak berstatus terbit.'
                );
            }

            $statusSebelum = $pembayaran->status;
            $sebelum = $pembayaran->ringkasanAudit();
            $catatan = trim((string) $data['catatan']);

            if ($tindakan === VerifikasiPembayaran::TERIMA) {
                $pembayaran->status = Pembayaran::DITERIMA;

                // Slot tetap dipertahankan agar tagihan yang sudah lunas
                // tidak dapat menerima pengajuan pembayaran baru.
                $pembayaran->tagihan_aktif_id = $pembayaran->tagihan_id;
                $pembayaran->bukti_aktif_sha256 = $pembayaran->bukti_sha256;
            } else {
                $pembayaran->status = Pembayaran::DITOLAK;

                // Penolakan melepaskan slot sehingga mahasiswa
                // dapat mengajukan bukti pembayaran baru.
                $pembayaran->tagihan_aktif_id = null;
                $pembayaran->bukti_aktif_sha256 = null;
            }

            $pembayaran->dibatalkan_at = null;
            $pembayaran->alasan_batal = null;
            $pembayaran->revisi++;
            $pembayaran->save();

            $waktu = now('UTC');

            $verifikasi = new VerifikasiPembayaran();
            $verifikasi->pembayaran_id = $pembayaran->id;
            $verifikasi->petugas_id = $petugas->id;
            $verifikasi->tindakan = $tindakan;
            $verifikasi->status_sebelum = $statusSebelum;
            $verifikasi->status_sesudah = $pembayaran->status;
            $verifikasi->catatan = $catatan;
            $verifikasi->revisi_pembayaran = $pembayaran->revisi;
            $verifikasi->waktu = $waktu;
            $verifikasi->save();

            $audit = new AuditLog();
            $audit->pelaku_id = $petugas->id;
            $audit->entitas = 'pembayaran';
            $audit->entitas_id = $pembayaran->id;
            $audit->versi_entitas = $pembayaran->revisi;
            $audit->aksi = $tindakan;
            $audit->sebelum = $sebelum;
            $audit->sesudah = $pembayaran->ringkasanAudit();
            $audit->alasan = $catatan;
            $audit->waktu = $waktu;
            $audit->save();

            return $pembayaran->fresh([
                'tagihan',
                'pengunggah',
                'verifikasi',
            ]);
        }, 3);
    }

    private function gagal(string $kolom, string $pesan): never
    {
        throw ValidationException::withMessages([
            $kolom => $pesan,
        ]);
    }
}