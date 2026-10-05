<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DetailKrs extends Model
{
    public const TERDAFTAR = 'terdaftar';
    public const AKTIF = 'aktif';
    public const DIBATALKAN = 'dibatalkan';
    public const STATUS = [
        self::TERDAFTAR => 'Menunggu pengesahan',
        self::AKTIF => 'Disahkan',
        self::DIBATALKAN => 'Dibatalkan',
    ];

    protected $table = 'detail_krs';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'krs_id' => 'integer',
            'kelas_kuliah_id' => 'integer',
            'aktif_at' => 'immutable_datetime',
            'batal_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $detail): void {
            if (DB::transactionLevel() < 1 || $detail->krs_id < 1 || $detail->kelas_kuliah_id < 1) {
                self::gagal('Detail KRS harus disimpan melalui transaksi KelolaKrs.');
            }

            if ($detail->exists && $detail->isDirty(['krs_id', 'kelas_kuliah_id'])) {
                self::gagal('KRS dan kelas pada detail tidak boleh diganti.');
            }

            if (! $detail->exists && $detail->status !== self::TERDAFTAR) {
                self::gagal('Detail baru harus berstatus terdaftar.');
            }

            $valid = match ($detail->status) {
                self::TERDAFTAR => $detail->aktif_at === null && $detail->batal_at === null,
                self::AKTIF => $detail->aktif_at !== null && $detail->batal_at === null,
                self::DIBATALKAN => $detail->batal_at !== null
                    && ($detail->aktif_at === null || $detail->batal_at->gte($detail->aktif_at)),
                default => false,
            };

            if (! $valid) {
                self::gagal('Status dan waktu pada detail KRS tidak sesuai.');
            }
        });

        static::deleting(function (): never {
            self::gagal('Detail KRS tidak dihapus; riwayat peserta harus dipertahankan.');
        });
    }

    public function krs(): BelongsTo
    {
        return $this->belongsTo(Krs::class, 'krs_id');
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    public function scopeDisahkan(Builder $query): Builder
    {
        return $query->where('detail_krs.status', self::AKTIF)
            ->whereHas('krs', fn(Builder $krs) => $krs->where('status', Krs::DISAHKAN));
    }

    public function scopePesertaAktif(Builder $query): Builder
    {
        return $query->disahkan()
            ->whereHas('krs.registrasiSemester', fn(Builder $reg) => $reg->where('status', RegistrasiSemester::AKTIF))
            ->whereHas('krs.registrasiSemester.riwayatStudi', fn(Builder $rs) => $rs->where('status', RiwayatStudi::AKTIF))
            ->whereHas('kelasKuliah', fn(Builder $kelas) => $kelas->where('status', KelasKuliah::AKTIF))
            ->whereHas('kelasKuliah.rombel.periodeAkademik', fn(Builder $periode) => $periode->where('status', 'aktif'));
    }

    private static function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['krs' => $pesan]);
    }

    public function pengumpulan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Pengumpulan::class, 'detail_krs_id');
    }
}
