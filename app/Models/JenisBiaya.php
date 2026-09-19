<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

class JenisBiaya extends Model
{
    public const SPP = 'SPP';
    protected $table = 'jenis_biaya';
    protected $guarded = ['*'];
    protected $hidden = ['form_token', 'hash_permohonan'];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'pembuat_id' => 'integer',
            'revisi' => 'integer',
            'aktif' => 'boolean',
            'dinonaktifkan_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $j): void {
            if (
                $j->getConnection()->transactionLevel() < 1 || $j->pembuat_id < 1
                || ! preg_match('/\A[A-Z][A-Z0-9_-]{1,29}\z/', (string) $j->kode)
                || ! is_string($j->nama) || trim($j->nama) !== $j->nama || mb_strlen($j->nama) < 3 || mb_strlen($j->nama) > 100
                || preg_match('/[\x00-\x1F\x7F]/', $j->nama) || mb_strlen((string) $j->keterangan) > 1000
                || ! Str::isUuid((string) $j->form_token) || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $j->hash_permohonan)
                || $j->revisi < 1 || $j->revisi > 4294967295 || ! is_bool($j->aktif)
                || $j->aktif !== ($j->dinonaktifkan_at === null)
            ) {
                throw new LogicException('Jenis biaya harus valid dan ditulis melalui KelolaJenisBiaya.');
            }
            if (! $j->exists) {
                if (! $j->aktif || $j->revisi !== 1) {
                    throw new LogicException('Jenis biaya baru harus aktif, revisi satu.');
                }
                return;
            }
            if (
                $j->isDirty(['kode', 'pembuat_id', 'form_token', 'hash_permohonan'])
                || $j->revisi !== (int) $j->getRawOriginal('revisi') + 1
                || ($j->isDirty('aktif') && $j->isDirty(['nama', 'keterangan']))
                || (! $j->isDirty('aktif') && $j->isDirty('dinonaktifkan_at'))
            ) {
                throw new LogicException('Kode/identitas tetap. Revisi dan transisi status harus sesuai.');
            }
            if (! (bool) $j->getRawOriginal('aktif') && $j->isDirty(['nama', 'keterangan'])) {
                throw new LogicException('Aktifkan kembali jenis biaya sebelum mengubah keterangannya.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Jenis biaya tidak dihapus. Gunakan Nonaktifkan.');
        });
    }
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'jenis_biaya');
    }
    // Dipakai setelah modul Tagihan dipasang; halaman Jenis Biaya belum memanggil relasi ini.
    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'jenis_biaya_id');
    }
    public function versiForm(): string
    {
        return hash_hmac('sha256', 'jenis_biaya:' . $this->id . ':' . $this->revisi, (string) config('app.key'));
    }
    public function labelStatus(): string
    {
        return $this->aktif ? 'Aktif' : 'Nonaktif';
    }
    public function ringkasanAudit(): array
    {
        return $this->only(['kode', 'nama', 'keterangan', 'aktif', 'dinonaktifkan_at', 'pembuat_id', 'revisi']);
    }
}
