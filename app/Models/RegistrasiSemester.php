<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;
use LogicException;

class RegistrasiSemester extends Model
{
    use HasFactory;

    public const TERDAFTAR = 'terdaftar';
    public const AKTIF = 'aktif';
    public const CUTI = 'cuti';
    public const BATAL = 'batal';

    public const STATUS = [
        self::TERDAFTAR => 'Terdaftar',
        self::AKTIF => 'Aktif',
        self::CUTI => 'Cuti',
        self::BATAL => 'Batal',
    ];

    public const MENGISI_KURSI = [self::TERDAFTAR, self::AKTIF];

    protected $table = 'registrasi_semester';

    // Penulisan dilakukan melalui SimpanRegistrasiSemester.
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'riwayat_studi_id' => 'integer',
            'rombel_id' => 'integer',
            'periode_akademik_id' => 'integer',
            'semester_studi' => 'integer',
            'revisi' => 'integer',
            'penempatan_dikunci_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $registrasi): void {
            if ($registrasi->getConnection()->transactionLevel() < 1) {
                throw new LogicException('Simpan registrasi melalui transaksi SimpanRegistrasiSemester.');
            }

            if (
                ! isset(self::STATUS[$registrasi->status])
                || $registrasi->semester_studi < 1
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Status atau semester studi tidak valid.',
                ]);
            }

            if (
                in_array($registrasi->status, [self::CUTI, self::BATAL], true)
                && mb_strlen(trim((string) $registrasi->alasan_status)) < 10
            ) {
                throw ValidationException::withMessages([
                    'alasan_status' => 'Alasan cuti atau batal minimal 10 karakter.',
                ]);
            }

            if (
                $registrasi->status === self::AKTIF
                && $registrasi->penempatan_dikunci_at === null
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Registrasi aktif harus memiliki penempatan yang dikunci.',
                ]);
            }

            if (! $registrasi->exists) {
                if (
                    $registrasi->status !== self::TERDAFTAR
                    || $registrasi->revisi !== 1
                    || $registrasi->penempatan_dikunci_at !== null
                ) {
                    throw ValidationException::withMessages([
                        'status' => 'Registrasi baru harus berstatus terdaftar.',
                    ]);
                }

                return;
            }

            if ($registrasi->isDirty(['riwayat_studi_id', 'periode_akademik_id'])) {
                throw ValidationException::withMessages([
                    'riwayat_studi_id' => 'Mahasiswa dan periode registrasi tidak dapat diganti.',
                ]);
            }

            if (
                $registrasi->getRawOriginal('penempatan_dikunci_at') !== null
                && $registrasi->isDirty([
                    'rombel_id',
                    'semester_studi',
                    'penempatan_dikunci_at',
                ])
            ) {
                throw ValidationException::withMessages([
                    'rombel_id' => 'Penempatan telah dikunci sejak aktivasi pertama.',
                ]);
            }

            if ($registrasi->revisi !== ((int) $registrasi->getRawOriginal('revisi') + 1)) {
                throw ValidationException::withMessages([
                    'versi' => 'Revisi registrasi tidak valid. Muat ulang halaman.',
                ]);
            }

            $sebelumnya = new self();
            $sebelumnya->setRawAttributes($registrasi->getRawOriginal(), true);

            if (! array_key_exists($registrasi->status, $sebelumnya->pilihanStatus())) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status tidak diizinkan.',
                ]);
            }
        });

        static::deleting(function (): never {
            throw ValidationException::withMessages([
                'registrasi' => 'Riwayat registrasi tidak boleh dihapus. Gunakan status batal.',
            ]);
        });
    }

    public function riwayatStudi(): BelongsTo
    {
        return $this->belongsTo(RiwayatStudi::class, 'riwayat_studi_id');
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }

    public function periodeAkademik(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_akademik_id');
    }

    public function scopeMenempatiKursi(Builder $query): Builder
    {
        return $query->whereIn('status', self::MENGISI_KURSI);
    }

    public function penempatanDapatDiubah(): bool
    {
        return $this->penempatan_dikunci_at === null;
    }

    public function dapatDiubah(): bool
    {
        return $this->riwayatStudi->status === RiwayatStudi::AKTIF
            && in_array(
                $this->periodeAkademik->status,
                Rombel::STATUS_PERIODE_TERBUKA,
                true
            );
    }

    public function pilihanStatus(): array
    {
        $boleh = match ($this->status) {
            self::AKTIF => [self::AKTIF, self::CUTI, self::BATAL],
            self::CUTI => $this->penempatanDapatDiubah()
                ? [self::TERDAFTAR, self::AKTIF, self::CUTI, self::BATAL]
                : [self::AKTIF, self::CUTI, self::BATAL],
            self::BATAL => $this->penempatanDapatDiubah()
                ? [self::TERDAFTAR, self::BATAL]
                : [self::AKTIF, self::BATAL],
            default => [self::TERDAFTAR, self::AKTIF, self::CUTI, self::BATAL],
        };

        return array_intersect_key(self::STATUS, array_flip($boleh));
    }

    public function versiForm(): string
    {
        $data = $this->getRawOriginal();
        ksort($data);

        return hash_hmac(
            'sha256',
            json_encode($data, JSON_THROW_ON_ERROR),
            (string) config('app.key')
        );
    }

    public function krs(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Krs::class, 'registrasi_semester_id');
    }

    public function tagihan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Tagihan::class, 'registrasi_semester_id');
    }

    public function permohonanSurat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PermohonanSurat::class, 'registrasi_semester_id');
    }
}
