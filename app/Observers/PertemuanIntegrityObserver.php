<?php

namespace App\Observers;

use App\Models\JadwalKuliah;
use App\Models\KelasKuliah;
use App\Models\PengajarKelas;
use App\Models\PeriodeAkademik;
use App\Models\Pertemuan;
use App\Models\Rombel;
use App\Services\PemeriksaBenturanPertemuan;
use App\Services\PemeriksaJadwalKuliah;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PertemuanIntegrityObserver
{
    public function saving(Model $model): void
    {
        if (
            $model instanceof JadwalKuliah && $model->aktif
            && (! $model->exists || $model->isDirty(['aktif', 'hari', 'jam_mulai', 'jam_selesai', 'berlaku_mulai', 'berlaku_selesai']))
        ) {
            $this->wajibTransaksi();
            $kelas = KelasKuliah::query()->whereKey($model->kelas_kuliah_id)->lockForUpdate()->firstOrFail();
            $rombel = Rombel::query()->whereKey($kelas->rombel_id)->lockForUpdate()->firstOrFail();
            $ids = app(PemeriksaJadwalKuliah::class)->kunciDosenKelas($kelas->id);
            app(PemeriksaBenturanPertemuan::class)->periksaPola($model, $rombel, $ids);
        }

        if (
            $model instanceof PengajarKelas && $model->aktif
            && (! $model->exists || ! (bool) $model->getRawOriginal('aktif'))
        ) {
            $this->wajibTransaksi();
            // SimpanPengajarKelas sudah mengunci dosen sasaran sebelum save().
            $kelas = KelasKuliah::query()->whereKey($model->kelas_kuliah_id)->lockForUpdate()->firstOrFail();
            $rombel = Rombel::query()->whereKey($kelas->rombel_id)->lockForUpdate()->firstOrFail();
            $pola = JadwalKuliah::query()->where('kelas_kuliah_id', $kelas->id)
                ->aktif()->orderBy('id')->lockForUpdate()->get();
            foreach ($pola as $jadwal) {
                app(PemeriksaBenturanPertemuan::class)->periksaPola($jadwal, $rombel, [$model->dosen_id]);
            }
        }
    }

    public function updating(Model $model): void
    {
        if ($model instanceof PengajarKelas && $model->isDirty('aktif') && ! $model->aktif) {
            $this->wajibTransaksi();
            $sesi = Pertemuan::query()->where('pengajar_kelas_id', $model->id)
                ->belumTuntas()->orderBy('id')->lockForUpdate()->first(['id']);
            if ($sesi !== null) {
                $this->gagal('Penugasan masih bertanggung jawab atas pertemuan #' . $sesi->id . '. Ganti penanggung jawab sesi terjadwal, batalkan, atau selesaikan sesi berlangsung terlebih dahulu.');
            }
        }

        if (
            $model instanceof KelasKuliah && $model->isDirty('status')
            && in_array($model->status, [KelasKuliah::SELESAI, KelasKuliah::ARSIP], true)
        ) {
            $this->wajibTransaksi();
            $sesi = Pertemuan::query()->where('kelas_kuliah_id', $model->id)
                ->belumTuntas()->orderBy('id')->lockForUpdate()->first(['id']);
            if ($sesi !== null) {
                $this->gagal('Kelas masih memiliki pertemuan #' . $sesi->id . ' yang belum dituntaskan.');
            }
        }

        if (
            $model instanceof PeriodeAkademik
            && ($model->isDirty(['mulai', 'selesai']) || ($model->isDirty('status') && $model->status === 'arsip'))
        ) {
            $this->wajibTransaksi();
            $arsip = $model->isDirty('status') && $model->status === 'arsip';
            $zona = (string) config('siakad.timezone', 'Asia/Makassar');
            $awal = CarbonImmutable::parse($model->mulai->toDateString(), $zona)->startOfDay()->utc();
            $akhir = CarbonImmutable::parse($model->selesai->toDateString(), $zona)->addDay()->startOfDay()->utc();
            $sesi = DB::table('pertemuan as s')
                ->join('kelas_kuliah as k', 'k.id', '=', 's.kelas_kuliah_id')
                ->join('rombel as r', 'r.id', '=', 'k.rombel_id')
                ->where('r.periode_akademik_id', $model->id)
                ->where(function (Builder $query) use ($awal, $akhir, $arsip): void {
                    $query->where('s.mulai_rencana', '<', $awal->format('Y-m-d H:i:s'))
                        ->orWhere('s.selesai_rencana', '>', $akhir->format('Y-m-d H:i:s'));
                    if ($arsip) {
                        $query->orWhereIn('s.status', Pertemuan::BELUM_TUNTAS);
                    }
                })->orderBy('s.id')->lockForUpdate()->first(['s.id']);
            if ($sesi !== null) {
                $this->gagal('Perubahan periode tidak sesuai dengan tanggal/status pertemuan #' . $sesi->id . '.');
            }
        }
    }

    private function wajibTransaksi(): void
    {
        if (DB::transactionLevel() < 1) {
            $this->gagal('Perubahan yang memengaruhi pertemuan wajib menggunakan transaksi.');
        }
    }

    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['pertemuan' => $pesan]);
    }
}
