<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Models\Concerns\MenjagaArsipPeriodeKelas;

class PeriodeAkademik extends Model
{
    use HasFactory;

    public const JENIS = [
        'ganjil' => 'Ganjil',
        'genap' => 'Genap',
        'pendek' => 'Pendek',
    ];

    public const STATUS = [
        'persiapan' => 'Persiapan',
        'aktif' => 'Aktif',
        'arsip' => 'Arsip',
    ];

    protected $table = 'periode_akademik';

    protected $fillable = [
        'tahun_mulai',
        'jenis',
        'mulai',
        'selesai',
    ];

    protected $attributes = [
        'jenis' => 'ganjil',
        'status' => 'persiapan',
    ];

    protected function casts(): array
    {
        return [
            'tahun_mulai' => 'integer',
            'mulai' => 'immutable_date',
            'selesai' => 'immutable_date',
            'krs_mulai' => 'immutable_datetime',
            'krs_selesai' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (PeriodeAkademik $periode): void {
            $periode->kode = (int) $periode->tahun_mulai
                . '-'
                . strtoupper((string) $periode->jenis);
        });

        static::updating(function (PeriodeAkademik $periode): void {
            if (! $periode->isDirty(['tahun_mulai', 'jenis', 'mulai'])) {
                return;
            }

            $dipakai = RiwayatStudi::query()
                ->where(function ($query) use ($periode): void {
                    $query->where('periode_mulai_id', $periode->getKey())
                        ->orWhere('periode_akhir_id', $periode->getKey());
                })
                ->lockForUpdate()
                ->first(['id']);

            if ($dipakai !== null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'periode_akademik' =>
                    'Tahun, jenis, dan tanggal mulai periode tidak dapat diubah '
                        . 'karena sudah digunakan dalam riwayat studi.',
                ]);
            }
        });

        static::updating(function (PeriodeAkademik $periode): void {
            if (! $periode->isDirty(['tahun_mulai', 'jenis', 'mulai'])) {
                return;
            }

            $dipakaiRombel = Rombel::query()
                ->where('periode_akademik_id', $periode->getKey())
                ->lockForUpdate()
                ->first(['id']);

            if ($dipakaiRombel !== null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'periode_akademik' =>
                    'Tahun, jenis, dan tanggal mulai periode tidak dapat diubah '
                        . 'karena sudah digunakan oleh rombel.',
                ]);
            }
        });
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function tahunAjaran(): string
    {
        if ($this->tahun_mulai === null) {
            return '—';
        }

        return $this->tahun_mulai . '/' . ($this->tahun_mulai + 1);
    }

    public function isKrsOpen(?CarbonInterface $at = null): bool
    {
        if (
            $this->status !== 'aktif'
            || $this->krs_mulai === null
            || $this->krs_selesai === null
        ) {
            return false;
        }

        $now = $at ?? CarbonImmutable::now('UTC');

        return $now->greaterThanOrEqual($this->krs_mulai)
            && $now->lessThanOrEqual($this->krs_selesai);
    }

    public function rombel(): HasMany
    {
        return $this->hasMany(Rombel::class, 'periode_akademik_id');
    }

    public function registrasiSemester(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\RegistrasiSemester::class, 'periode_akademik_id');
    }

    public function krs(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Krs::class,
            \App\Models\RegistrasiSemester::class,
            'periode_akademik_id',
            'registrasi_semester_id',
            'id',
            'id'
        );
    }

    public function agendaAkademik(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\KalenderAkademik::class, 'periode_akademik_id');
    }
}
