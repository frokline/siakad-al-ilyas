<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class PengumpulanBerkas extends Model
{
    public const SNAPSHOT = [
        'nama_asli',
        'mime_type',
        'ekstensi',
        'ukuran_byte',
        'sha256',
    ];

    public const EKSTENSI_DIDUKUNG = [
        'pdf',
        'jpg',
        'png',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'zip',
    ];

    protected $table = 'pengumpulan_berkas';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'pengumpulan_id' => 'integer',
            'berkas_id' => 'integer',
            'ukuran_byte' => 'integer',
            'aktif' => 'boolean',
            'dilepas_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (
            self $lampiran
        ): void {
            if (
                $lampiran
                    ->getConnection()
                    ->transactionLevel() < 1
            ) {
                throw new LogicException(
                    'Lampiran harus ditulis dalam transaksi.'
                );
            }

            $pengumpulan = Pengumpulan::query()
                ->whereKey(
                    $lampiran->pengumpulan_id
                )
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $pengumpulan->status
                    !== Pengumpulan::TERKIRIM
                || $lampiran->aktif
                    !== ($lampiran->dilepas_at === null)
                || $lampiran->ukuran_byte < 1
                || ! preg_match(
                    '/\A[a-f0-9]{64}\z/',
                    (string) $lampiran->sha256
                )
                || ! in_array(
                    $lampiran->ekstensi,
                    self::EKSTENSI_DIDUKUNG,
                    true
                )
                || blank($lampiran->nama_asli)
            ) {
                throw new LogicException(
                    'Lampiran jawaban tidak valid.'
                );
            }

            if (
                $lampiran->exists
                && $lampiran->isDirty([
                    'pengumpulan_id',
                    'berkas_id',
                    ...self::SNAPSHOT,
                ])
            ) {
                throw new LogicException(
                    'Identitas lampiran tidak boleh diubah.'
                );
            }
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Lampiran tidak dihapus secara fisik.'
            );
        });
    }

    public function pengumpulan(): BelongsTo
    {
        return $this->belongsTo(
            Pengumpulan::class,
            'pengumpulan_id'
        );
    }

    public function berkas(): BelongsTo
    {
        return $this->belongsTo(
            Berkas::class,
            'berkas_id'
        );
    }

    public function cocok(Berkas $berkas): bool
    {
        return $this->berkas_id
                === (int) $berkas->id
            && $this->nama_asli
                === $berkas->nama_asli
            && $this->mime_type
                === $berkas->mime_type
            && $this->ekstensi
                === $berkas->ekstensi
            && $this->ukuran_byte
                === $berkas->ukuran_byte
            && hash_equals(
                $this->sha256,
                (string) $berkas->sha256
            );
    }

    public function ukuranLabel(): string
    {
        return number_format(
            $this->ukuran_byte / 1048576,
            2,
            ',',
            '.'
        ) . ' MB';
    }
}