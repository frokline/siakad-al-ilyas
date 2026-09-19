<?php

namespace App\Models;

use \App\Models\Concerns\MenjagaBuktiPembayaranPrivat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PengumpulanBerkas extends Model
{
    public const SNAPSHOT = ['nama_asli', 'mime_type', 'ekstensi', 'ukuran_byte', 'sha256'];
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
            'updated_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $b): void {
            if ($b->getConnection()->transactionLevel() < 1) {
                throw new LogicException('Lampiran harus ditulis dalam transaksi.');
            }
            $p = Pengumpulan::query()->whereKey($b->pengumpulan_id)->lockForUpdate()->firstOrFail();
            if (
                $p->status !== Pengumpulan::DRAF || $b->aktif !== ($b->dilepas_at === null)
                || $b->ukuran_byte < 1 || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $b->sha256)
                || ! in_array($b->ekstensi, ['pdf', 'jpg', 'png'], true) || blank($b->nama_asli)
                || ($b->exists && $b->isDirty(['pengumpulan_id', 'berkas_id', ...self::SNAPSHOT]))
            ) {
                throw new LogicException('Snapshot lampiran harus valid dan kiriman final tidak boleh diubah.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Gunakan pelepasan logis pada draf; jangan hapus lampiran historis.');
        });
    }
    public function pengumpulan(): BelongsTo
    {
        return $this->belongsTo(Pengumpulan::class, 'pengumpulan_id');
    }
    public function berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'berkas_id');
    }
    public function cocok(Berkas $b): bool
    {
        return $this->berkas_id === (int) $b->id && $this->nama_asli === $b->nama_asli
            && $this->mime_type === $b->mime_type && $this->ekstensi === $b->ekstensi
            && $this->ukuran_byte === $b->ukuran_byte && hash_equals($this->sha256, (string) $b->sha256);
    }
    public function ukuranLabel(): string
    {
        return number_format($this->ukuran_byte / 1048576, 2, ',', '.') . ' MB';
    }
}
