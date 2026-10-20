<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaBiodataBerkas extends Model
{
    protected $table = 'mahasiswa_biodata_berkas';

    protected $fillable = [
        'mahasiswa_id',
        'berkas_id',
        'jenis_berkas',
        'aktif',
        'dilepas_at',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'dilepas_at' => 'datetime',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class);
    }
}
