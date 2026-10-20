<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaKeluarga extends Model
{
    protected $table = 'mahasiswa_keluarga';

    protected $fillable = [
        'jenis_hubungan',
        'nama_lengkap',
        'nik',
        'tanggal_lahir',
        'status_hidup',
        'status_kekerabatan',
        'pendidikan_terakhir',
        'pekerjaan',
        'penghasilan',
        'alamat',
        'nomor_telepon',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'penghasilan' => 'integer',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
