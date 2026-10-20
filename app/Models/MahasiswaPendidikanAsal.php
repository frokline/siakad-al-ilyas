<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaPendidikanAsal extends Model
{
    protected $table = 'mahasiswa_pendidikan_asal';

    protected $fillable = [
        'jenis_pendidikan_asal',
        'perguruan_tinggi',
        'program_studi_asal',
        'ipk',
        'sks_diakui',
        'nomor_ijazah',
        'nisn',
    ];

    protected function casts(): array
    {
        return [
            'ipk' => 'decimal:2',
            'sks_diakui' => 'integer',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
