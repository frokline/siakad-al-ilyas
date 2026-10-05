<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Services\AksesPresensiMahasiswa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PortalPresensiMahasiswaController extends Controller
{
    public function __construct(
        private readonly AksesPresensiMahasiswa $aksesPresensi
    ) {
    }

    /**
     * Menampilkan daftar dan ringkasan presensi mahasiswa.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null, Response::HTTP_UNAUTHORIZED);
        abort_unless(
            $this->aksesPresensi->masuk($user),
            Response::HTTP_FORBIDDEN
        );

        $filter = $request->validate([
            'status' => [
                'nullable',
                'string',
                'in:belum_dicatat,hadir,izin,sakit,alpa',
            ],
            'kelas' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'tanggal_mulai' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'tanggal_selesai' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:tanggal_mulai',
            ],
        ]);

        $query = $this->aksesPresensi
            ->batasi(Presensi::query(), $user)
            ->with([
                'kelasKuliah:id,kode,nama_mk_snapshot,sks_snapshot',
                'daftar:id,pertemuan_id,kelas_kuliah_id,status',
                'daftar.pertemuan:id,nomor,topik,mulai_rencana,selesai_rencana,status',
            ]);

        if (! empty($filter['status'])) {
            $query->where('presensi.status', $filter['status']);
        }

        if (! empty($filter['kelas'])) {
            $query->where('presensi.kelas_kuliah_id', $filter['kelas']);
        }

        if (! empty($filter['tanggal_mulai'])) {
            $tanggalMulai = $filter['tanggal_mulai'];

            $query->whereHas(
                'daftar.pertemuan',
                static function ($pertemuan) use ($tanggalMulai): void {
                    $pertemuan->whereDate(
                        'mulai_rencana',
                        '>=',
                        $tanggalMulai
                    );
                }
            );
        }

        if (! empty($filter['tanggal_selesai'])) {
            $tanggalSelesai = $filter['tanggal_selesai'];

            $query->whereHas(
                'daftar.pertemuan',
                static function ($pertemuan) use ($tanggalSelesai): void {
                    $pertemuan->whereDate(
                        'mulai_rencana',
                        '<=',
                        $tanggalSelesai
                    );
                }
            );
        }

        $presensi = $query
            ->orderByDesc('presensi.id')
            ->paginate(15)
            ->withQueryString();

        $queryRekap = $this->aksesPresensi
            ->batasi(Presensi::query(), $user);

        $rekap = [
            'total' => (clone $queryRekap)->count(),
            'hadir' => (clone $queryRekap)
                ->where('presensi.status', 'hadir')
                ->count(),
            'izin' => (clone $queryRekap)
                ->where('presensi.status', 'izin')
                ->count(),
            'sakit' => (clone $queryRekap)
                ->where('presensi.status', 'sakit')
                ->count(),
            'alpa' => (clone $queryRekap)
                ->where('presensi.status', 'alpa')
                ->count(),
            'belum_dicatat' => (clone $queryRekap)
                ->where('presensi.status', 'belum_dicatat')
                ->count(),
        ];

        $kelas = $this->aksesPresensi
            ->batasi(Presensi::query(), $user)
            ->join(
                'kelas_kuliah',
                'kelas_kuliah.id',
                '=',
                'presensi.kelas_kuliah_id'
            )
            ->select([
                'kelas_kuliah.id',
                'kelas_kuliah.kode',
                'kelas_kuliah.nama_mk_snapshot',
            ])
            ->distinct()
            ->orderBy('kelas_kuliah.nama_mk_snapshot')
            ->orderBy('kelas_kuliah.kode')
            ->get();

        return view('portal.presensi.index', [
            'presensi' => $presensi,
            'rekap' => $rekap,
            'kelas' => $kelas,
            'filter' => $filter,
            'pilihanStatus' => Presensi::STATUS,
        ]);
    }

    /**
     * Menampilkan satu detail presensi milik mahasiswa.
     */
    public function show(Request $request, int $presensi): View
    {
        $user = $request->user();

        abort_unless($user !== null, Response::HTTP_UNAUTHORIZED);
        abort_unless(
            $this->aksesPresensi->masuk($user),
            Response::HTTP_FORBIDDEN
        );

        $record = $this->aksesPresensi->temukan($user, $presensi);

        return view('portal.presensi.show', [
            'presensi' => $record,
            'labelStatus' => Presensi::STATUS[$record->status]
                ?? $record->status,
        ]);
    }
}