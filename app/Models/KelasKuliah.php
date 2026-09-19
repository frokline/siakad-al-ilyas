<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;
use LogicException;

class KelasKuliah extends Model
{
    use HasFactory;

    public const PERSIAPAN = 'persiapan';
    public const AKTIF = 'aktif';
    public const SELESAI = 'selesai';
    public const ARSIP = 'arsip';

    public const STATUS = [
        self::PERSIAPAN => 'Persiapan',
        self::AKTIF => 'Aktif',
        self::SELESAI => 'Selesai',
        self::ARSIP => 'Arsip',
    ];

    public const BELUM_TUNTAS = [self::PERSIAPAN, self::AKTIF];

    protected $table = 'kelas_kuliah';

    // Gunakan SimpanKelasKuliah untuk seluruh penulisan.
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'rombel_id' => 'integer',
            'detail_paket_id' => 'integer',
            'sks_snapshot' => 'decimal:1',
            'revisi' => 'integer',
            'diaktifkan_at' => 'immutable_datetime',
            'diselesaikan_at' => 'immutable_datetime',
            'diarsipkan_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $kelas): void {
            if ($kelas->getConnection()->transactionLevel() < 1) {
                throw new LogicException('Simpan kelas melalui transaksi SimpanKelasKuliah.');
            }

            if (! preg_match('/\A[A-Z0-9][A-Z0-9._-]{0,39}\z/', (string) $kelas->kode)) {
                throw ValidationException::withMessages([
                    'kode' => 'Kode maksimal 40 karakter: huruf besar, angka, titik, garis bawah, atau tanda hubung.',
                ]);
            }

            if (
                trim((string) $kelas->nama_mk_snapshot) === ''
                || mb_strlen((string) $kelas->nama_mk_snapshot) > 150
                || (float) $kelas->sks_snapshot <= 0
            ) {
                throw ValidationException::withMessages([
                    'detail_paket_id' => 'Nama mata kuliah atau SKS sumber tidak valid.',
                ]);
            }

            $kelas->pastikanWaktuStatusValid();

            if (! $kelas->exists) {
                if ($kelas->status !== self::PERSIAPAN || $kelas->revisi !== 1) {
                    throw ValidationException::withMessages([
                        'status' => 'Kelas baru harus berstatus persiapan dengan revisi pertama.',
                    ]);
                }

                return;
            }

            if ($kelas->isDirty([
                'rombel_id',
                'detail_paket_id',
                'nama_mk_snapshot',
                'sks_snapshot',
            ])) {
                throw ValidationException::withMessages([
                    'kelas' => 'Rombel, mata kuliah, nama penawaran, dan SKS kelas tidak dapat diganti.',
                ]);
            }

            if (
                $kelas->getRawOriginal('diaktifkan_at') !== null
                && $kelas->isDirty(['kode', 'diaktifkan_at'])
            ) {
                throw ValidationException::withMessages([
                    'kode' => 'Kode dan waktu aktivasi terkunci sejak aktivasi pertama.',
                ]);
            }

