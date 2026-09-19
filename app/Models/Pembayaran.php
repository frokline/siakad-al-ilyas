<?php

namespace App\Models;

use App\Services\UangTagihan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Pembayaran extends Model
{
    public const MENUNGGU = 'menunggu';
    public const DITERIMA = 'diterima';
    public const DITOLAK = 'ditolak';
    public const DIBATALKAN = 'dibatalkan';
    public const STATUS = ['menunggu' => 'Menunggu verifikasi', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'];
    public const IDENTITAS = [
        'tagihan_id',
        'pengunggah_id',
        'bukti_berkas_id',
        'nomor_pengajuan',
        'nominal_diajukan',
        'tanggal_transfer',
        'referensi_bank',
        'tujuan_transfer',
        'tagihan_snapshot',
        'bukti_snapshot',
        'bukti_sha256',
        'form_token',
        'hash_permohonan',
        'diajukan_at'
    ];
    protected $table = 'pembayaran';
    protected $guarded = ['*'];
    protected $hidden = ['form_token', 'hash_permohonan', 'bukti_aktif_sha256'];
    protected function casts(): array
    {
        return [
            'tagihan_id' => 'integer',
            'pengunggah_id' => 'integer',
            'bukti_berkas_id' => 'integer',
            'tagihan_aktif_id' => 'integer',
            'revisi' => 'integer',
            'nominal_diajukan' => 'decimal:2',
            'tanggal_transfer' => 'immutable_date',
            'diajukan_at' => 'immutable_datetime',
            'dibatalkan_at' => 'immutable_datetime',
            'tagihan_snapshot' => 'array',
            'bukti_snapshot' => 'array'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $p): void {
            $aktif = in_array($p->status, [self::MENUNGGU, self::DITERIMA], true);
            if (
                $p->getConnection()->transactionLevel() < 1 || ! isset(self::STATUS[$p->status]) || $p->revisi < 1 || $p->revisi > 4294967295
                || ! is_array($p->tagihan_snapshot) || ! is_array($p->bukti_snapshot) || ! $p->diajukan_at || ! $p->tanggal_transfer
                || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $p->bukti_sha256)
                || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $p->hash_permohonan)
                || ! \Illuminate\Support\Str::isUuid((string) $p->form_token)
                || $p->tagihan_aktif_id !== ($aktif ? $p->tagihan_id : null)
                || $p->bukti_aktif_sha256 !== ($aktif ? $p->bukti_sha256 : null)
                || ($p->status === self::DIBATALKAN) !== ($p->dibatalkan_at !== null)
                || ($p->status === self::DIBATALKAN && mb_strlen(trim((string) $p->alasan_batal)) < 10)
            ) {
                throw new LogicException('Pembayaran, slot unik, dan waktu harus konsisten. Gunakan KelolaPembayaran.');
            }
            UangTagihan::normal((string) $p->nominal_diajukan);
            if (! $p->exists) {
                if ($p->status !== self::MENUNGGU || $p->revisi !== 1) {
                    throw new LogicException('Pengajuan baru harus menunggu revisi satu.');
                }
            } elseif (
                $p->isDirty(self::IDENTITAS) || $p->revisi !== (int) $p->getRawOriginal('revisi') + 1
                || $p->getRawOriginal('status') !== self::MENUNGGU || $p->status !== self::DIBATALKAN
            ) {
                // Transisi diterima/ditolak baru ditambahkan bersama layanan verifikasi, bukan melalui endpoint umum.
                throw new LogicException('Bukti/isi pengajuan tetap. Tahap ini hanya mengizinkan pembatalan pengajuan menunggu.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Histori pembayaran tidak boleh dihapus.');
        });
    }
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }
    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengunggah_id');
    }
    public function bukti(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'bukti_berkas_id');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'pembayaran');
    }
    // Jangan eager-load sebelum modul verifikasi dibuat.
    public function verifikasi(): HasMany
    {
        return $this->hasMany(VerifikasiPembayaran::class, 'pembayaran_id');
    }
    public function versiForm(): string
    {
        return hash_hmac('sha256', 'pembayaran:' . $this->id . ':' . $this->revisi, (string) config('app.key'));
    }
    public function ringkasanAudit(): array
    {
        return $this->only([
            'tagihan_id',
            'pengunggah_id',
            'bukti_berkas_id',
            'nomor_pengajuan',
            'nominal_diajukan',
            'tanggal_transfer',
            'referensi_bank',
            'tujuan_transfer',
            'tagihan_snapshot',
            'bukti_snapshot',
            'status',
            'revisi',
            'diajukan_at',
            'dibatalkan_at',
            'alasan_batal'
        ]);
    }
}
