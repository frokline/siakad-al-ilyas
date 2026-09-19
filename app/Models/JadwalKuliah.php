<?php

namespace App\Models;

use App\Rules\TautanPertemuanAman;
use App\Support\PolaJadwal;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JadwalKuliah extends Model
{
    public const HARI = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];
    public const METODE = ['daring' => 'Daring', 'luring' => 'Luring', 'campuran' => 'Campuran'];
    public const AKSI_AUDIT = [
        'buat' => 'Membuat jadwal',
        'ubah' => 'Mengubah jadwal',
        'aktifkan' => 'Mengaktifkan jadwal',
        'nonaktifkan' => 'Menonaktifkan jadwal',
    ];
    public const KOLOM_POLA = [
        'hari',
        'jam_mulai',
        'jam_selesai',
        'berlaku_mulai',
        'berlaku_selesai',
        'metode',
        'lokasi',
        'tautan_pertemuan',
        'aktif',
    ];

    protected $table = 'jadwal_kuliah';
    protected $guarded = ['*'];
    protected $hidden = ['tautan_pertemuan'];

    protected function casts(): array
    {
        return [
            'kelas_kuliah_id' => 'integer',
            'hari' => 'integer',
            'berlaku_mulai' => 'immutable_date',
            'berlaku_selesai' => 'immutable_date',
            'tautan_pertemuan' => 'encrypted',
            'aktif' => 'boolean',
            'revisi' => 'integer',
            'dinonaktifkan_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $jadwal): void {
            if ($jadwal->getConnection()->transactionLevel() < 1) {
                self::gagal('Simpan jadwal melalui transaksi SimpanJadwalKuliah.');
            }
            $jadwal->pastikanPolaValid();

            if (
                $jadwal->revisi < 1 || $jadwal->revisi > 4294967295
                || $jadwal->kelas_kuliah_id < 1
                || ($jadwal->aktif !== ($jadwal->dinonaktifkan_at === null))
            ) {
                self::gagal('Identitas, revisi, atau waktu penonaktifan tidak valid.');
            }
            if (! $jadwal->exists) {
                if (! $jadwal->aktif || $jadwal->revisi !== 1) {
                    self::gagal('Jadwal baru harus aktif pada revisi pertama.');
                }
                return;
            }
            if (
                $jadwal->isDirty('kelas_kuliah_id')
                || $jadwal->revisi !== (int) $jadwal->getRawOriginal('revisi') + 1
            ) {
                self::gagal('Kelas tidak boleh dipindahkan; revisi harus bertambah satu.');
            }
            if (! $jadwal->isDirty(self::KOLOM_POLA)) {
                self::gagal('Tidak ada perubahan jadwal untuk disimpan.');
            }
            if (
                ! $jadwal->aktif && ! (bool) $jadwal->getRawOriginal('aktif')
                && $jadwal->isDirty('dinonaktifkan_at')
            ) {
                self::gagal('Waktu penonaktifan yang masih berlaku tidak boleh diubah.');
            }
        });

        static::deleting(function (): never {
            self::gagal('Jadwal tidak dihapus. Gunakan status nonaktif.');
        });
    }

    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'jadwal_kuliah');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('jadwal_kuliah.aktif', true);
    }

    public function dapatDiubah(): bool
    {
        return in_array($this->kelasKuliah->status, KelasKuliah::BELUM_TUNTAS, true)
            && in_array($this->kelasKuliah->rombel->periodeAkademik->status, Rombel::STATUS_PERIODE_TERBUKA, true);
    }

    public function memilikiTautan(): bool
    {
        // Memeriksa keberadaan ciphertext; tidak perlu mendekripsi untuk daftar/audit.
        return ($this->getAttributes()['tautan_pertemuan'] ?? null) !== null;
    }

    public function pola(): array
    {
        return [
            'hari' => $this->hari,
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'berlaku_mulai' => $this->berlaku_mulai->toDateString(),
            'berlaku_selesai' => $this->berlaku_selesai->toDateString(),
        ];
    }

    public function tanggalPertama(): ?CarbonImmutable
    {
        return PolaJadwal::tanggalPertama(
            $this->hari,
            $this->berlaku_mulai->toDateString(),
            $this->berlaku_selesai->toDateString()
        );
    }

    public function pastikanPolaValid(): void
    {
        Validator::make([
            'hari' => $this->hari,
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'berlaku_mulai' => $this->berlaku_mulai?->toDateString(),
            'berlaku_selesai' => $this->berlaku_selesai?->toDateString(),
            'metode' => $this->metode,
            'lokasi' => $this->lokasi,
            'tautan_pertemuan' => $this->tautan_pertemuan,
        ], [
            'hari' => ['required', 'integer', 'between:1,7'],
            'jam_mulai' => ['required', 'regex:/\A(?:[01][0-9]|2[0-3]):[0-5][0-9]:00\z/'],
            'jam_selesai' => ['required', 'regex:/\A(?:[01][0-9]|2[0-3]):[0-5][0-9]:00\z/', 'after:jam_mulai'],
            'berlaku_mulai' => ['required', 'date_format:Y-m-d'],
            'berlaku_selesai' => ['required', 'date_format:Y-m-d', 'after_or_equal:berlaku_mulai'],
            'metode' => ['required', Rule::in(array_keys(self::METODE))],
            'lokasi' => ['nullable', 'string', 'max:150', 'required_if:metode,luring,campuran', 'prohibited_if:metode,daring'],
            'tautan_pertemuan' => ['nullable', 'string', 'max:2048', 'prohibited_if:metode,luring', new TautanPertemuanAman()],
        ])->validate();

        if ($this->tanggalPertama() === null) {
            throw ValidationException::withMessages([
                'berlaku_selesai' => 'Rentang tanggal tidak memuat hari yang dipilih.',
            ]);
        }
    }

    public function ringkasanAudit(): array
    {
        return array_merge($this->pola(), [
            'kelas_kuliah_id' => $this->kelas_kuliah_id,
            'metode' => $this->metode,
            'lokasi' => $this->lokasi,
            'tautan_tersedia' => $this->memilikiTautan(),
            'aktif' => $this->aktif,
            'revisi' => $this->revisi,
            'dinonaktifkan_at' => $this->dinonaktifkan_at?->format('Y-m-d H:i:s'),
        ]);
    }

    private static function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['jadwal' => $pesan]);
    }

    public function pertemuan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Pertemuan::class, 'jadwal_kuliah_id');
    }
}
