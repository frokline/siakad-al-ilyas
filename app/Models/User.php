<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    protected $table = 'users';

    // Status dan kata sandi ditetapkan secara eksplisit oleh pengelola akun.
    protected $fillable = [
        'username',
        'email',
        'nama',
        'telepon',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $attributes = [
        'status' => self::STATUS_AKTIF,
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function isAktif(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
        )
            ->using(UserRole::class)
            ->withPivot('id')
            ->withTimestamps();
    }

    public function hasRole(string $kode): bool
    {
        if (! $this->exists) {
            return false;
        }

        return static::query()
            ->whereKey($this->getKey())
            ->aktif()
            ->whereHas(
                'roles',
                fn(Builder $query) => $query->where('roles.kode', $kode)
            )
            ->exists();
    }

    public function dosen(): HasOne
    {
        return $this->hasOne(Dosen::class, 'user_id');
    }

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class, 'user_id');
    }

    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\AuditLog::class, 'pelaku_id');
    }

    public function pengesahanKrs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Krs::class, 'disahkan_oleh');
    }

    public function penugasanMengajar(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\PengajarKelas::class,
            \App\Models\Dosen::class,
            'user_id',
            'dosen_id',
            'id',
            'id'
        );
    }

    public function presensiDicatat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Presensi::class, 'dicatat_oleh');
    }

    public function presensiDibuka(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PresensiPertemuan::class, 'dibuka_oleh');
    }

    public function presensiDitutup(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PresensiPertemuan::class, 'ditutup_oleh');
    }

    public function berkas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Berkas::class, 'diunggah_oleh');
    }

    public function materiDibuat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Materi::class, 'pembuat_id');
    }

    public function kegiatanDibuat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Kegiatan::class, 'pembuat_id');
    }

    public function pengumpulanJawaban(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Pengumpulan::class, 'pemilik_id');
    }

    public function jenisBiayaDibuat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\JenisBiaya::class, 'pembuat_id');
    }

    public function pembayaranDiunggah(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Pembayaran::class, 'pengunggah_id');
    }
    public function jenisSuratDibuat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\JenisSurat::class, 'pembuat_id');
    }

    public function permohonanSurat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PermohonanSurat::class, 'pemohon_id');
    }
    public function tindakanSurat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\RiwayatSurat::class, 'pelaku_id');
    }
    public function agendaAkademikDibuat(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\KalenderAkademik::class, 'pembuat_id');
    }

    public function notifikasiDiterima(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Notifikasi::class, 'penerima_id');
    }
}
