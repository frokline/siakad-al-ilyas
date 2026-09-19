<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use LogicException;

class Berkas extends Model
{
    public const MENUNGGU = 'menunggu';
    public const TERSEDIA = 'tersedia';
    public const DITOLAK = 'ditolak';
    public const DIHAPUS = 'dihapus';
    public const STATUS = [
        self::MENUNGGU => 'Sedang diproses',
        self::TERSEDIA => 'Tersedia',
        self::DITOLAK => 'Gagal diproses',
        self::DIHAPUS => 'Dinonaktifkan'
    ];
    public const IDENTITAS = [
        'diunggah_oleh',
        'upload_token',
        'storage_disk',
        'object_key',
        'nama_asli',
        'mime_type',
        'ekstensi',
        'ukuran_byte',
        'sha256',
        'pemeriksaan',
        'diperiksa_at'
    ];
    protected $table = 'berkas';
    protected $guarded = ['*'];
    protected $hidden = ['storage_disk', 'object_key', 'upload_token'];

    protected function casts(): array
    {
        return [
            'diunggah_oleh' => 'integer',
            'ukuran_byte' => 'integer',
            'revisi' => 'integer',
            'diperiksa_at' => 'immutable_datetime',
            'tersedia_at' => 'immutable_datetime',
            'dinonaktifkan_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $file): void {
            if (
                DB::transactionLevel() < 1 || ! isset(self::STATUS[$file->status]) || $file->ukuran_byte < 1
                || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $file->sha256) || blank($file->label)
            ) {
                throw new LogicException('Metadata berkas wajib lengkap dan disimpan melalui transaksi.');
            }
            if (! $file->exists) {
                if ($file->status !== self::MENUNGGU || $file->revisi !== 1) {
                    throw new LogicException('Berkas baru harus menunggu, revisi 1.');
                }
            } else {
                $asal = $file->getRawOriginal('status');
                $tujuan = [
                    self::MENUNGGU => [self::TERSEDIA, self::DITOLAK],
                    self::TERSEDIA => [self::TERSEDIA, self::DIHAPUS],
                    self::DIHAPUS => [self::TERSEDIA],
                    self::DITOLAK => []
                ];
                if (
                    $file->isDirty(self::IDENTITAS) || ! in_array($file->status, $tujuan[$asal] ?? [], true)
                    || $file->revisi !== (int) $file->getRawOriginal('revisi') + 1
                    || ($asal === self::TERSEDIA && $file->status === self::TERSEDIA && ! $file->isDirty(['label', 'keterangan']))
                ) {
                    throw new LogicException('Identitas berkas tetap; perubahan/transisi harus sah dan menaikkan revisi.');
                }
                if ($asal !== self::MENUNGGU && $file->isDirty('tersedia_at')) {
                    throw new LogicException('Waktu ketersediaan pertama tidak boleh diubah.');
                }
            }
            $siap = in_array($file->status, [self::TERSEDIA, self::DIHAPUS], true);
            if ($siap !== ($file->tersedia_at !== null) || ($file->status === self::DIHAPUS) !== ($file->dinonaktifkan_at !== null)) {
                throw new LogicException('Status dan waktu berkas tidak sesuai.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Berkas tidak dihapus fisik melalui model. Gunakan nonaktifkan.');
        });
    }
    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'berkas');
    }
    public function versiForm(): string
    {
        return hash_hmac('sha256', 'berkas:' . $this->id . ':' . $this->revisi, (string) config('app.key'));
    }
    public function labelStatus(): string
    {
        return self::STATUS[$this->status];
    }
    public function ukuranLabel(): string
    {
        return number_format($this->ukuran_byte / 1048576, 2, ',', '.') . ' MB';
    }
    public function ringkasanAudit(): array
    {
        return $this->only([
            'diunggah_oleh',
            'nama_asli',
            'label',
            'keterangan',
            'mime_type',
            'ekstensi',
            'ukuran_byte',
            'sha256',
            'pemeriksaan',
            'diperiksa_at',
            'status',
            'pesan_status',
            'tersedia_at',
            'dinonaktifkan_at',
            'revisi'
        ]);
    }

    public function materiLampiran(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MateriBerkas::class, 'berkas_id');
    }

    public function kegiatanLampiran(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\KegiatanBerkas::class, 'berkas_id');
    }

    public function pengumpulanLampiran(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PengumpulanBerkas::class, 'berkas_id');
    }

    public function pembayaran(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Pembayaran::class, 'bukti_berkas_id');
    }
    public function lampiranPermohonanSurat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PermohonanSurat::class, 'lampiran_berkas_id');
    }
    public function hasilPermohonanSurat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PermohonanSurat::class, 'hasil_berkas_id');
    }
}
