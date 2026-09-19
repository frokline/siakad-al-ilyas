<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class SasaranPengumuman extends Model
{
    protected $table = 'sasaran_pengumuman';
    protected $guarded = ['*'];
    public $timestamps = false;
    protected function casts(): array
    {
        return ['pengumuman_id' => 'integer', 'program_studi_id' => 'integer', 'kelas_kuliah_id' => 'integer', 'role_id' => 'integer'];
    }
    public static function kunci(array $s): string
    {
        return hash('sha256', implode(':', [$s['lingkup'], $s['program_studi_id'] ?? 0, $s['kelas_kuliah_id'] ?? 0, $s['role_id'] ?? 0]));
    }
    protected static function booted(): void
    {
        $cek = function (self $s): void {
            if (
                $s->getConnection()->transactionLevel() < 1
                || Pengumuman::query()->whereKey($s->pengumuman_id)->lockForUpdate()->value('status') !== 'draf'
            ) {
                throw new LogicException('Sasaran hanya boleh diubah pada draf dalam transaksi.');
            }
        };
        static::saving(function (self $s) use ($cek): void {
            $cek($s);
            $valid = match ($s->lingkup) {
                'kampus' => $s->program_studi_id === null && $s->kelas_kuliah_id === null,
                'prodi' => $s->program_studi_id > 0 && $s->kelas_kuliah_id === null,
                'kelas' => $s->kelas_kuliah_id > 0 && $s->program_studi_id === null,
                default => false,
            };
            if (! $valid || $s->exists) {
                throw new LogicException('Bentuk sasaran salah. Ganti sasaran melalui layanan draf.');
            }
            $s->kunci_sasaran = self::kunci($s->getAttributes());
        });
        static::deleting($cek);
    }
    public function pengumuman(): BelongsTo
    {
        return $this->belongsTo(Pengumuman::class, 'pengumuman_id');
    }
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }
    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
