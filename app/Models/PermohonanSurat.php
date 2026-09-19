<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

class PermohonanSurat extends Model
{
    public const STATUS = ['diajukan' => 'Diajukan', 'diproses' => 'Diproses', 'ditolak' => 'Ditolak', 'terbit' => 'Terbit', 'dibatalkan' => 'Dibatalkan'];
    public const TRANSISI = [
        'diajukan' => ['diproses', 'ditolak', 'dibatalkan'],
        'diproses' => ['terbit', 'ditolak', 'dibatalkan'],
        'terbit' => ['dibatalkan'],
        'ditolak' => [],
        'dibatalkan' => []
    ];
    protected $table = 'permohonan_surat';
    protected $guarded = ['*'];
    protected $hidden = ['form_token', 'hash_permohonan', 'slot_aktif'];
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'registrasi_semester_id' => 'integer',
            'mahasiswa_id' => 'integer',
            'pemohon_id' => 'integer',
            'jenis_surat_id' => 'integer',
            'lampiran_berkas_id' => 'integer',
            'hasil_berkas_id' => 'integer',
            'revisi' => 'integer',
            'akademik_snapshot' => 'array',
            'jenis_snapshot' => 'array',
            'lampiran_snapshot' => 'array',
            'hasil_snapshot' => 'array',
            'diajukan_at' => 'immutable_datetime',
            'terbit_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $p): void {
            $slot = in_array($p->status, ['diajukan', 'diproses'], true) ? self::slot($p->registrasi_semester_id, $p->jenis_surat_id) : null;
            if (
                $p->getConnection()->transactionLevel() < 1 || ! isset(self::STATUS[$p->status]) || $p->revisi < 1
                || $p->slot_aktif !== $slot || ! Str::isUuid((string) $p->form_token)
                || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $p->hash_permohonan)
                || ! preg_match('/\ASRT-[A-Z0-9]{26}\z/', (string) $p->nomor_pengajuan)
                || ! is_array($p->akademik_snapshot) || ! is_array($p->jenis_snapshot)
                || mb_strlen(trim((string) $p->keperluan)) < 10 || mb_strlen((string) $p->keperluan) > 2000
                || ! $p->diajukan_at || (($p->lampiran_berkas_id !== null) !== ($p->lampiran_snapshot !== null))
            ) {
                throw new LogicException('Permohonan harus lengkap dan ditulis melalui KelolaPermohonanSurat.');
            }
            $adaHasil = $p->hasil_berkas_id !== null;
            if (
                $adaHasil !== ($p->hasil_snapshot !== null) || $adaHasil !== ($p->nomor_surat !== null)
                || $adaHasil !== ($p->terbit_at !== null) || ($p->status === 'terbit' && ! $adaHasil)
                || ($adaHasil && ! in_array($p->status, ['terbit', 'dibatalkan'], true))
            ) {
                throw new LogicException('Penerbitan harus lengkap; hasil lama tidak boleh dihapus.');
            }
            if (! $p->exists) {
                if ($p->status !== 'diajukan' || $p->revisi !== 1 || $adaHasil) {
                    throw new LogicException('Permohonan baru harus diajukan, revisi satu.');
                }
                return;
            }
            if (
                $p->isDirty([
                    'nomor_pengajuan',
                    'registrasi_semester_id',
                    'mahasiswa_id',
                    'pemohon_id',
                    'jenis_surat_id',
                    'keperluan',
                    'akademik_snapshot',
                    'jenis_snapshot',
                    'lampiran_berkas_id',
                    'lampiran_snapshot',
                    'diajukan_at',
                    'form_token',
                    'hash_permohonan'
                ])
                || $p->revisi !== (int) $p->getRawOriginal('revisi') + 1
                || ! in_array($p->status, self::TRANSISI[$p->getRawOriginal('status')] ?? [], true)
            ) {
                throw new LogicException('Isi pengajuan tetap; hanya transisi status sah yang diperbolehkan.');
            }
            if ($p->getRawOriginal('status') !== 'diproses' || $p->status !== 'terbit') {
                if ($p->isDirty(['nomor_surat', 'hasil_berkas_id', 'hasil_snapshot', 'terbit_at'])) {
                    throw new LogicException('Dokumen final dan nomor tetap setelah terbit.');
                }
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Permohonan tidak dihapus. Gunakan pembatalan.');
        });
    }
    public static function slot(int $registrasi, int $jenis): string
    {
        return hash('sha256', $registrasi . ':' . $jenis);
    }
    public function registrasiSemester(): BelongsTo
    {
        return $this->belongsTo(RegistrasiSemester::class, 'registrasi_semester_id');
    }
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }
    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_id');
    }
    public function lampiran(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'lampiran_berkas_id');
    }
    public function hasil(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'hasil_berkas_id');
    }
    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatSurat::class, 'permohonan_surat_id');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'permohonan_surat');
    }
    public function versiForm(): string
    {
        return hash_hmac('sha256', 'permohonan_surat:' . $this->id . ':' . $this->revisi, (string) config('app.key'));
    }
    public function ringkasanAudit(): array
    {
        return $this->only([
            'nomor_pengajuan',
            'registrasi_semester_id',
            'mahasiswa_id',
            'pemohon_id',
            'jenis_surat_id',
            'keperluan',
            'akademik_snapshot',
            'jenis_snapshot',
            'lampiran_berkas_id',
            'lampiran_snapshot',
            'status',
            'nomor_surat',
            'hasil_berkas_id',
            'hasil_snapshot',
            'diajukan_at',
            'terbit_at',
            'revisi'
        ]);
    }
}