            if (
                $kelas->getRawOriginal('diselesaikan_at') !== null
                && $kelas->isDirty('diselesaikan_at')
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Waktu penyelesaian kelas tidak dapat diganti.',
                ]);
            }

            $asal = new self();
            $asal->setRawAttributes($kelas->getRawOriginal(), true);

            if (! array_key_exists($kelas->status, $asal->pilihanStatus())) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status kelas tidak diizinkan.',
                ]);
            }

            if ($asal->status === self::ARSIP && $asal->diaktifkan_at !== null) {
                throw ValidationException::withMessages([
                    'kelas' => 'Arsip kelas yang sudah berjalan bersifat tetap.',
                ]);
            }

            if ($kelas->revisi !== ((int) $kelas->getRawOriginal('revisi') + 1)) {
                throw ValidationException::withMessages([
                    'versi' => 'Revisi tidak valid. Muat ulang formulir.',
                ]);
            }
        });

        static::deleting(function (): never {
            throw ValidationException::withMessages([
                'kelas' => 'Kelas tidak boleh dihapus. Gunakan alur pengarsipan.',
            ]);
        });
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }

    public function detailPaket(): BelongsTo
    {
        return $this->belongsTo(DetailPaket::class, 'detail_paket_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::AKTIF);
    }

    public function scopeBelumTuntas(Builder $query): Builder
    {
        return $query->whereIn('status', self::BELUM_TUNTAS);
    }

    public function kodeDapatDiubah(): bool
    {
        return $this->diaktifkan_at === null;
    }

    public function dapatDiubah(): bool
    {
        return in_array(
            $this->rombel->periodeAkademik->status,
            Rombel::STATUS_PERIODE_TERBUKA,
            true
        ) && ! ($this->status === self::ARSIP && $this->diaktifkan_at !== null);
    }

    public function pilihanStatus(): array
    {
        $boleh = match ($this->status) {
            self::AKTIF => [self::AKTIF, self::SELESAI],
            self::SELESAI => [self::SELESAI, self::ARSIP],
            self::ARSIP => $this->diaktifkan_at === null
                ? [self::PERSIAPAN, self::ARSIP]
                : [self::ARSIP],
            default => [self::PERSIAPAN, self::AKTIF, self::ARSIP],
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

    private function pastikanWaktuStatusValid(): void
    {
        $aktif = $this->diaktifkan_at;
        $selesai = $this->diselesaikan_at;
        $arsip = $this->diarsipkan_at;

        $valid = match ($this->status) {
            self::PERSIAPAN => $aktif === null && $selesai === null && $arsip === null,
            self::AKTIF => $aktif !== null && $selesai === null && $arsip === null,
            self::SELESAI => $aktif !== null && $selesai !== null
                && $selesai->gte($aktif) && $arsip === null,
            self::ARSIP => $arsip !== null && (
                ($aktif === null && $selesai === null)
                || ($aktif !== null && $selesai !== null
                    && $selesai->gte($aktif) && $arsip->gte($selesai))
            ),
            default => false,
        };

        if (! $valid) {
            throw ValidationException::withMessages([
                'status' => 'Status dan waktu perubahan tidak sesuai. Periksa waktu server dan ulangi.',
            ]);
        }
    }

    public function detailKrs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\DetailKrs::class, 'kelas_kuliah_id');
    }

    public function pesertaAktif(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->detailKrs()->pesertaAktif();
    }

    public function pengajarKelas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PengajarKelas::class, 'kelas_kuliah_id');
    }

    public function pengajarAktif(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->pengajarKelas()->aktif();
    }

    public function koordinatorAktif(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\PengajarKelas::class, 'kelas_kuliah_id')
            ->where('aktif', true)->where('peran', \App\Models\PengajarKelas::KOORDINATOR);
    }

    public function versiTimPengajar(): string
    {
        $this->loadMissing('pengajarKelas');
        $atribut = static function (\Illuminate\Database\Eloquent\Model $model): array {
            $data = $model->getRawOriginal();
            ksort($data);
            return $data;
        };

        $data = [
            'lingkup' => 'tim-pengajar-kelas',
            'kelas' => $atribut($this),
            'tim' => $this->pengajarKelas->sortBy('id')->map($atribut)->values()->all(),
        ];

        return hash_hmac('sha256', json_encode($data, JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    public function jadwalKuliah(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\JadwalKuliah::class, 'kelas_kuliah_id');
    }

    public function versiJadwalKuliah(): string
    {
        $this->loadMissing(['jadwalKuliah', 'pengajarKelas', 'rombel.periodeAkademik']);
        $atribut = static function (\Illuminate\Database\Eloquent\Model $model): array {
            $nilai = $model->getAttributes();
            ksort($nilai);
            return $nilai;
        };
        $kunci = (string) config('app.key');
        if ($kunci === '') {
            throw new \LogicException('APP_KEY belum dikonfigurasi.');
        }

        return hash_hmac('sha256', json_encode([
            'lingkup' => 'jadwal-kelas',
            'kelas' => $atribut($this),
            'rombel' => $atribut($this->rombel),
            'periode' => $atribut($this->rombel->periodeAkademik),
            'tim' => $this->pengajarKelas->sortBy('id')->map($atribut)->values()->all(),
            'jadwal' => $this->jadwalKuliah->sortBy('id')->map($atribut)->values()->all(),
        ], JSON_THROW_ON_ERROR), $kunci);
    }

    public function pertemuan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Pertemuan::class, 'kelas_kuliah_id');
    }

    public function versiPertemuan(): string
    {
        $this->loadMissing(['pertemuan', 'pengajarKelas', 'jadwalKuliah', 'rombel.periodeAkademik']);
        $atribut = static function (\Illuminate\Database\Eloquent\Model $model): array {
            $data = $model->getAttributes();
            ksort($data);
            return $data;
        };
        $kunci = (string) config('app.key');
        if ($kunci === '') {
            throw new \LogicException('APP_KEY belum dikonfigurasi.');
        }
        return hash_hmac('sha256', json_encode([
            'lingkup' => 'pertemuan-kelas',
            'kelas' => $atribut($this),
            'rombel' => $atribut($this->rombel),
            'periode' => $atribut($this->rombel->periodeAkademik),
            'tim' => $this->pengajarKelas->sortBy('id')->map($atribut)->values()->all(),
            'jadwal' => $this->jadwalKuliah->sortBy('id')->map($atribut)->values()->all(),
            'pertemuan' => $this->pertemuan->sortBy('id')->map($atribut)->values()->all(),
        ], JSON_THROW_ON_ERROR), $kunci);
    }

    public function presensiPertemuan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PresensiPertemuan::class, 'kelas_kuliah_id');
    }

    public function presensi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Presensi::class, 'kelas_kuliah_id');
    }

    public function kegiatan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Kegiatan::class, 'kelas_kuliah_id');
    }

    public function sasaranPengumuman(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SasaranPengumuman::class, 'kelas_kuliah_id');
    }
}
