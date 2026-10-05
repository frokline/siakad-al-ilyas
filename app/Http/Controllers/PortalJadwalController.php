<?php

namespace App\Http\Controllers;

use App\Models\JadwalKuliah;
use App\Models\User;
use App\Services\AksesJadwalMahasiswa;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PortalJadwalController extends Controller
{
    public function index(
        Request $request,
        AksesJadwalMahasiswa $akses
    ): View {
        /** @var User $user */
        $user = $request->user();

        abort_unless(
            $akses->masuk($user),
            403,
            'Portal jadwal hanya tersedia untuk mahasiswa aktif.'
        );

        $filter = $request->validate([
            'hari' => [
                'nullable',
                'integer',
                Rule::in(array_keys(JadwalKuliah::HARI)),
            ],
            'metode' => [
                'nullable',
                Rule::in(array_keys(JadwalKuliah::METODE)),
            ],
            'periode_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $query = $akses->batasi(
            JadwalKuliah::query()->with([
                'kelasKuliah.rombel.periodeAkademik',
                'kelasKuliah.pengajarAktif.dosen.user',
            ]),
            $user
        );

        if (! empty($filter['hari'])) {
            $query->where(
                'jadwal_kuliah.hari',
                $filter['hari']
            );
        }

        if (! empty($filter['metode'])) {
            $query->where(
                'jadwal_kuliah.metode',
                $filter['metode']
            );
        }

        if (! empty($filter['periode_id'])) {
            $query->whereHas(
                'kelasKuliah.rombel',
                fn(Builder $rombel): Builder => $rombel->where(
                    'periode_akademik_id',
                    $filter['periode_id']
                )
            );
        }

        $daftarJadwal = $query
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->orderBy('id')
            ->get();

        $daftarPeriode = $akses->batasi(
            JadwalKuliah::query(),
            $user
        )
            ->join(
                'kelas_kuliah',
                'kelas_kuliah.id',
                '=',
                'jadwal_kuliah.kelas_kuliah_id'
            )
            ->join(
                'rombel',
                'rombel.id',
                '=',
                'kelas_kuliah.rombel_id'
            )
            ->join(
                'periode_akademik',
                'periode_akademik.id',
                '=',
                'rombel.periode_akademik_id'
            )
            ->select([
                'periode_akademik.id',
                'periode_akademik.kode',
                'periode_akademik.mulai',
            ])
            ->distinct()
            ->orderByDesc('periode_akademik.mulai')
            ->get();

        return view('portal.jadwal.index', [
            'daftarJadwal' => $daftarJadwal,
            'daftarPeriode' => $daftarPeriode,
            'filter' => $filter,
        ]);
    }

    public function show(
        Request $request,
        JadwalKuliah $jadwalKuliah,
        AksesJadwalMahasiswa $akses
    ): View {
        /** @var User $user */
        $user = $request->user();

        // Jadwal kelas lain disembunyikan menggunakan respons 404.
        abort_unless(
            $akses->lihat($user, $jadwalKuliah),
            404
        );

        $jadwalKuliah->load([
            'kelasKuliah.rombel.periodeAkademik',
            'kelasKuliah.pengajarAktif.dosen.user',
        ]);

        return view('portal.jadwal.show', [
            'jadwal' => $jadwalKuliah,
            'kelas' => $jadwalKuliah->kelasKuliah,
        ]);
    }
}