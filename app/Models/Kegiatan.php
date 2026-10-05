<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Kegiatan extends Model
{
    public const MATERI = 'materi';
    public const TUGAS = 'tugas';
    public const LATIHAN = 'latihan';
    public const UTS = 'uts';
    public const UAS = 'uas';

    public const TERBIT = 'terbit';
    public const DITUTUP = 'ditutup';
    public const ARSIP = 'arsip';

    public const JENIS = [
        self::MATERI => 'Materi',
        self::TUGAS => 'Tugas',
        self::LATIHAN => 'Latihan',
        self::UTS => 'UTS',
        self::UAS => 'UAS',
    ];

    public const STATUS = [
        self::TERBIT => 'Terbit',
        self::DITUTUP => 'Ditutup',
        self::ARSIP => 'Arsip',
    ];

    public const EKSTENSI_JAWABAN = [
        'pdf',
        'jpg',
        'png',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'zip',
    ];

    protected $table = 'kegiatan';

    protected $guarded = ['*'];

    protected $hidden = [
        'form_token',
        'hash_permohonan',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'kelas_kuliah_id' => 'integer',
            'pertemuan_id' => 'integer',
            'pembuat_id' => 'integer',
            'maks_ukuran_byte' => 'integer',
            'maks_berkas' => 'integer',
            'ekstensi_diizinkan' => 'array',
            'revisi' => 'integer',
            'buka_at' => 'immutable_datetime',
            'tenggat_at' => 'immutable_datetime',
            'terbit_at' => 'immutable_datetime',
            'ditutup_at' => 'immutable_datetime',
            'diarsipkan_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $kegiatan): void {
            if (
                $kegiatan->getConnection()->transactionLevel() < 1
                || blank($kegiatan->judul)
                || mb_strlen($kegiatan->judul) > 200
                || mb_strlen((string) $kegiatan->instruksi) > 10000
                || ! isset(self::JENIS[$kegiatan->jenis])
                || ! isset(self::STATUS[$kegiatan->status])
                || $kegiatan->revisi < 1
                || $kegiatan->revisi > 4294967295
                || ! preg_match(
                    '/\A[a-f0-9]{64}\z/',
                    (string) $kegiatan->hash_permohonan
                )
            ) {
                throw new LogicException(
                    'Pembelajaran harus valid dan disimpan melalui KelolaKegiatan.'
                );
            }

            self::periksaAturanJenis($kegiatan);
            self::periksaStatus($kegiatan);

            if (! $kegiatan->exists) {
                if (
                    $kegiatan->status !== self::TERBIT
                    || $kegiatan->terbit_at === null
                    || $kegiatan->revisi !== 1
                ) {
                    throw new LogicException(
                        'Pembelajaran baru harus langsung terbit dengan revisi pertama.'
                    );
                }

                return;
            }

            $statusLama = (string) $kegiatan->getRawOriginal(
                'status'
            );

            $transisi = [
                self::TERBIT => [
                    self::TERBIT,
                    self::DITUTUP,
                    self::ARSIP,
                ],
                self::DITUTUP => [
                    self::DITUTUP,
                    self::TERBIT,
                    self::ARSIP,
                ],
                self::ARSIP => [
                    self::ARSIP,
                    self::TERBIT,
                ],
            ];

            if (
                $kegiatan->isDirty([
                    'kelas_kuliah_id',
                    'pembuat_id',
                    'form_token',
                    'hash_permohonan',
                    'terbit_at',
                ])
                || $kegiatan->revisi !==
                    (int) $kegiatan->getRawOriginal('revisi') + 1
                || ! in_array(
                    $kegiatan->status,
                    $transisi[$statusLama] ?? [],
                    true
                )
            ) {
                throw new LogicException(
                    'Identitas pembelajaran, revisi, atau perubahan status tidak valid.'
                );
            }
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Pembelajaran tidak dihapus permanen. Gunakan arsip.'
            );
        });
    }

    private static function periksaAturanJenis(
        self $kegiatan
    ): void {
        if ($kegiatan->jenis === self::MATERI) {
            if (
                $kegiatan->metode !== 'informasi'
                || $kegiatan->tenggat_at !== null
                || $kegiatan->maks_ukuran_byte !== null
                || $kegiatan->maks_berkas !== null
                || $kegiatan->ekstensi_diizinkan !== null
            ) {
                throw new LogicException(
                    'Materi tidak menggunakan aturan pengumpulan jawaban.'
                );
            }

            return;
        }

        if (
            $kegiatan->metode !== 'pengumpulan_berkas'
            || $kegiatan->buka_at === null
            || $kegiatan->tenggat_at === null
            || ! $kegiatan->buka_at->lt($kegiatan->tenggat_at)
            || $kegiatan->maks_berkas < 1
            || $kegiatan->maks_berkas > 10
            || $kegiatan->maks_ukuran_byte < 1048576
            || $kegiatan->maks_ukuran_byte > 52428800
            || ! is_array($kegiatan->ekstensi_diizinkan)
            || count($kegiatan->ekstensi_diizinkan) < 1
            || array_diff(
                $kegiatan->ekstensi_diizinkan,
                self::EKSTENSI_JAWABAN
            ) !== []
            || count(array_unique(
                $kegiatan->ekstensi_diizinkan
            )) !== count($kegiatan->ekstensi_diizinkan)
        ) {
            throw new LogicException(
                'Jadwal atau ketentuan pengumpulan tidak valid.'
            );
        }
    }

    private static function periksaStatus(
        self $kegiatan
    ): void {
        $valid = match ($kegiatan->status) {
            self::TERBIT =>
                $kegiatan->terbit_at !== null
                && $kegiatan->ditutup_at === null
                && $kegiatan->diarsipkan_at === null,

            self::DITUTUP =>
                $kegiatan->terbit_at !== null
                && $kegiatan->ditutup_at !== null
                && $kegiatan->diarsipkan_at === null,

            self::ARSIP =>
                $kegiatan->terbit_at !== null
                && $kegiatan->diarsipkan_at !== null,

            default => false,
        };

        if (! $valid) {
            throw new LogicException(
                'Status dan waktu pembelajaran tidak sesuai.'
            );
        }
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(
            KelasKuliah::class,
            'kelas_kuliah_id'
        );
    }

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(
            Pertemuan::class,
            'pertemuan_id'
        );
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'pembuat_id'
        );
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(
            KegiatanBerkas::class,
            'kegiatan_id'
        )->where('aktif', true);
    }

    public function semuaLampiran(): HasMany
    {
        return $this->hasMany(
            KegiatanBerkas::class,
            'kegiatan_id'
        );
    }

    public function pengumpulan(): HasMany
    {
        return $this->hasMany(
            Pengumpulan::class,
            'kegiatan_id'
        );
    }

    public function audits(): HasMany
    {
        return $this->hasMany(
            AuditLog::class,
            'entitas_id'
        )->where('entitas', 'kegiatan');
    }

    public function versiForm(): string
    {
        return hash_hmac(
            'sha256',
            'kegiatan:' . $this->id . ':' . $this->revisi,
            (string) config('app.key')
        );
    }

    public function berupaMateri(): bool
    {
        return $this->jenis === self::MATERI;
    }

    public function memerlukanPengumpulan(): bool
    {
        return ! $this->berupaMateri()
            && $this->metode === 'pengumpulan_berkas';
    }

    public function dapatDilihat(
        ?CarbonImmutable $waktu = null
    ): bool {
        $waktu ??= CarbonImmutable::now('UTC');

        return $this->status !== self::ARSIP
            && $this->terbit_at !== null
            && $this->terbit_at->lte($waktu)
            && (
                $this->buka_at === null
                || $this->buka_at->lte($waktu)
            );
    }

    public function jendelaTerbuka(
        ?CarbonImmutable $waktu = null
    ): bool {
        $waktu ??= CarbonImmutable::now('UTC');

        return $this->memerlukanPengumpulan()
            && $this->status === self::TERBIT
            && $this->dapatDilihat($waktu)
            && $this->buka_at !== null
            && $this->tenggat_at !== null
            && $this->buka_at->lte($waktu)
            && $waktu->lt($this->tenggat_at);
    }

    public function labelJadwal(): string
    {
        if ($this->status === self::ARSIP) {
            return 'Arsip';
        }

        if ($this->status === self::DITUTUP) {
            return 'Ditutup oleh pengajar';
        }

        if ($this->berupaMateri()) {
            return $this->dapatDilihat()
                ? 'Tersedia'
                : 'Belum tersedia';
        }

        if (
            $this->buka_at !== null
            && now('UTC')->lt($this->buka_at)
        ) {
            return 'Belum mulai';
        }

        return $this->jendelaTerbuka()
            ? 'Dapat dikumpulkan'
            : 'Tenggat telah lewat';
    }

    public function ringkasanAudit(): array
    {
        return [
            ...$this->only([
                'kelas_kuliah_id',
                'pertemuan_id',
                'pembuat_id',
                'jenis',
                'metode',
                'judul',
                'instruksi',
                'tautan_eksternal',
                'buka_at',
                'tenggat_at',
                'maks_ukuran_byte',
                'maks_berkas',
                'ekstensi_diizinkan',
                'status',
                'terbit_at',
                'ditutup_at',
                'diarsipkan_at',
                'revisi',
            ]),
            'berkas_ids' => $this->lampiran()
                ->orderBy('berkas_id')
                ->pluck('berkas_id')
                ->all(),
        ];
    }
}