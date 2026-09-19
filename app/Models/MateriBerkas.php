<?php

namespace App\Models;

use \App\Models\Concerns\MenjagaBuktiPembayaranPrivat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class MateriBerkas extends Model
{
    protected $table = 'materi_berkas';
    protected $guarded = ['*'];
    protected function casts(): array
    {
        return [
            'materi_id' => 'integer',
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
                $p->getConnection()->transactionLevel() < 1 || $p->materi_id < 1 || $p->berkas_id < 1
                || $p->aktif !== ($p->dilepas_at === null)
                || ($p->exists && $p->isDirty(['materi_id', 'berkas_id']))
            ) {
                throw new LogicException('Lampiran harus disimpan melalui transaksi KelolaMateri.');
            }
            if (! Materi::query()->whereKey($p->materi_id)->where('status', Materi::DRAF)->exists()) {
                throw new LogicException('Lampiran hanya diubah ketika materi berupa draf.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Lampiran dilepas secara logis, bukan dihapus.');
        });
    }
    public function materi(): BelongsTo
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }
    public function berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'berkas_id');
    }
}
