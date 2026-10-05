<?php

namespace App\Models;

use App\Services\UangTagihan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

final class Pembayaran extends Model
{
    public const MENUNGGU = 'menunggu';
    public const DITERIMA = 'diterima';
    public const DITOLAK = 'ditolak';
    public const DIBATALKAN = 'dibatalkan';

    public const STATUS = [
        self::MENUNGGU => 'Menunggu verifikasi',
        self::DITERIMA => 'Diterima',
        self::DITOLAK => 'Ditolak',
        self::DIBATALKAN => 'Dibatalkan',
    ];

    public const IDENTITAS = [
        'tagihan_id',
        'pengunggah_id',
        'bukti_berkas_id',
        'nomor_pengajuan',
        'nominal_diajukan',
        'tanggal_transfer',
        'referensi_bank',
        'tujuan_transfer',
        'tagihan_snapshot',
        'bukti_snapshot',
        'bukti_sha256',
        'form_token',
        'hash_permohonan',
        'diajukan_at',
    ];

    protected $table = 'pembayaran';

    protected $guarded = ['*'];

    protected $hidden = [
        'form_token',
        'hash_permohonan',
        'bukti_aktif_sha256',
    ];

    protected function casts(): array
    {
        return [
            'tagihan_id' => 'integer',
            'pengunggah_id' => 'integer',
            'bukti_berkas_id' => 'integer',
            'tagihan_aktif_id' => 'integer',
            'revisi' => 'integer',
            'nominal_diajukan' => 'decimal:2',
            'tanggal_transfer' => 'immutable_date',
            'diajukan_at' => 'immutable_datetime',
            'dibatalkan_at' => 'immutable_datetime',
            'tagihan_snapshot' => 'array',
            'bukti_snapshot' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $pembayaran): void {
            $mengisiSlot = in_array(
                $pembayaran->status,
                [
                    self::MENUNGGU,
                    self::DITERIMA,
                ],
                true
            );

            $statusDibatalkan =
                $pembayaran->status === self::DIBATALKAN;

            $alasanBatal = trim(
                (string) $pembayaran->alasan_batal
            );

            $dataDasarValid = (
                $pembayaran->getConnection()->transactionLevel() >= 1
                && array_key_exists(
                    (string) $pembayaran->status,
                    self::STATUS
                )
                && $pembayaran->revisi >= 1
                && $pembayaran->revisi <= 4294967295
                && is_array($pembayaran->tagihan_snapshot)
                && is_array($pembayaran->bukti_snapshot)
                && $pembayaran->diajukan_at !== null
                && $pembayaran->tanggal_transfer !== null
                && preg_match(
                    '/\A[a-f0-9]{64}\z/',
                    (string) $pembayaran->bukti_sha256
                )
                && preg_match(
                    '/\A[a-f0-9]{64}\z/',
                    (string) $pembayaran->hash_permohonan
                )
                && Str::isUuid(
                    (string) $pembayaran->form_token
                )
                && $pembayaran->tagihan_aktif_id === (
                    $mengisiSlot
                    ? $pembayaran->tagihan_id
                    : null
                )
                && $pembayaran->bukti_aktif_sha256 === (
                    $mengisiSlot
                    ? $pembayaran->bukti_sha256
                    : null
                )
                && $statusDibatalkan === (
                    $pembayaran->dibatalkan_at !== null
                )
                && (
                    $statusDibatalkan
                    ? mb_strlen($alasanBatal) >= 10
                    : $alasanBatal === ''
                )
            );

            if (! $dataDasarValid) {
                throw new LogicException(
                    'Pembayaran, slot unik, dan waktu tidak konsisten. '
                        . 'Gunakan layanan pengelolaan pembayaran.'
                );
            }

            UangTagihan::normal(
                (string) $pembayaran->nominal_diajukan
            );

            if (! $pembayaran->exists) {
                if (
                    $pembayaran->status !== self::MENUNGGU
                    || $pembayaran->revisi !== 1
                ) {
                    throw new LogicException(
                        'Pengajuan baru harus berstatus menunggu '
                            . 'dengan revisi satu.'
                    );
                }

                return;
            }

            if ($pembayaran->isDirty(self::IDENTITAS)) {
                throw new LogicException(
                    'Identitas dan bukti pembayaran tidak dapat diubah.'
                );
            }

            if (
                $pembayaran->revisi
                !== (int) $pembayaran->getRawOriginal('revisi') + 1
            ) {
                throw new LogicException(
                    'Nomor revisi pembayaran tidak valid.'
                );
            }

            $statusAwal = (string) $pembayaran
                ->getRawOriginal('status');

            $statusTujuan = (string) $pembayaran->status;

            $transisiDiizinkan = (
                $statusAwal === self::MENUNGGU
                && in_array(
                    $statusTujuan,
                    [
                        self::DITERIMA,
                        self::DITOLAK,
                        self::DIBATALKAN,
                    ],
                    true
                )
            );

            if (! $transisiDiizinkan) {
                throw new LogicException(
                    'Perubahan status pembayaran tidak diizinkan.'
                );
            }
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Histori pembayaran tidak boleh dihapus.'
            );
        });
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(
            Tagihan::class,
            'tagihan_id'
        );
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'pengunggah_id'
        );
    }

    public function bukti(): BelongsTo
    {
        return $this->belongsTo(
            Berkas::class,
            'bukti_berkas_id'
        );
    }

    public function audits(): HasMany
    {
        return $this->hasMany(
            AuditLog::class,
            'entitas_id'
        )->where('entitas', 'pembayaran');
    }

    public function verifikasi(): HasMany
    {
        return $this->hasMany(
            VerifikasiPembayaran::class,
            'pembayaran_id'
        );
    }

    public function versiForm(): string
    {
        return hash_hmac(
            'sha256',
            'pembayaran:' . $this->id . ':' . $this->revisi,
            (string) config('app.key')
        );
    }

    public function ringkasanAudit(): array
    {
        return $this->only([
            'tagihan_id',
            'pengunggah_id',
            'bukti_berkas_id',
            'nomor_pengajuan',
            'nominal_diajukan',
            'tanggal_transfer',
            'referensi_bank',
            'tujuan_transfer',
            'tagihan_snapshot',
            'bukti_snapshot',
            'status',
            'revisi',
            'diajukan_at',
            'dibatalkan_at',
            'alasan_batal',
        ]);
    }
}
