<?php

namespace App\Services;

use App\Models\JadwalKuliah;
use App\Models\Pertemuan;
use App\Models\Rombel;
use App\Support\PolaJadwal;
use App\Support\WaktuPertemuan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

final class PemeriksaBenturanPertemuan
{
    /** Pemanggil memegang lock rombel, kelas, tim, jadwal, sesi, dan dosen terkait. */
    public function periksaRencana(Pertemuan $sesi, Rombel $rombel, int $dosenId): void
    {
        $this->wajibTransaksi();
        if ($sesi->status === Pertemuan::BATAL) {
            return;
        }
        $query = $this->querySesi($rombel->id, [$dosenId])
            ->where('s.mulai_rencana', '<', $sesi->selesai_rencana->format('Y-m-d H:i:s'))
            ->where('s.selesai_rencana', '>', $sesi->mulai_rencana->format('Y-m-d H:i:s'));
        if ($sesi->exists) {
            $query->where('s.id', '<>', $sesi->id);
        }
        $bentrok = $query->orderBy('s.id')->lockForUpdate()->first(['s.id', 's.nomor', 'k.kode']);
        if ($bentrok !== null) {
            $this->gagal('Benturan dengan pertemuan ' . $bentrok->nomor . ' kelas ' . $bentrok->kode . ' (#' . $bentrok->id . ').');
        }

        $pola = WaktuPertemuan::polaTanggal($sesi->mulai_rencana, $sesi->selesai_rencana, $this->zona());
        $polaLain = DB::table('jadwal_kuliah as j')
            ->join('kelas_kuliah as k', 'k.id', '=', 'j.kelas_kuliah_id')
            ->leftJoin('pengajar_kelas as p', function (JoinClause $join): void {
                $join->on('p.kelas_kuliah_id', '=', 'k.id')->where('p.aktif', true);
            })
            ->where('j.aktif', true)->where('j.kelas_kuliah_id', '<>', $sesi->kelas_kuliah_id)
            ->where('j.hari', $pola['hari'])
            ->where('j.berlaku_mulai', '<=', $pola['berlaku_mulai'])
            ->where('j.berlaku_selesai', '>=', $pola['berlaku_mulai'])
            ->where('j.jam_mulai', '<', $pola['jam_selesai'])
            ->where('j.jam_selesai', '>', $pola['jam_mulai'])
            ->where(function (Builder $query) use ($rombel, $dosenId): void {
                $query->where('k.rombel_id', $rombel->id)->orWhere('p.dosen_id', $dosenId);
            })->orderBy('j.id')->lockForUpdate()->get([
                'j.id',
                'j.hari',
                'j.jam_mulai',
                'j.jam_selesai',
                'j.berlaku_mulai',
                'j.berlaku_selesai',
                'k.kode',
            ])->unique('id');

        foreach ($polaLain as $lain) {
            if (PolaJadwal::benturanPertama($pola, (array) $lain) !== null) {
                $this->gagal('Benturan dengan pola jadwal #' . $lain->id . ' kelas ' . $lain->kode . '.');
            }
        }
    }

    /** Pemeriksaan balik ketika pola atau tim dosen pola ditambah/diubah. */
    public function periksaPola(JadwalKuliah $jadwal, Rombel $rombel, array $dosenIds): void
    {
        $this->wajibTransaksi();
        if (! $jadwal->aktif) {
            return;
        }
        $awal = CarbonImmutable::parse($jadwal->berlaku_mulai->toDateString(), $this->zona())->startOfDay()->utc();
        $akhir = CarbonImmutable::parse($jadwal->berlaku_selesai->toDateString(), $this->zona())->addDay()->startOfDay()->utc();
        $kandidat = $this->querySesi($rombel->id, $dosenIds)
            ->where('s.kelas_kuliah_id', '<>', $jadwal->kelas_kuliah_id)
            ->where('s.mulai_rencana', '<', $akhir->format('Y-m-d H:i:s'))
            ->where('s.selesai_rencana', '>', $awal->format('Y-m-d H:i:s'))
            ->orderBy('s.id')->lockForUpdate()->get([
                's.id',
                's.nomor',
                's.mulai_rencana',
                's.selesai_rencana',
                'k.kode',
            ]);

        foreach ($kandidat as $sesi) {
            $pola = WaktuPertemuan::polaTanggal(
                CarbonImmutable::parse($sesi->mulai_rencana, 'UTC'),
                CarbonImmutable::parse($sesi->selesai_rencana, 'UTC'),
                $this->zona()
            );
            if (PolaJadwal::benturanPertama($jadwal->pola(), $pola) !== null) {
                $this->gagal('Pola akan berbenturan dengan pertemuan ' . $sesi->nomor . ' kelas ' . $sesi->kode . ' (#' . $sesi->id . ').');
            }
        }
    }

    public function pastikanTidakSedangMengajar(Pertemuan $sesi, Rombel $rombel, int $dosenId): void
    {
        $this->wajibTransaksi();
        // Sesi yang melewati jam rencana harus ditutup, bukan dianggap selesai otomatis.
        $lain = $this->querySesi($rombel->id, [$dosenId])->where('s.status', Pertemuan::BERLANGSUNG)
            ->where('s.id', '<>', $sesi->id)->orderBy('s.id')->lockForUpdate()
            ->first(['s.id', 's.nomor', 'k.kode']);
        if ($lain !== null) {
            $this->gagal('Rombel atau dosen masih memiliki sesi berlangsung: ' . $lain->kode . ' pertemuan ' . $lain->nomor . '. Selesaikan sesi tersebut dahulu.');
        }
    }

    private function querySesi(int $rombelId, array $dosenIds): Builder
    {
        // Penugasan penanggung jawab tetap terhitung walaupun akun dosennya dinonaktifkan.
        return DB::table('pertemuan as s')
            ->join('kelas_kuliah as k', 'k.id', '=', 's.kelas_kuliah_id')
            ->join('pengajar_kelas as p', 'p.id', '=', 's.pengajar_kelas_id')
            ->where('s.status', '<>', Pertemuan::BATAL)
            ->where(function (Builder $query) use ($rombelId, $dosenIds): void {
                $query->where('k.rombel_id', $rombelId);
                if ($dosenIds !== []) {
                    $query->orWhereIn('p.dosen_id', $dosenIds);
                }
            });
    }

    private function zona(): string
    {
        return (string) config('siakad.timezone', 'Asia/Makassar');
    }

    private function wajibTransaksi(): void
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Pemeriksaan benturan wajib berada di dalam transaksi penyimpanan.');
        }
    }

    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['pertemuan' => $pesan]);
    }
}
