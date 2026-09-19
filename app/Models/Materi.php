<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Materi extends Model
{
    public const DRAF = 'draf';
    public const TERBIT = 'terbit';
    public const ARSIP = 'arsip';
    public const STATUS = ['draf' => 'Draf', 'terbit' => 'Terbit', 'arsip' => 'Arsip'];
    protected $table = 'materi';
    protected $guarded = ['*'];
    protected $hidden = ['form_token', 'hash_permohonan'];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'kelas_kuliah_id' => 'integer',
            'pertemuan_id' => 'integer',
            'pembuat_id' => 'integer',
            'revisi' => 'integer',
            'terbit_at' => 'immutable_datetime',
            'diarsipkan_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime'
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            if (
                $m->getConnection()->transactionLevel() < 1 || blank($m->judul)
                || mb_strlen($m->judul) > 200 || ! isset(self::STATUS[$m->status])
                || $m->revisi < 1 || $m->revisi > 4294967295
                || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $m->hash_permohonan)
            ) {
                throw new LogicException('Materi harus valid dan disimpan melalui KelolaMateri.');
            }
            if (($m->status === self::DRAF && $m->terbit_at !== null)
                || ($m->status === self::TERBIT && $m->terbit_at === null)
                || (($m->status === self::ARSIP) !== ($m->diarsipkan_at !== null))
            ) {
                throw new LogicException('Status dan waktu materi tidak sesuai.');
            }
            if (! $m->exists) {
                if ($m->status !== self::DRAF || $m->revisi !== 1) {
                    throw new LogicException('Materi baru harus draf, revisi pertama.');
                }
                return;
            }
            $asal = $m->getRawOriginal('status');
            $transisi = [
                self::DRAF => [self::DRAF, self::TERBIT, self::ARSIP],
                self::TERBIT => [self::DRAF, self::ARSIP],
                self::ARSIP => [self::DRAF]
            ];
            if (
                $m->isDirty(['kelas_kuliah_id', 'pembuat_id', 'form_token', 'hash_permohonan'])
                || $m->revisi !== (int) $m->getRawOriginal('revisi') + 1
                || ! in_array($m->status, $transisi[$asal] ?? [], true)
            ) {
                throw new LogicException('Identitas materi tetap; transisi dan revisi harus sah.');
            }
            if (
                ! ($asal === self::DRAF && $m->status === self::DRAF)
                && $m->isDirty(['judul', 'isi', 'tautan_eksternal', 'pertemuan_id'])
            ) {
                throw new LogicException('Tarik materi menjadi draf sebelum mengubah isi.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Gunakan arsip; materi tidak dihapus.');
        });
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }
    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id');
    }
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }
    public function lampiran(): HasMany
    {
        return $this->hasMany(MateriBerkas::class, 'materi_id')->where('aktif', true);
    }
    public function semuaLampiran(): HasMany
    {
        return $this->hasMany(MateriBerkas::class, 'materi_id');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'materi');
    }
    public function versiForm(): string
    {
        return hash_hmac('sha256', 'materi:' . $this->id . ':' . $this->revisi, (string) config('app.key'));
    }
    public function sudahTerbit(): bool
    {
        return $this->status === self::TERBIT && $this->terbit_at !== null && $this->terbit_at->lte(now('UTC'));
    }
    public function ringkasanAudit(): array
    {
        return [
            ...$this->only([
                'kelas_kuliah_id',
                'pertemuan_id',
                'pembuat_id',
                'judul',
                'isi',
                'tautan_eksternal',
                'status',
                'terbit_at',
                'diarsipkan_at',
                'revisi'
            ]),
            'berkas_ids' => $this->lampiran()->orderBy('berkas_id')->pluck('berkas_id')->all()
        ];
    }
}
