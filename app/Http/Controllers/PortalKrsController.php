<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use App\Models\User;
use App\Services\AksesKrsMahasiswa;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PortalKrsController extends Controller
{
    public function index(
        Request $request,
        AksesKrsMahasiswa $akses
    ): View {
        /** @var User $user */
        $user = $request->user();

        abort_unless(
            $akses->masuk($user),
            403,
            'Portal KRS hanya tersedia untuk mahasiswa aktif.'
        );

        $filter = $request->validate([
            'status' => [
                'nullable',
                Rule::in(array_keys(Krs::STATUS)),
            ],
            'periode_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'page' => [
                'nullable',
                'integer',
                'between:1,100000',
            ],
        ]);

        $query = $akses->batasi(
            Krs::query()->with($this->relasi()),
            $user
        );

        if (! empty($filter['status'])) {
            $query->where(
                'status',
                $filter['status']
            );
        }

        if (! empty($filter['periode_id'])) {
            $query->whereHas(
                'registrasiSemester',
                fn(Builder $registrasi): Builder => $registrasi
                    ->where(
                        'periode_akademik_id',
                        $filter['periode_id']
                    )
            );
        }

        $daftarKrs = $query
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $daftarPeriode = $akses->batasi(
            Krs::query(),
            $user
        )
            ->join(
                'registrasi_semester',
                'registrasi_semester.id',
                '=',
                'krs.registrasi_semester_id'
            )
            ->join(
                'periode_akademik',
                'periode_akademik.id',
                '=',
                'registrasi_semester.periode_akademik_id'
            )
            ->select([
                'periode_akademik.id',
                'periode_akademik.kode',
                'periode_akademik.mulai',
            ])
            ->distinct()
            ->orderByDesc('periode_akademik.mulai')
            ->get();

        return view('portal.krs.index', [
            'daftarKrs' => $daftarKrs,
            'daftarPeriode' => $daftarPeriode,
            'filter' => $filter,
        ]);
    }

    public function show(
        Request $request,
        Krs $krs,
        AksesKrsMahasiswa $akses
    ): View {
        /** @var User $user */
        $user = $request->user();

        // Menggunakan 404 agar identitas KRS milik orang lain
        // tidak dibocorkan kepada pengguna.
        abort_unless(
            $akses->lihat($user, $krs),
            404
        );

        $krs->load($this->relasi());

        return view('portal.krs.show', [
            'krs' => $krs,
            'registrasi' => $krs->registrasiSemester,
            'bolehCetak' => $akses->cetak($user, $krs),
        ]);
    }

    public function cetak(
        Request $request,
        Krs $krs,
        AksesKrsMahasiswa $akses
    ): View {
        /** @var User $user */
        $user = $request->user();

        abort_unless(
            $akses->lihat($user, $krs),
            404
        );

        abort_unless(
            $akses->cetak($user, $krs),
            409,
            'KRS hanya dapat dicetak setelah disahkan.'
        );

        $krs->load($this->relasi());

        return view('portal.krs.cetak', [
            'krs' => $krs,
            'registrasi' => $krs->registrasiSemester,
            'dicetakPada' => now('UTC'),
        ]);
    }

    private function relasi(): array
    {
        return [
            'registrasiSemester.riwayatStudi.mahasiswa.user',
            'registrasiSemester.riwayatStudi.kurikulum.programStudi',
            'registrasiSemester.periodeAkademik',
            'registrasiSemester.rombel.paketSemester',
            'details.kelasKuliah',
            'pengesah',
        ];
    }
}