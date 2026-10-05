<?php

namespace App\Http\Controllers;

use App\Models\DetailKrs;
use App\Services\AksesKelasDosen;
use App\Services\AksesPesertaKelasDosen;
use App\Services\RekapPresensiPesertaDosen;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PortalPesertaKelasDosenController extends Controller
{
    public function __construct(
        private readonly AksesKelasDosen $aksesKelas,
        private readonly AksesPesertaKelasDosen $aksesPeserta,
        private readonly RekapPresensiPesertaDosen $rekapPresensi,
    ) {
    }

    public function index(Request $request, int $kelas): View
    {
        $user = $request->user();

        abort_unless($user !== null, 401);
        abort_unless($this->aksesPeserta->masuk($user), 403);

        $kelasKuliah = $this->aksesKelas->temukan(
            $user,
            $kelas
        );

        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $pencarian = trim((string) ($filter['q'] ?? ''));

        $query = $this->aksesPeserta
            ->batasi(
                DetailKrs::query(),
                $user,
                $kelasKuliah
            )
            ->with([
                'krs.registrasiSemester.riwayatStudi.mahasiswa.user',
            ]);

        if ($pencarian !== '') {
            $query->whereHas(
                'krs.registrasiSemester.riwayatStudi.mahasiswa',
                function ($mahasiswa) use ($pencarian): void {
                    $mahasiswa->where(
                        function ($pencarianMahasiswa) use (
                            $pencarian
                        ): void {
                            $pencarianMahasiswa
                                ->where(
                                    'nim',
                                    'like',
                                    '%' . $pencarian . '%'
                                )
                                ->orWhereHas(
                                    'user',
                                    function ($akun) use (
                                        $pencarian
                                    ): void {
                                        $akun->where(
                                            'nama',
                                            'like',
                                            '%' . $pencarian . '%'
                                        );
                                    }
                                );
                        }
                    );
                }
            );
        }

        $peserta = $query
            ->orderBy('detail_krs.id')
            ->paginate(25)
            ->withQueryString();

        return view('portal.dosen.peserta.index', [
            'kelas' => $kelasKuliah,
            'peserta' => $peserta,
            'pencarian' => $pencarian,
            'jumlahRiwayat' => $this->aksesPeserta->jumlahRiwayat(
                $user,
                $kelasKuliah
            ),
            'jumlahAktif' => $this->aksesPeserta->jumlahAktif(
                $user,
                $kelasKuliah
            ),
        ]);
    }

    public function show(
        Request $request,
        int $kelas,
        int $peserta
    ): View {
        $user = $request->user();

        abort_unless($user !== null, 401);
        abort_unless($this->aksesPeserta->masuk($user), 403);

        $kelasKuliah = $this->aksesKelas->temukan(
            $user,
            $kelas
        );

        $detail = $this->aksesPeserta
            ->batasi(
                DetailKrs::query(),
                $user,
                $kelasKuliah
            )
            ->with([
                'krs.registrasiSemester.periodeAkademik',
                'krs.registrasiSemester.riwayatStudi.mahasiswa.user',
            ])
            ->whereKey($peserta)
            ->firstOrFail();

        $rekap = $this->rekapPresensi->ambil(
            $user,
            $kelasKuliah,
            $detail
        );

        return view('portal.dosen.peserta.show', [
            'kelas' => $kelasKuliah,
            'peserta' => $detail,
            'rekapPresensi' => $rekap,
        ]);
    }
}