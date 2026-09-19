<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

class KalenderAkademik extends Model
{
    public const ZONA = 'Asia/Makassar';
    public const JENIS = ['krs' => 'KRS', 'kuliah' => 'Perkuliahan', 'ujian' => 'Ujian', 'libur' => 'Libur', 'lainnya' => 'Lainnya'];
    public const STATUS = ['draf' => 'Draf', 'terbit' => 'Terbit', 'batal' => 'Dibatalkan'];
    protected $table = 'kalender_akademik';
    protected $guarded = ['*'];
    protected $hidden = ['form_token', 'hash_permohonan'];
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'periode_akademik_id' => 'integer',
            'program_studi_id' => 'integer',
            'pembuat_id' => 'integer',
            'revisi' => 'integer',
            'mulai_at' => 'immutable_datetime',
            'selesai_at' => 'immutable_datetime',
            'diterbitkan_at' => 'immutable_datetime',
            'dibatalkan_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $k): void {
            if (
                $k->getConnection()->transactionLevel() < 1 || ! isset(self::STATUS[$k->status], self::JENIS[$k->jenis])
                || ! $k->mulai_at || ! $k->selesai_at || $k->selesai_at->lessThanOrEqualTo($k->mulai_at)
                || mb_strlen(trim((string) $k->judul)) < 3 || mb_strlen((string) $k->judul) > 200
                || mb_strlen((string) $k->keterangan) > 5000 || $k->revisi < 1 || $k->revisi > 4294967295
                || ! Str::isUuid((string) $k->form_token) || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $k->hash_permohonan)
                || ($k->status === 'batal') !== ($k->dibatalkan_at !== null)
                || ($k->status === 'terbit' && $k->diterbitkan_at === null)
                || ($k->status === 'draf' && $k->diterbitkan_at !== null)
            ) {
                throw new LogicException('Agenda harus valid dan ditulis melalui KelolaKalenderAkademik.');
            }
            if (! $k->exists) {
                if ($k->status !== 'draf' || $k->revisi !== 1) {
                    throw new LogicException('Agenda baru harus draf revisi satu.');
                }
                return;
            }
            $asal = $k->getRawOriginal('status');
            if (
                $k->isDirty(['pembuat_id', 'form_token', 'hash_permohonan']) || $k->revisi !== (int) $k->getRawOriginal('revisi') + 1
                || ! in_array($k->status, ['draf' => ['draf', 'terbit', 'batal'], 'terbit' => ['terbit', 'batal'], 'batal' => []][$asal] ?? [], true)
            ) {
                throw new LogicException('Identitas, revisi, atau transisi agenda tidak sah.');
            }
            if ($k->getRawOriginal('diterbitkan_at') !== null && $k->isDirty(['periode_akademik_id', 'program_studi_id', 'diterbitkan_at'])) {
                throw new LogicException('Periode, sasaran, dan waktu terbit pertama tetap setelah publikasi.');
            }
            if ($k->isDirty('status') && $k->isDirty(['judul', 'keterangan', 'jenis', 'mulai_at', 'selesai_at', 'periode_akademik_id', 'program_studi_id'])) {
                throw new LogicException('Perubahan isi dan status dilakukan melalui tindakan terpisah.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Agenda tidak dihapus. Gunakan Batalkan.');
        });
    }
    public function periodeAkademik(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_akademik_id');
    }
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'kalender_akademik');
    }
    public function versiForm(): string
    {
        return hash_hmac('sha256', 'kalender_akademik:' . $this->id . ':' . $this->revisi, (string) config('app.key'));
    }
    public function ringkasanAudit(): array
    {
        return $this->only([
            'periode_akademik_id',
            'program_studi_id',
            'pembuat_id',
            'judul',
            'keterangan',
            'jenis',
            'mulai_at',
            'selesai_at',
            'status',
            'diterbitkan_at',
            'dibatalkan_at',
            'catatan_perubahan',
            'revisi'
        ]);
    }
}
