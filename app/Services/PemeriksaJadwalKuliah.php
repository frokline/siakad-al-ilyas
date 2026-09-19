<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\JadwalKuliah;
use App\Models\KelasKuliah;
use App\Models\PengajarKelas;
use App\Models\Rombel;
use App\Support\PolaJadwal;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

final class PemeriksaJadwalKuliah
{
    /** Pemanggil telah mengunci periode, rombel, kelas, dan seluruh tim kelas. */
    public function kunciDosenKelas(int $kelasId): array
    {
        $this->wajibTransaksi();
        $ids = PengajarKelas::query()->where('kelas_kuliah_id', $kelasId)
            ->where('aktif', true)->orderBy('id')->lockForUpdate()
            ->get(['id', 'dosen_id'])->pluck('dosen_id')->unique()->sort()->values()->all();

        if ($ids !== []) {
            Dosen::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get(['id']);
        }
        return $ids;
    }

    /** Pemeriksaan dilakukan setelah lock rombel dan semua dosen terkait diperoleh. */
    public function periksa(JadwalKuliah $calon, Rombel $rombel, array $dosenIds): void
    {
        $this->wajibTransaksi();
        if (! $calon->aktif) {
            return;
        }
        $pola = $calon->pola();

        // JOIN + FOR UPDATE membaca keadaan terbaru, termasuk setelah menunggu lock.
        // Status kelas/periode/akun tidak dipakai untuk membebaskan pemesanan waktu.
        $query = DB::table('jadwal_kuliah as j')
            ->join('kelas_kuliah as k', 'k.id', '=', 'j.kelas_kuliah_id')
            ->leftJoin('pengajar_kelas as p', function (JoinClause $join): void {
                $join->on('p.kelas_kuliah_id', '=', 'k.id')->where('p.aktif', true);
            })
            ->where('j.aktif', true)->where('j.hari', $pola['hari'])
            ->where('j.jam_mulai', '<', $pola['jam_selesai'])
            ->where('j.jam_selesai', '>', $pola['jam_mulai'])
            ->where('j.berlaku_mulai', '<=', $pola['berlaku_selesai'])
            ->where('j.berlaku_selesai', '>=', $pola['berlaku_mulai'])
            ->where(function (Builder $benturan) use ($rombel, $dosenIds): void {
                $benturan->where('k.rombel_id', $rombel->id);
                if ($dosenIds !== []) {
                    $benturan->orWhereIn('p.dosen_id', $dosenIds);
                }
            });

        if ($calon->exists) {
            $query->where('j.id', '<>', $calon->id);
        }
        $kandidat = $query->orderBy('j.id')->lockForUpdate()->get([
            'j.id',
            'j.hari',
            'j.jam_mulai',
            'j.jam_selesai',
            'j.berlaku_mulai',
            'j.berlaku_selesai',
            'k.kode as kode_kelas',
            'k.rombel_id',
        ])->unique('id');

        foreach ($kandidat as $lain) {
            $tanggal = PolaJadwal::benturanPertama($pola, (array) $lain);
            if ($tanggal === null) {
                continue;
            }
            $jenis = (int) $lain->rombel_id === (int) $rombel->id ? 'rombel' : 'dosen';
            throw ValidationException::withMessages([
                'jadwal' => 'Benturan ' . $jenis . ' dengan jadwal #' . $lain->id
                    . ' kelas ' . $lain->kode_kelas . ' pada ' . $tanggal->format('d-m-Y')
                    . ' (' . substr($lain->jam_mulai, 0, 5) . '–' . substr($lain->jam_selesai, 0, 5) . ').',
            ]);
        }
    }

    /** Dipanggil observer ketika anggota tim baru ditambah atau diaktifkan ulang. */
    public function periksaPenugasan(PengajarKelas $penugasan): void
    {
        $this->wajibTransaksi();
        // SimpanPengajarKelas sudah memegang lock parent, tim, dan dosen ini.
        $kelas = KelasKuliah::query()->whereKey($penugasan->kelas_kuliah_id)
            ->lockForUpdate()->firstOrFail();
        $rombel = Rombel::query()->whereKey($kelas->rombel_id)->lockForUpdate()->firstOrFail();
        Dosen::query()->whereKey($penugasan->dosen_id)->lockForUpdate()->firstOrFail();

        $jadwal = JadwalKuliah::query()->where('kelas_kuliah_id', $kelas->id)
            ->aktif()->orderBy('id')->lockForUpdate()->get();
        foreach ($jadwal as $pola) {
            $this->periksa($pola, $rombel, [$penugasan->dosen_id]);
        }
    }

    private function wajibTransaksi(): void
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Pemeriksaan benturan harus berada dalam transaksi penyimpanan.');
        }
    }
}
