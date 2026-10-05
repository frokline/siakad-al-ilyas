<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

final class Pengumpulan extends Model
{
    public const TERKIRIM = 'terkirim';
    public const DIBATALKAN = 'dibatalkan';

    public const STATUS = [
        self::TERKIRIM => 'Sudah dikumpulkan',
        self::DIBATALKAN => 'Dihapus mahasiswa',
    ];

    protected $table = 'pengumpulan';

    protected $guarded = ['*'];

    protected $hidden = [
        'jawaban_teks',
        'hash_jawaban',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'kegiatan_id' => 'integer',
            'detail_krs_id' => 'integer',
            'pemilik_id' => 'integer',
            'revisi_kegiatan' => 'integer',
            'revisi' => 'integer',
            'dikirim_at' => 'immutable_datetime',
            'diubah_at' => 'immutable_datetime',
            'dibatalkan_at' => 'immutable_datetime',
            'tenggat_snapshot' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $pengumpulan): void {
            if (
                $pengumpulan
                    ->getConnection()
                    ->transactionLevel() < 1
            ) {
                throw new LogicException(
                    'Pengumpulan harus disimpan melalui transaksi.'
                );
            }

            if (
                ! isset(
                    self::STATUS[$pengumpulan->status]
                )
                || $pengumpulan->kegiatan_id < 1
                || $pengumpulan->detail_krs_id < 1
                || $pengumpulan->pemilik_id < 1
                || $pengumpulan->revisi < 1
                || $pengumpulan->revisi > 4294967295
                || $pengumpulan->revisi_kegiatan < 1
                || mb_strlen(
                    (string) $pengumpulan->jawaban_teks
                ) > 10000
                || ! preg_match(
                    '/\A[a-f0-9]{64}\z/',
                    (string) $pengumpulan->hash_jawaban
                )
                || $pengumpulan->dikirim_at === null
                || $pengumpulan->tenggat_snapshot === null
                || ! $pengumpulan->dikirim_at->lt(
                    $pengumpulan->tenggat_snapshot
                )
            ) {
                throw new LogicException(
                    'Data pengumpulan tidak valid.'
                );
            }

            if (
                $pengumpulan->status === self::TERKIRIM
                && $pengumpulan->dibatalkan_at !== null
            ) {
                throw new LogicException(
                    'Jawaban aktif tidak boleh mempunyai waktu pembatalan.'
                );
            }

            if (
                $pengumpulan->status === self::DIBATALKAN
                && $pengumpulan->dibatalkan_at === null
            ) {
                throw new LogicException(
                    'Jawaban yang dihapus wajib mempunyai waktu pembatalan.'
                );
            }

            if (! $pengumpulan->exists) {
                if (
                    $pengumpulan->status !== self::TERKIRIM
                    || $pengumpulan->revisi !== 1
                    || $pengumpulan->diubah_at !== null
                ) {
                    throw new LogicException(
                        'Jawaban baru harus langsung berstatus terkirim.'
                    );
                }

                return;
            }

            if (
                $pengumpulan->isDirty([
                    'kegiatan_id',
                    'detail_krs_id',
                    'pemilik_id',
                    'dikirim_at',
                ])
                || $pengumpulan->revisi
                    !== (int) $pengumpulan
                        ->getRawOriginal('revisi') + 1
            ) {
                throw new LogicException(
                    'Identitas pengumpulan tidak boleh diubah.'
                );
            }

            if (
                $pengumpulan->getRawOriginal('status')
                    === self::DIBATALKAN
            ) {
                throw new LogicException(
                    'Jawaban yang sudah dihapus tidak dapat diubah kembali.'
                );
            }
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Jawaban tidak dihapus secara fisik. Gunakan pembatalan.'
            );
        });
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(
            Kegiatan::class,
            'kegiatan_id'
        );
    }

    public function detailKrs(): BelongsTo
    {
        return $this->belongsTo(
            DetailKrs::class,
            'detail_krs_id'
        );
    }

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'pemilik_id'
        );
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(
            PengumpulanBerkas::class,
            'pengumpulan_id'
        )->where('aktif', true);
    }

    public function semuaLampiran(): HasMany
    {
        return $this->hasMany(
            PengumpulanBerkas::class,
            'pengumpulan_id'
        );
    }

    public function audits(): HasMany
    {
        return $this->hasMany(
            AuditLog::class,
            'entitas_id'
        )->where(
            'entitas',
            'pengumpulan'
        );
    }

    public function scopeBerlaku(
        Builder $query
    ): Builder {
        return $query->where(
            'pengumpulan.status',
            self::TERKIRIM
        );
    }

    public function versiForm(): string
    {
        return hash_hmac(
            'sha256',
            'pengumpulan:'
                . $this->id
                . ':'
                . $this->revisi,
            (string) config('app.key')
        );
    }

    public function hitungHash(): string
    {
        $berkas = $this->lampiran()
            ->orderBy('berkas_id')
            ->get()
            ->map(
                static fn (
                    PengumpulanBerkas $lampiran
                ): array => $lampiran->only([
                    'berkas_id',
                    'nama_asli',
                    'mime_type',
                    'ekstensi',
                    'ukuran_byte',
                    'sha256',
                ])
            )
            ->all();

        return hash(
            'sha256',
            json_encode(
                [
                    'kegiatan' =>
                        $this->kegiatan_id,

                    'peserta' =>
                        $this->detail_krs_id,

                    'jawaban' =>
                        $this->jawaban_teks,

                    'berkas' =>
                        $berkas,
                ],
                JSON_THROW_ON_ERROR
                    | JSON_UNESCAPED_UNICODE
            )
        );
    }

    public function ringkasanAudit(): array
    {
        return [
            ...$this->only([
                'kegiatan_id',
                'detail_krs_id',
                'pemilik_id',
                'status',
                'revisi',
                'dikirim_at',
                'diubah_at',
                'dibatalkan_at',
                'tenggat_snapshot',
                'revisi_kegiatan',
                'hash_jawaban',
            ]),

            'panjang_teks' => mb_strlen(
                (string) $this->jawaban_teks
            ),

            'berkas_ids' => $this->lampiran()
                ->orderBy('berkas_id')
                ->pluck('berkas_id')
                ->all(),
        ];
    }
}