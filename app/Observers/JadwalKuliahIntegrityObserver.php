<?php

namespace App\Observers;

use App\Models\PengajarKelas;
use App\Models\PeriodeAkademik;
use App\Services\PemeriksaJadwalKuliah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JadwalKuliahIntegrityObserver
{
    public function saving(Model $model): void
    {
        if (! $model instanceof PengajarKelas || ! $model->aktif) {
            return;
        }
        // Pergantian koordinator saja tidak menambah dosen dalam tim.
        if ($model->exists && (bool) $model->getRawOriginal('aktif')) {
            return;
        }
        app(PemeriksaJadwalKuliah::class)->periksaPenugasan($model);
    }

    public function updating(Model $model): void
    {
        if (! $model instanceof PeriodeAkademik || ! $model->isDirty(['mulai', 'selesai'])) {
            return;
        }
        if (DB::transactionLevel() < 1) {
            $this->gagal('Batas periode harus diubah melalui transaksi.');
        }
        $terkini = PeriodeAkademik::query()->whereKey($model->id)->lockForUpdate()->firstOrFail();
        foreach (['mulai', 'selesai'] as $kolom) {
            if ($terkini->getRawOriginal($kolom) !== $model->getRawOriginal($kolom)) {
                $this->gagal('Periode telah berubah. Muat ulang formulir periode.');
            }
        }
        $mulai = $model->mulai?->toDateString();
        $selesai = $model->selesai?->toDateString();
        if ($mulai === null || $selesai === null || $mulai > $selesai) {
            $this->gagal('Batas tanggal periode tidak valid.');
        }

        // Pola nonaktif juga dipertahankan sebagai riwayat dalam batas periodenya.
        $melampaui = DB::table('jadwal_kuliah as j')
            ->join('kelas_kuliah as k', 'k.id', '=', 'j.kelas_kuliah_id')
            ->join('rombel as r', 'r.id', '=', 'k.rombel_id')
            ->where('r.periode_akademik_id', $model->id)
            ->where(function (Builder $query) use ($mulai, $selesai): void {
                $query->where('j.berlaku_mulai', '<', $mulai)->orWhere('j.berlaku_selesai', '>', $selesai);
            })->orderBy('j.id')->lockForUpdate()->first(['j.id']);

        if ($melampaui !== null) {
            $this->gagal('Batas periode akan mengecualikan jadwal #' . $melampaui->id . '. Periksa jadwal terlebih dahulu.');
        }
    }

    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['jadwal' => $pesan]);
    }
}
