<?php

namespace App\Models;

use App\Support\TokenPresensi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use LogicException;

class PresensiPertemuan extends Model
{
    public const TERBUKA = 'terbuka';
    public const DITUTUP = 'ditutup';
    protected $table = 'presensi_pertemuan';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'pertemuan_id' => 'integer',
            'kelas_kuliah_id' => 'integer',
            'jumlah_peserta' => 'integer',
            'revisi' => 'integer',
            'dibuka_oleh' => 'integer',
            'ditutup_oleh' => 'integer',
            'dibuka_at' => 'immutable_datetime',
            'ditutup_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $daftar): void {
            if (
                DB::transactionLevel() < 1 || $daftar->jumlah_peserta < 1 || $daftar->revisi < 1
                || ! $daftar->dibuka_oleh || ! $daftar->dibuka_at
            ) {
                throw new LogicException('Daftar presensi harus lengkap dan disimpan dalam transaksi.');
            }
            if (! $daftar->exists) {
                if (
                    $daftar->status !== self::TERBUKA || $daftar->revisi !== 1
                    || $daftar->ditutup_oleh !== null || $daftar->ditutup_at !== null
                ) {
                    throw new LogicException('Daftar baru harus terbuka dengan revisi pertama.');
                }
                return;
            }
            if (
                $daftar->isDirty(['pertemuan_id', 'kelas_kuliah_id', 'jumlah_peserta', 'dibuka_oleh', 'dibuka_at'])
                || $daftar->getRawOriginal('status') !== self::TERBUKA || $daftar->status !== self::DITUTUP
                || $daftar->revisi !== (int) $daftar->getRawOriginal('revisi') + 1
                || ! $daftar->ditutup_oleh || ! $daftar->ditutup_at
                || $daftar->ditutup_at->lt($daftar->dibuka_at)
            ) {
                throw new LogicException('Daftar hanya dapat ditutup sekali; identitas dan peserta tetap.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Daftar presensi tidak boleh dihapus.');
        });
    }

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id');
    }
    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }
    public function pembuka(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuka_oleh');
    }
    public function penutup(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditutup_oleh');
    }
    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'presensi_pertemuan_id');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'presensi_pertemuan');
    }

    public function versiPenutupan(?Collection $semuaBaris = null): string
    {
        // Seluruh daftar, bukan hanya baris yang tampil pada satu halaman/filter.
        $semuaBaris ??= $this->presensi()->orderBy('id')->get(['id', 'revisi']);
        $versi = $semuaBaris->sortBy('id')->values()
            ->map(fn(Presensi $baris): array => [$baris->id, $baris->revisi])->all();
        return TokenPresensi::buat('penutupan-presensi', [$this->id, $this->revisi, $versi]);
    }

    public function ringkasanAudit(): array
    {
        return $this->only([
            'pertemuan_id',
            'kelas_kuliah_id',
            'status',
            'jumlah_peserta',
            'dibuka_oleh',
            'dibuka_at',
            'ditutup_oleh',
            'ditutup_at',
            'revisi'
        ]);
    }
}
