<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;
use LogicException;

class Mahasiswa extends Model
{
    use HasFactory;

    public const JENIS_KELAMIN = [
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
    ];

    protected $table = 'mahasiswa';

    protected $fillable = [
        'nim',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'tanggal_lahir' => 'immutable_date',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $mahasiswa): void {
            if ($mahasiswa->isDirty('user_id')) {
                throw ValidationException::withMessages([
                    'user_id' => 'Akun pemilik mahasiswa tidak dapat diganti.',
                ]);
            }
        });

        static::deleting(function (self $mahasiswa): void {
            throw new LogicException(
                'Data mahasiswa tidak dapat dihapus. Kelola status akun atau riwayat studinya.'
            );
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function riwayatStudi(): HasMany
    {
        return $this->hasMany(RiwayatStudi::class, 'mahasiswa_id');
    }

    public function riwayatAktif(): HasOne
    {
        return $this->hasOne(RiwayatStudi::class, 'mahasiswa_id')
            ->where('status', 'aktif');
    }

    public function labelJenisKelamin(): string
    {
        return self::JENIS_KELAMIN[$this->jenis_kelamin] ?? 'Belum diisi';
    }

    /**
     * Digunakan untuk mendeteksi perubahan data sejak formulir dibuka.
     */
    public function versiForm(): string
    {
        $key = (string) config('app.key');

        if ($key === '') {
            throw new LogicException('APP_KEY belum dikonfigurasi.');
        }

        $atribut = $this->getRawOriginal();

        ksort($atribut);

        return hash_hmac(
            'sha256',
            json_encode($atribut, JSON_THROW_ON_ERROR),
            $key
        );
    }

    public function presensi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Presensi::class, 'mahasiswa_id');
    }

    public function tagihan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Tagihan::class, 'mahasiswa_id');
    }

    public function permohonanSurat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PermohonanSurat::class, 'mahasiswa_id');
    }
}
