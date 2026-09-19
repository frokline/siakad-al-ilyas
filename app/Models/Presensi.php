<?php

namespace App\Models;

use App\Support\TokenPresensi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use LogicException;

class Presensi extends Model
{
    public const BELUM = 'belum_dicatat';
    public const PILIHAN = ['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'];
    public const STATUS = [self::BELUM => 'Belum dicatat', ...self::PILIHAN];
    protected $table = 'presensi';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'presensi_pertemuan_id' => 'integer',
            'kelas_kuliah_id' => 'integer',
            'detail_krs_id' => 'integer',
            'mahasiswa_id' => 'integer',
            'peserta_snapshot' => 'array',
            'dicatat_oleh' => 'integer',
            'revisi' => 'integer',
            'dicatat_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $baris): void {
            if (
                DB::transactionLevel() < 1 || ! isset(self::STATUS[$baris->status]) || $baris->revisi < 1
                || ! is_array($baris->peserta_snapshot)
                || ! isset($baris->peserta_snapshot['nim'], $baris->peserta_snapshot['nama'])
            ) {
                throw new LogicException('Presensi harus lengkap dan disimpan dalam transaksi.');
            }
            if (! $baris->exists) {
                if (
                    $baris->status !== self::BELUM || $baris->revisi !== 1
                    || $baris->dicatat_oleh !== null || $baris->dicatat_at !== null || $baris->catatan !== null
                ) {
                    throw new LogicException('Peserta baru harus berstatus belum dicatat.');
                }
                return;
            }
            if (
                $baris->isDirty(['presensi_pertemuan_id', 'kelas_kuliah_id', 'detail_krs_id', 'mahasiswa_id', 'peserta_snapshot'])
                || $baris->status === self::BELUM || ! $baris->dicatat_oleh || ! $baris->dicatat_at
                || $baris->revisi !== (int) $baris->getRawOriginal('revisi') + 1
                || ! $baris->isDirty(['status', 'catatan']) || mb_strlen((string) $baris->catatan) > 1000
            ) {
                throw new LogicException('Identitas peserta tetap; setiap perubahan wajib menaikkan revisi.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Riwayat presensi tidak boleh dihapus.');
        });
    }

    public function daftar(): BelongsTo
    {
        return $this->belongsTo(PresensiPertemuan::class, 'presensi_pertemuan_id');
    }
    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }
    public function detailKrs(): BelongsTo
    {
        return $this->belongsTo(DetailKrs::class, 'detail_krs_id');
    }
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'presensi');
    }
    public function labelStatus(): string
    {
        return self::STATUS[$this->status];
    }
    public function versiForm(): string
    {
        return TokenPresensi::buat('baris-presensi', [$this->id, $this->revisi]);
    }
    public function ringkasanAudit(): array
    {
        return $this->only([
            'presensi_pertemuan_id',
            'kelas_kuliah_id',
            'detail_krs_id',
            'mahasiswa_id',
            'peserta_snapshot',
            'status',
            'catatan',
            'dicatat_oleh',
            'dicatat_at',
            'revisi'
        ]);
    }
}
