<?php

namespace App\Models;

use \App\Models\Concerns\MenjagaBuktiPembayaranPrivat;
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
            'kegiatan_id' => 'integer',
            'berkas_id' => 'integer',
            'aktif' => 'boolean',
            'dilepas_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $p): void {
            if (
                $p->getConnection()->transactionLevel() < 1 || $p->kegiatan_id < 1 || $p->berkas_id < 1
                || $p->aktif !== ($p->dilepas_at === null)
                || ($p->exists && $p->isDirty(['kegiatan_id', 'berkas_id']))
            ) {
                throw new LogicException('Lampiran harus disimpan melalui transaksi KelolaKegiatan.');
            }
            if (! Kegiatan::query()->whereKey($p->kegiatan_id)->where('status', Kegiatan::DRAF)->whereNull('terbit_at')->exists()) {
                throw new LogicException('Lampiran hanya diubah ketika kegiatan berupa draf.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Lampiran dilepas secara logis, bukan dihapus.');
        });
    }
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }
    public function berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'berkas_id');
    }
}
