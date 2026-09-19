<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class PengajarKelas extends Model
{
    public const KOORDINATOR = 'koordinator';
    public const PENGAJAR = 'pengajar';

    public const PERAN = [
        self::KOORDINATOR => 'Koordinator',
        self::PENGAJAR => 'Pengajar',
    ];

    public const AKSI_AUDIT = [
        'buat' => 'Membuat penugasan',
        'ubah' => 'Mengubah penugasan',
        'ganti_koordinator' => 'Mengalihkan tugas koordinator',
    ];

    protected $table = 'pengajar_kelas';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'kelas_kuliah_id' => 'integer',
            'dosen_id' => 'integer',
            'aktif' => 'boolean',
            'revisi' => 'integer',
            'koordinator_aktif' => 'integer',
            'diaktifkan_at' => 'immutable_datetime',
            'dinonaktifkan_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $penugasan): void {
            if ($penugasan->getConnection()->transactionLevel() < 1) {
                self::gagal('Simpan penugasan melalui transaksi SimpanPengajarKelas.');
            }

            if (
                $penugasan->kelas_kuliah_id < 1 || $penugasan->dosen_id < 1
                || ! isset(self::PERAN[$penugasan->peran])
                || $penugasan->revisi < 1 || $penugasan->revisi > 4294967295
                || $penugasan->isDirty('koordinator_aktif')
            ) {
                self::gagal('Identitas, peran, atau revisi penugasan tidak valid.');
            }

            $waktuValid = $penugasan->diaktifkan_at !== null && (
                ($penugasan->aktif && $penugasan->dinonaktifkan_at === null)
                || (! $penugasan->aktif && $penugasan->dinonaktifkan_at !== null
                    && $penugasan->dinonaktifkan_at->gte($penugasan->diaktifkan_at))
            );
            if (! $waktuValid) {
                self::gagal('Status dan waktu penugasan tidak sesuai. Periksa waktu server.');
            }

            if (! $penugasan->exists) {
                if (! $penugasan->aktif || $penugasan->revisi !== 1) {
                    self::gagal('Penugasan baru harus aktif pada revisi pertama.');
                }
                return;
            }

            if (
                $penugasan->isDirty(['kelas_kuliah_id', 'dosen_id'])
                || $penugasan->revisi !== (int) $penugasan->getRawOriginal('revisi') + 1
            ) {
                self::gagal('Kelas dan dosen tetap; revisi harus bertambah satu.');
            }

            $asal = new self();
            $asal->setRawAttributes($penugasan->getRawOriginal(), true);

            if ($asal->aktif && $penugasan->isDirty('diaktifkan_at')) {
                self::gagal('Waktu awal penugasan yang masih berjalan tidak boleh diganti.');
            }
            if (! $penugasan->aktif && $penugasan->isDirty('peran')) {
                self::gagal('Saat menonaktifkan, pertahankan peran sebelumnya.');
            }
            if (! $asal->aktif && ! $penugasan->aktif) {
                self::gagal('Penugasan nonaktif hanya dapat diaktifkan kembali.');
            }
            if (
                ! $asal->aktif && $penugasan->aktif
                && $penugasan->diaktifkan_at->lt($asal->dinonaktifkan_at)
            ) {
                self::gagal('Aktivasi ulang tidak boleh mendahului penonaktifan sebelumnya.');
            }
        });

        static::deleting(function (): never {
            self::gagal('Penugasan tidak dihapus. Gunakan status nonaktif.');
        });
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'pengajar_kelas');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('pengajar_kelas.aktif', true);
    }

    public function scopeBolehMengajar(Builder $query): Builder
    {
        return $query->aktif()
            ->whereHas('dosen', fn(Builder $dosen) => $dosen->where('status', Dosen::AKTIF))
            ->whereHas('dosen.user', fn(Builder $user) => $user->where('status', User::STATUS_AKTIF))
            ->whereHas('dosen.user.roles', fn(Builder $role) => $role->where('kode', Role::DOSEN))
            ->whereHas('kelasKuliah', fn(Builder $kelas) => $kelas->where('status', KelasKuliah::AKTIF))
            ->whereHas('kelasKuliah.rombel.periodeAkademik', fn(Builder $periode) => $periode->where('status', 'aktif'));
    }

    public function isKoordinatorAktif(): bool
    {
        return $this->aktif && $this->peran === self::KOORDINATOR;
    }

    public function dapatDiubah(): bool
    {
        return in_array($this->kelasKuliah->status, KelasKuliah::BELUM_TUNTAS, true)
            && in_array($this->kelasKuliah->rombel->periodeAkademik->status, Rombel::STATUS_PERIODE_TERBUKA, true);
    }

    public function ringkasanAudit(): array
    {
        return [
            'pengajar_kelas_id' => $this->id,
            'kelas_kuliah_id' => $this->kelas_kuliah_id,
            'dosen_id' => $this->dosen_id,
            'peran' => $this->peran,
            'aktif' => $this->aktif,
            'revisi' => $this->revisi,
            'diaktifkan_at_utc' => $this->diaktifkan_at?->toDateTimeString(),
            'dinonaktifkan_at_utc' => $this->dinonaktifkan_at?->toDateTimeString(),
        ];
    }

    private static function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['penugasan' => $pesan]);
    }

    public function pertemuan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Pertemuan::class, 'pengajar_kelas_id');
    }
}
