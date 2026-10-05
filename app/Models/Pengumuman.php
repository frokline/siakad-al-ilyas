<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Pengumuman extends Model
{
    public const STATUS = ['draf' => 'Draf', 'terbit' => 'Terbit', 'arsip' => 'Arsip'];
    public const ZONA = 'Asia/Makassar';
    protected $table = 'pengumuman';
    protected $guarded = ['*'];
    protected $hidden = ['form_token', 'hash_permohonan'];
    protected function casts(): array
    {
        return [
            'pembuat_id' => 'integer',
            'revisi' => 'integer',
            'terbit_at' => 'immutable_datetime',
            'berakhir_at' => 'immutable_datetime',
            'diarsipkan_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $p): void {
            if (
                $p->getConnection()->transactionLevel() < 1 || ! isset(self::STATUS[$p->status])
                || $p->revisi < 1 || $p->revisi > 4294967295 || mb_strlen(trim((string) $p->judul)) < 3
                || mb_strlen($p->judul) > 200 || mb_strlen(trim((string) $p->isi)) < 3 || mb_strlen($p->isi) > 20000
                || ! \Illuminate\Support\Str::isUuid((string) $p->form_token)
                || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $p->hash_permohonan)
                || ($p->status === 'draf' && $p->terbit_at !== null)
                || ($p->status === 'terbit' && $p->terbit_at === null)
                || (($p->status === 'arsip') !== ($p->diarsipkan_at !== null))
            ) {
                throw new LogicException('Pengumuman tidak valid atau bukan dalam transaksi.');
            }
            if (! $p->exists) {
                if ($p->status !== 'draf' || $p->revisi !== 1) {
                    throw new LogicException('Pengumuman baru harus draf.');
                }
                return;
            }
            $asal = $p->getRawOriginal('status');
            if (
                $p->isDirty(['pembuat_id', 'form_token', 'hash_permohonan'])
                || $p->revisi !== (int) $p->getRawOriginal('revisi') + 1
                || ! in_array($p->status, ['draf' => ['draf', 'terbit', 'arsip'], 'terbit' => ['arsip'], 'arsip' => []][$asal] ?? [], true)
                || ($asal !== 'draf' && $p->isDirty(['judul', 'isi', 'berakhir_at', 'terbit_at']))
                || ($p->isDirty('status') && $p->isDirty(['judul', 'isi', 'berakhir_at']))
            ) {
                throw new LogicException('Perubahan pengumuman tidak sah.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Gunakan arsip, bukan hapus.');
        });
    }
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }
    public function sasaran(): HasMany
    {
        return $this->hasMany(SasaranPengumuman::class, 'pengumuman_id');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'pengumuman');
    }
    public function versiForm(): string
    {
        return hash_hmac('sha256', 'pengumuman:' . $this->id . ':' . $this->revisi, (string) config('app.key'));
    }
    public function kedaluwarsa(): bool
    {
        return $this->berakhir_at !== null && $this->berakhir_at->lteTo(now('UTC'));
    }
    public function ringkasanAudit(): array
    {
        return $this->only(['pembuat_id', 'judul', 'isi', 'status', 'terbit_at', 'berakhir_at', 'diarsipkan_at', 'revisi'])
            + ['sasaran' => $this->sasaran()->orderBy('kunci_sasaran')->get()->map(fn($s) => $s->only(['lingkup', 'program_studi_id', 'kelas_kuliah_id', 'role_id']))->all()];
    }
}
