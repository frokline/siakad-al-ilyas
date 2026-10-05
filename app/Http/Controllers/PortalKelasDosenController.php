<?php

namespace App\Http\Controllers;

use App\Models\KelasKuliah;
use App\Services\AksesKelasDosen;
use App\Services\RingkasanPresensiKelasDosen;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class PortalKelasDosenController extends Controller
{
    public function __construct(
        private readonly AksesKelasDosen $aksesKelas,
        private readonly RingkasanPresensiKelasDosen $ringkasanPresensi,
    ) {
    }

    /**
     * Menampilkan semua kelas yang ditugaskan kepada dosen.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user !== null,
            Response::HTTP_UNAUTHORIZED
        );

        abort_unless(
            $this->aksesKelas->masuk($user),
            Response::HTTP_FORBIDDEN
        );

        $filter = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(array_keys(KelasKuliah::STATUS)),
            ],
        ]);

        $query = $this->aksesKelas
            ->batasi(KelasKuliah::query(), $user)
            ->with([
                'rombel.periodeAkademik',
                'pengajarKelas' => static function (
                    $penugasan
                ): void {
                    $penugasan
                        ->where('aktif', true)
                        ->orderBy('peran')
                        ->orderBy('id');
                },
                'pengajarKelas.dosen.user',
            ])
            ->withCount([
                'jadwalKuliah as jumlah_jadwal_aktif' =>
                    static function ($jadwal): void {
                        $jadwal->where('aktif', true);
                    },

                'pertemuan as jumlah_pertemuan',

                'pesertaAktif as jumlah_peserta',

                'kegiatan as jumlah_kegiatan',
            ]);

        if (! empty($filter['q'])) {
            $pencarian = trim($filter['q']);

            $query->where(
                static function (Builder $kelas) use (
                    $pencarian
                ): void {
                    $kelas
                        ->where(
                            'kelas_kuliah.kode',
                            'like',
                            '%' . $pencarian . '%'
                        )
                        ->orWhere(
                            'kelas_kuliah.nama_mk_snapshot',
                            'like',
                            '%' . $pencarian . '%'
                        );
                }
            );
        }

        if (! empty($filter['status'])) {
            $query->where(
                'kelas_kuliah.status',
                $filter['status']
            );
        }

        $kelas = $query
            ->orderByRaw(
                "CASE kelas_kuliah.status
                    WHEN 'aktif' THEN 1
                    WHEN 'persiapan' THEN 2
                    WHEN 'selesai' THEN 3
                    WHEN 'arsip' THEN 4
                    ELSE 5
                END"
            )
            ->orderBy('kelas_kuliah.nama_mk_snapshot')
            ->orderBy('kelas_kuliah.kode')
            ->paginate(15)
            ->withQueryString();

        return view('portal.dosen.kelas.index', [
            'kelas' => $kelas,
            'filter' => $filter,
            'pilihanStatus' => KelasKuliah::STATUS,
        ]);
    }

    /**
     * Menampilkan detail satu kelas milik dosen.
     */
    public function show(
        Request $request,
        int $kelas
    ): View {
        $user = $request->user();

        abort_unless(
            $user !== null,
            Response::HTTP_UNAUTHORIZED
        );

        abort_unless(
            $this->aksesKelas->masuk($user),
            Response::HTTP_FORBIDDEN
        );

        $kelasKuliah = $this->aksesKelas
            ->temukan($user, $kelas);

        $kelasKuliah->loadCount([
            'pesertaAktif as jumlah_peserta',
            'pertemuan as jumlah_pertemuan',
            'kegiatan as jumlah_kegiatan',
            'jadwalKuliah as jumlah_jadwal_aktif' =>
                static function ($jadwal): void {
                    $jadwal->where('aktif', true);
                },
        ]);

        $ringkasanPresensi = $this->ringkasanPresensi
            ->ambil(
                $user,
                $kelasKuliah
            );

        return view('portal.dosen.kelas.show', [
            'kelas' => $kelasKuliah,
            'labelStatus' =>
                KelasKuliah::STATUS[$kelasKuliah->status]
                ?? $kelasKuliah->status,
            'bolehMengelola' => $this->aksesKelas->kelola(
                $user,
                $kelasKuliah
            ),
            'ringkasanPresensi' => $ringkasanPresensi,
        ]);
    }
}