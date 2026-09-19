<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Krs extends Model
{
    public const DRAF = 'draf';
    public const DIAJUKAN = 'diajukan';
    public const DISAHKAN = 'disahkan';
    public const DIBATALKAN = 'dibatalkan';

    public const STATUS = [
        self::DRAF => 'Draf',
        self::DIAJUKAN => 'Diajukan',
        self::DISAHKAN => 'Disahkan',
        self::DIBATALKAN => 'Dibatalkan',
    ];

    public const OPERASI = [
        'buat' => 'Membuat draf',
        'ubah' => 'Mengubah catatan draf',
        'ajukan' => 'Mengajukan KRS',
        'kembalikan' => 'Mengembalikan ke draf',
        'sahkan' => 'Mengesahkan KRS',
        'revisi' => 'Membuka revisi',
        'pulihkan' => 'Memulihkan ke draf',
        'batalkan' => 'Membatalkan KRS',
    ];

    protected $table = 'krs';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'registrasi_semester_id' => 'integer',
            'disahkan_oleh' => 'integer',
            'versi' => 'integer',
            'diajukan_at' => 'immutable_datetime',
            'disahkan_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $krs): void {
            $valid = DB::transactionLevel() > 0
                && isset(self::STATUS[$krs->status])
                && $krs->registrasi_semester_id > 0
                && $krs->versi >= 1 && $krs->versi <= 4294967295;

            if (! $valid) {
                self::gagal('KRS harus disimpan melalui transaksi KelolaKrs.');
            }

            if (! $krs->exists) {
                if ($krs->status !== self::DRAF || $krs->versi !== 1) {
                    self::gagal('KRS baru harus berupa draf versi 1.');
                }
            } else {
                if (
                    $krs->isDirty('registrasi_semester_id')
                    || $krs->versi !== (int) $krs->getRawOriginal('versi') + 1
                ) {
                    self::gagal('Registrasi tetap dan versi KRS harus bertambah satu.');
                }

                $asal = $krs->getRawOriginal('status');
                $transisi = [
                    self::DRAF => [self::DRAF, self::DIAJUKAN, self::DIBATALKAN],
                    self::DIAJUKAN => [self::DRAF, self::DISAHKAN, self::DIBATALKAN],
                    self::DISAHKAN => [self::DRAF, self::DIBATALKAN],
                    self::DIBATALKAN => [self::DRAF],
                ];

                if (! in_array($krs->status, $transisi[$asal] ?? [], true)) {
                    self::gagal('Perubahan status KRS tidak diizinkan.');
                }
            }

            $diajukan = $krs->diajukan_at;
            $disahkan = $krs->disahkan_at;
            $pengesah = $krs->disahkan_oleh;
            $pasanganSah = ($disahkan === null && $pengesah === null)
                || ($disahkan !== null && $pengesah !== null && $diajukan !== null
                    && $disahkan->greaterThanOrEqualTo($diajukan));

            $metadataValid = $pasanganSah && match ($krs->status) {
                self::DRAF => $diajukan === null && $disahkan === null,
                self::DIAJUKAN => $diajukan !== null && $disahkan === null,
                self::DISAHKAN => $diajukan !== null && $disahkan !== null,
                self::DIBATALKAN => true,
                default => false,
            };

            if (! $metadataValid) {
                self::gagal('Waktu pengajuan dan pengesahan tidak sesuai status KRS.');
            }
        });

        static::deleting(function (): never {
            self::gagal('KRS tidak dihapus. Gunakan pembatalan agar riwayat terjaga.');
        });
    }

    public function registrasiSemester(): BelongsTo
    {
        return $this->belongsTo(RegistrasiSemester::class, 'registrasi_semester_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailKrs::class, 'krs_id');
    }

    public function pengesah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disahkan_oleh');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entitas_id')->where('entitas', 'krs');
    }

    public function operasiTersedia(): array
    {
        $operasi = match ($this->status) {
            self::DRAF => ['ajukan', 'batalkan'],
            self::DIAJUKAN => ['sahkan', 'kembalikan', 'batalkan'],
            self::DISAHKAN => ['revisi', 'batalkan'],
            self::DIBATALKAN => ['pulihkan'],
            default => [],
        };

        return array_intersect_key(self::OPERASI, array_flip($operasi));
    }

    public function versiForm(): string
    {
        $this->loadMissing(['registrasiSemester', 'details.kelasKuliah']);
        $atribut = static function (Model $model): array {
            $data = $model->getRawOriginal();
            ksort($data);
            return $data;
        };

        $data = [
            'krs' => $atribut($this),
            'registrasi' => $atribut($this->registrasiSemester),
            'details' => $this->details->sortBy('id')->map(
                fn(DetailKrs $detail): array => [
                    'detail' => $atribut($detail),
                    'kelas' => $atribut($detail->kelasKuliah),
                ]
            )->values()->all(),
        ];

        return hash_hmac('sha256', json_encode($data, JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    public function ringkasanAudit(): array
    {
        return [
            'krs_id' => $this->id,
            'registrasi_semester_id' => $this->registrasi_semester_id,
            'status' => $this->status,
            'versi' => $this->versi,
            'catatan' => $this->catatan,
            'diajukan_at_utc' => $this->diajukan_at?->toDateTimeString(),
            'disahkan_at_utc' => $this->disahkan_at?->toDateTimeString(),
            'disahkan_oleh' => $this->disahkan_oleh,
            'details' => $this->details->sortBy('id')->map(
                fn(DetailKrs $detail): array => [
                    'id' => $detail->id,
                    'kelas_kuliah_id' => $detail->kelas_kuliah_id,
                    'status' => $detail->status,
                    'aktif_at_utc' => $detail->aktif_at?->toDateTimeString(),
                    'batal_at_utc' => $detail->batal_at?->toDateTimeString(),
                ]
            )->values()->all(),
        ];
    }

    // Decimal satu angka dihitung sebagai integer persepuluhan, tanpa float.
    public function totalSks(): string
    {
        $this->loadMissing('details.kelasKuliah');
        $jumlah = $this->details->sum(function (DetailKrs $detail): int {
            [$utuh, $pecahan] = array_pad(explode('.', (string) $detail->kelasKuliah->sks_snapshot, 2), 2, '0');
            return ((int) $utuh * 10) + (int) str_pad($pecahan, 1, '0');
        });

        return intdiv($jumlah, 10) . '.' . ($jumlah % 10);
    }

    private static function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['krs' => $pesan]);
    }
}
