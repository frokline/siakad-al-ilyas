<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaBiodata extends Model
{
    protected $table = 'mahasiswa_biodata';

    protected $fillable = [
        'mahasiswa_id',
        'agama',
        'suku',
        'berat_badan',
        'tinggi_badan',
        'golongan_darah',
        'transportasi',
        'kewarganegaraan',
        'nik',
        'nomor_kk',
        'npwp',
        'nomor_kps',
        'status_nikah',
        'ukuran_jas_almamater',
        'nomor_hp',
        'email_pribadi',
        'pekerjaan',
        'instansi_pekerjaan',
        'penghasilan',
        'nomor_rekening',
        'nama_rekening',
        'nama_bank',
    ];

    protected function casts(): array
    {
        return [
            'berat_badan' => 'integer',
            'tinggi_badan' => 'integer',
            'penghasilan' => 'integer',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
