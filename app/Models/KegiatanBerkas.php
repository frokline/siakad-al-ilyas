<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class KegiatanBerkas extends Model
{
    protected $table = 'kegiatan_berkas';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'kegiatan_id' => 'integer',
            'berkas_id' => 'integer',
            'aktif' => 'boolean',
            'dilepas_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $lampiran): void {
            if (
                $lampiran->getConnection()->transactionLevel() < 1
                || $lampiran->kegiatan_id < 1
                || $lampiran->berkas_id < 1
                || $lampiran->aktif !==
                    ($lampiran->dilepas_at === null)
            ) {
                throw new LogicException(
                    'Lampiran pembelajaran harus disimpan melalui transaksi.'
                );
            }

            if (
                $lampiran->exists
                && $lampiran->isDirty([
                    'kegiatan_id',
                    'berkas_id',
                ])
            ) {
                throw new LogicException(
                    'Identitas lampiran pembelajaran tidak boleh diubah.'
                );
            }

            $kegiatan = Kegiatan::query()
                ->whereKey($lampiran->kegiatan_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($kegiatan->status === Kegiatan::ARSIP) {
                throw new LogicException(
                    'Lampiran pembelajaran yang sudah diarsipkan tidak dapat diubah.'
                );
            }

            $berkasTersedia = Berkas::query()
                ->whereKey($lampiran->berkas_id)
                ->where('status', Berkas::TERSEDIA)
                ->lockForUpdate()
                ->exists();

            if (! $berkasTersedia) {
                throw new LogicException(
                    'Berkas lampiran tidak tersedia.'
                );
            }
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Lampiran dilepas secara logis dan tidak dihapus permanen.'
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

    public function berkas(): BelongsTo
    {
        return $this->belongsTo(
            Berkas::class,
            'berkas_id'
        );
    }
}