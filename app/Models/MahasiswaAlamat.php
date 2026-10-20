<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaAlamat extends Model
{
    protected $table = 'mahasiswa_alamat';

    protected $fillable = [
        'alamat_ktp',
        'rt_ktp',
        'rw_ktp',
        'dusun_ktp',
        'desa_kelurahan_ktp',
        'provinsi_ktp',
        'kota_kabupaten_ktp',
        'kecamatan_ktp',
        'kode_pos_ktp',
        'domisili_sama_dengan_ktp',
        'alamat_domisili',
        'rt_domisili',
        'rw_domisili',
        'dusun_domisili',
        'desa_kelurahan_domisili',
        'provinsi_domisili',
        'kota_kabupaten_domisili',
        'kecamatan_domisili',
        'kode_pos_domisili',
        'status_tinggal',
    ];

    protected function casts(): array
    {
        return [
            'domisili_sama_dengan_ktp' => 'boolean',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
