<?php

namespace App\Models;

use App\Rules\TautanPertemuanAman;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Pertemuan extends Model
{
    public const TERJADWAL = 'terjadwal';
    public const BERLANGSUNG = 'berlangsung';
    public const SELESAI = 'selesai';
    public const BATAL = 'batal';
    public const STATUS = [
        self::TERJADWAL => 'Terjadwal',
        self::BERLANGSUNG => 'Berlangsung',
        self::SELESAI => 'Selesai',
        self::BATAL => 'Batal',
    ];
    public const BELUM_TUNTAS = [self::TERJADWAL, self::BERLANGSUNG];
    public const JENIS = ['kuliah' => 'Kuliah', 'uts' => 'UTS', 'uas' => 'UAS', 'pengganti' => 'Pengganti'];
    public const AKSI = [
        'buat' => 'Membuat pertemuan',
        'ubah' => 'Mengubah rencana',
        'mulai' => 'Memulai pertemuan',
        'selesai' => 'Menyelesaikan pertemuan',
        'batalkan' => 'Membatalkan pertemuan',
        'pulihkan' => 'Memulihkan pertemuan',
    ];
    public const KOLOM_RENCANA = [
        'jadwal_kuliah_id',
        'pengajar_kelas_id',
        'jenis',
        'topik',
        'rencana',
        'mulai_rencana',
        'selesai_rencana',
        'metode',
        'lokasi',
        'tautan_pertemuan',
        'jadwal_snapshot',
    ];

    protected $table = 'pertemuan';
    protected $guarded = ['*'];
    protected $hidden = ['tautan_pertemuan'];

    protected function casts(): array
    {
        return [
            'kelas_kuliah_id' => 'integer',
            'jadwal_kuliah_id' => 'integer',
            'pengajar_kelas_id' => 'integer',
            'nomor' => 'integer',
            'revisi' => 'integer',
            'tautan_pertemuan' => 'encrypted',
            'jadwal_snapshot' => 'array',
            'pengajar_snapshot' => 'array',
            'mulai_rencana' => 'immutable_datetime',
            'selesai_rencana' => 'immutable_datetime',
            'mulai_aktual' => 'immutable_datetime',
            'selesai_aktual' => 'immutable_datetime',
            'dibatalkan_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $sesi): void {
            if ($sesi->getConnection()->transactionLevel() < 1) {
                self::gagal('Simpan pertemuan melalui transaksi KelolaPertemuan.');
            }
            $sesi->pastikanValid();
            if (! $sesi->exists) {
                if ($sesi->status !== self::TERJADWAL || $sesi->revisi !== 1) {
                    self::gagal('Pertemuan baru harus terjadwal pada revisi pertama.');
                }
                return;
            }
            if (
                $sesi->isDirty(['kelas_kuliah_id', 'nomor'])
                || $sesi->revisi !== (int) $sesi->getRawOriginal('revisi') + 1
            ) {
                self::gagal('Kelas/nomor tetap; revisi harus bertambah satu.');
            }
            $asal = (string) $sesi->getRawOriginal('status');
            $transisi = [
                self::TERJADWAL => [self::TERJADWAL, self::BERLANGSUNG, self::BATAL],
                self::BERLANGSUNG => [self::SELESAI],
                self::BATAL => [self::TERJADWAL],
                self::SELESAI => [],
            ];
            if (! in_array($sesi->status, $transisi[$asal] ?? [], true)) {
                self::gagal('Perubahan status tidak diizinkan.');
            }
            $editRencana = $asal === self::TERJADWAL && $sesi->status === self::TERJADWAL;
            if (! $editRencana && $sesi->isDirty(self::KOLOM_RENCANA)) {
                self::gagal('Rencana tidak boleh ikut berubah saat menjalankan tindakan status.');
            }
            if ($editRencana && ! $sesi->isDirty(array_merge(self::KOLOM_RENCANA, ['pengajar_snapshot']))) {
                self::gagal('Tidak ada perubahan rencana.');
            }
            if ($sesi->getRawOriginal('mulai_aktual') !== null && $sesi->isDirty('mulai_aktual')) {
                self::gagal('Waktu mulai aktual yang sudah dicatat tidak dapat diganti.');
            }
            if (
                $sesi->isDirty('pengajar_snapshot')
                && ! $editRencana && ! ($asal === self::TERJADWAL && $sesi->status === self::BERLANGSUNG)
            ) {
                self::gagal('Identitas pengajar hanya diperbarui saat mengubah rencana atau memulai sesi.');
            }
        });

        static::deleting(function (): never {
            self::gagal('Pertemuan tidak dihapus. Batalkan sesi yang belum dimulai.');
        });
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    public function jadwalKuliah(): BelongsTo
    {
        return $this->belongsTo(JadwalKuliah::class, 'jadwal_kuliah_id');
    }

    public function pengajarKelas(): BelongsTo
    {
        return $this->belongsTo(PengajarKelas::class, 'pengajar_kelas_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'pertemuan');
    }

    public function scopeBelumTuntas(Builder $query): Builder
    {
        return $query->whereIn('pertemuan.status', self::BELUM_TUNTAS);
    }

    public function konteksTerbuka(): bool
    {
        return in_array($this->kelasKuliah->status, KelasKuliah::BELUM_TUNTAS, true)
            && in_array($this->kelasKuliah->rombel->periodeAkademik->status, Rombel::STATUS_PERIODE_TERBUKA, true);
    }

    public function dapatDiubah(): bool
    {
        return $this->status === self::TERJADWAL && $this->konteksTerbuka();
    }

    public function memilikiTautan(): bool
    {
        return ($this->getAttributes()['tautan_pertemuan'] ?? null) !== null;
    }

    public function pastikanValid(): void
    {
        Validator::make([
            'nomor' => $this->nomor,
            'jenis' => $this->jenis,
            'topik' => $this->topik,
            'rencana' => $this->rencana,
            'realisasi' => $this->realisasi,
            'metode' => $this->metode,
            'lokasi' => $this->lokasi,
            'tautan_pertemuan' => $this->tautan_pertemuan,
        ], [
            'nomor' => ['required', 'integer', 'between:1,65535'],
            'jenis' => ['required', Rule::in(array_keys(self::JENIS))],
            'topik' => ['required', 'string', 'max:200'],
            'rencana' => ['nullable', 'string', 'max:10000'],
            'realisasi' => ['nullable', 'string', 'max:20000'],
            'metode' => ['required', Rule::in(array_keys(JadwalKuliah::METODE))],
            'lokasi' => ['nullable', 'string', 'max:150', 'required_if:metode,luring,campuran', 'prohibited_if:metode,daring'],
            'tautan_pertemuan' => ['nullable', 'string', 'max:2048', 'prohibited_if:metode,luring', new TautanPertemuanAman()],
        ])->validate();

        if (
            $this->kelas_kuliah_id < 1 || $this->pengajar_kelas_id < 1
            || $this->revisi < 1 || $this->revisi > 4294967295
            || $this->mulai_rencana === null || $this->selesai_rencana === null
            || ! $this->mulai_rencana->lt($this->selesai_rencana)
            || ($this->jadwal_kuliah_id === null) !== ($this->jadwal_snapshot === null)
            || ! is_array($this->pengajar_snapshot)
            || (int) ($this->pengajar_snapshot['pengajar_kelas_id'] ?? 0) !== $this->pengajar_kelas_id
        ) {
            self::gagal('Identitas, rentang waktu, salinan sumber, atau revisi tidak valid.');
        }
        $valid = match ($this->status) {
            self::TERJADWAL => $this->mulai_aktual === null && $this->selesai_aktual === null
                && $this->dibatalkan_at === null && $this->realisasi === null,
            self::BERLANGSUNG => $this->mulai_aktual !== null && $this->selesai_aktual === null
                && $this->dibatalkan_at === null && $this->realisasi === null,
            self::SELESAI => $this->mulai_aktual !== null && $this->selesai_aktual !== null
                && $this->selesai_aktual->gt($this->mulai_aktual) && $this->dibatalkan_at === null
                && is_string($this->realisasi) && mb_strlen(trim($this->realisasi)) >= 10,
            self::BATAL => $this->mulai_aktual === null && $this->selesai_aktual === null
                && $this->dibatalkan_at !== null && $this->realisasi === null,
            default => false,
        };
        if (! $valid) {
            self::gagal('Status tidak sesuai dengan waktu pelaksanaan dan catatan realisasi.');
        }
    }

    public function ringkasanAudit(): array
    {
        $hasil = [
            'kelas_kuliah_id' => $this->kelas_kuliah_id,
            'nomor' => $this->nomor,
            'jadwal_kuliah_id' => $this->jadwal_kuliah_id,
            'pengajar_kelas_id' => $this->pengajar_kelas_id,
            'jenis' => $this->jenis,
            'topik' => $this->topik,
            'rencana' => $this->rencana,
            'realisasi' => $this->realisasi,
            'metode' => $this->metode,
            'lokasi' => $this->lokasi,
            'tautan_tersedia' => $this->memilikiTautan(),
            'status' => $this->status,
            'revisi' => $this->revisi,
            'jadwal_snapshot' => $this->jadwal_snapshot,
            'pengajar_snapshot' => $this->pengajar_snapshot,
        ];
        foreach (['mulai_rencana', 'selesai_rencana', 'mulai_aktual', 'selesai_aktual', 'dibatalkan_at'] as $kolom) {
            $hasil[$kolom] = $this->{$kolom}?->format('Y-m-d H:i:s');
        }
        return $hasil;
    }

    private static function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['pertemuan' => $pesan]);
    }

    public function presensiPertemuan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\PresensiPertemuan::class, 'pertemuan_id');
    }

    public function presensi(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Presensi::class,
            \App\Models\PresensiPertemuan::class,
            'pertemuan_id',
            'presensi_pertemuan_id',
            'id',
            'id'
        );
    }

    public function kegiatan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Kegiatan::class, 'pertemuan_id');
    }
}
