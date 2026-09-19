<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Kegiatan extends Model
{
    public const DRAF = 'draf';
    public const TERBIT = 'terbit';
    public const DITUTUP = 'ditutup';
    public const ARSIP = 'arsip';
    public const STATUS = ['draf' => 'Draf', 'terbit' => 'Terbit', 'ditutup' => 'Ditutup', 'arsip' => 'Arsip'];
    public const JENIS = ['tugas' => 'Tugas', 'latihan' => 'Latihan', 'uts' => 'UTS', 'uas' => 'UAS'];
    public const ISI_TETAP = [
        'pertemuan_id',
        'jenis',
        'metode',
        'judul',
        'instruksi',
        'buka_at',
        'maks_ukuran_byte',
        'maks_berkas',
        'ekstensi_diizinkan'
    ];
    protected $table = 'kegiatan';
    protected $guarded = ['*'];
    protected $hidden = ['form_token', 'hash_permohonan', 'instruksi'];

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
            'updated_at' => 'immutable_datetime'
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (self $k): void {
            if (
                $k->getConnection()->transactionLevel() < 1 || blank($k->judul) || mb_strlen($k->judul) > 200
                || blank($k->instruksi) || mb_strlen($k->instruksi) > 10000
                || ! isset(self::JENIS[$k->jenis]) || ! isset(self::STATUS[$k->status])
                || $k->metode !== 'pengumpulan_berkas' || $k->revisi < 1 || $k->revisi > 4294967295
                || ! preg_match('/\A[a-f0-9]{64}\z/', (string) $k->hash_permohonan)
            ) {
                throw new LogicException('Kegiatan harus valid dan ditulis melalui KelolaKegiatan.');
            }
            if (
                ! $k->buka_at || ! $k->tenggat_at || ! $k->buka_at->lt($k->tenggat_at)
                || $k->maks_berkas < 1 || $k->maks_berkas > 5 || $k->maks_ukuran_byte < 1048576 || $k->maks_ukuran_byte > 20971520
                || ! is_array($k->ekstensi_diizinkan) || count($k->ekstensi_diizinkan) < 1
                || array_diff($k->ekstensi_diizinkan, ['pdf', 'jpg', 'png']) !== []
                || count(array_unique($k->ekstensi_diizinkan)) !== count($k->ekstensi_diizinkan)
            ) {
                throw new LogicException('Jadwal atau batas jawaban tidak valid.');
            }
            $valid = match ($k->status) {
                self::DRAF => $k->terbit_at === null && $k->ditutup_at === null && $k->diarsipkan_at === null,
                self::TERBIT => $k->terbit_at !== null && $k->ditutup_at === null && $k->diarsipkan_at === null,
                self::DITUTUP => $k->terbit_at !== null && $k->ditutup_at !== null && $k->diarsipkan_at === null,
                self::ARSIP => $k->diarsipkan_at !== null,
            };
            if (! $valid) {
                throw new LogicException('Status dan waktu kegiatan tidak sesuai.');
            }
            if (! $k->exists) {
                if ($k->status !== self::DRAF || $k->revisi !== 1) {
                    throw new LogicException('Kegiatan baru harus draf, revisi satu.');
                }
                return;
            }
            $asal = $k->getRawOriginal('status');
            $transisi = [
                self::DRAF => [self::DRAF, self::TERBIT, self::ARSIP],
                self::TERBIT => [self::TERBIT, self::DITUTUP, self::ARSIP],
                self::DITUTUP => [self::DITUTUP, self::TERBIT, self::ARSIP],
                self::ARSIP => [self::DRAF, self::DITUTUP]
            ];
            if (
                $k->isDirty(['kelas_kuliah_id', 'pembuat_id', 'form_token', 'hash_permohonan'])
                || $k->revisi !== (int) $k->getRawOriginal('revisi') + 1
                || ! in_array($k->status, $transisi[$asal] ?? [], true)
            ) {
                throw new LogicException('Identitas tetap; revisi dan transisi harus sesuai.');
            }
            if ($k->getRawOriginal('terbit_at') !== null) {
                if ($k->isDirty([...self::ISI_TETAP, 'terbit_at']) || $k->status === self::DRAF) {
                    throw new LogicException('Isi dan aturan kegiatan dikunci sejak penerbitan pertama.');
                }
                if ($k->isDirty('tenggat_at') && ! $k->tenggat_at->gt(CarbonImmutable::parse($k->getRawOriginal('tenggat_at'), 'UTC'))) {
                    throw new LogicException('Tenggat hanya boleh diperpanjang.');
                }
            } elseif (
                ! ($asal === self::DRAF && $k->status === self::DRAF)
                && $k->isDirty([...self::ISI_TETAP, 'tenggat_at'])
            ) {
                throw new LogicException('Ubah isi melalui edit draf sebelum menerbitkan.');
            }
        });
        static::deleting(function (): never {
            throw new LogicException('Gunakan arsip; kegiatan tidak dihapus.');
        });
    }
    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }
    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id');
    }
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }
    public function lampiran(): HasMany
    {
        return $this->hasMany(KegiatanBerkas::class, 'kegiatan_id')->where('aktif', true);
    }
    public function semuaLampiran(): HasMany
    {
        return $this->hasMany(KegiatanBerkas::class, 'kegiatan_id');
    }
    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'kegiatan');
    }
    public function versiForm(): string
    {
        return hash_hmac('sha256', 'kegiatan:' . $this->id . ':' . $this->revisi, (string) config('app.key'));
    }
    // Ini hanya pemeriksaan status/waktu, BUKAN pengganti otorisasi peserta ketika pengumpulan dibuat.
    public function jendelaTerbuka(?CarbonImmutable $waktu = null): bool
    {
        $waktu ??= CarbonImmutable::now('UTC');
        return $this->status === self::TERBIT && $this->terbit_at !== null && $this->terbit_at->lte($waktu)
            && $this->buka_at !== null && $this->tenggat_at !== null && $this->buka_at->lte($waktu) && $waktu->lt($this->tenggat_at);
    }
    public function labelJadwal(): string
    {
        if ($this->status === self::DRAF) {
            return 'Draf';
        }
        if ($this->status === self::ARSIP) {
            return 'Arsip';
        }
        if ($this->status === self::DITUTUP) {
            return 'Ditutup oleh pengajar';
        }
        if (now('UTC')->lt($this->buka_at)) {
            return 'Belum mulai';
        }
        return $this->jendelaTerbuka() ? 'Dalam jadwal pengumpulan' : 'Tenggat telah lewat';
    }
    public function ringkasanAudit(): array
    {
        return [
            ...$this->only([
                'kelas_kuliah_id',
                'pertemuan_id',
                'pembuat_id',
                ...self::ISI_TETAP,
                'tenggat_at',
                'status',
                'terbit_at',
                'ditutup_at',
                'diarsipkan_at',
                'revisi'
            ]),
            'berkas_ids' => $this->lampiran()->orderBy('berkas_id')->pluck('berkas_id')->all()
        ];
    }

    public function pengumpulan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Pengumpulan::class, 'kegiatan_id');
    }
}
