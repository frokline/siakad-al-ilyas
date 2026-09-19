<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\ValidationException;

class Role extends Model
{
    public const MAHASISWA = 'mahasiswa';
    public const DOSEN = 'dosen';
    public const ADMIN_AKADEMIK = 'admin_akademik';
    public const ADMIN_KEUANGAN = 'admin_keuangan';

    public const KODE_SISTEM = [
        self::MAHASISWA,
        self::DOSEN,
        self::ADMIN_AKADEMIK,
        self::ADMIN_KEUANGAN,
    ];

    protected $table = 'roles';

    protected $fillable = [
        'nama',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Role $role): void {
            if (! in_array($role->kode, self::KODE_SISTEM, true)) {
                throw ValidationException::withMessages([
                    'kode' => 'Kode peran tidak dikenali oleh sistem.',
                ]);
            }
        });

        static::updating(function (Role $role): void {
            if ($role->isDirty('kode')) {
                throw ValidationException::withMessages([
                    'kode' => 'Kode peran sistem tidak dapat diubah.',
                ]);
            }
        });

        static::deleting(function (Role $role): void {
            throw ValidationException::withMessages([
                'role' => 'Peran sistem tidak dapat dihapus.',
            ]);
        });
    }

    // Tabel penghubung user_roles dibuat pada tahap berikutnya.
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_roles',
            'role_id',
            'user_id',
        )
            ->using(UserRole::class)
            ->withPivot('id')
            ->withTimestamps();
    }

    public function sasaranPengumuman(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SasaranPengumuman::class, 'role_id');
    }
}
