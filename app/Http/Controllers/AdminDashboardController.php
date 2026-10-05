<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user instanceof User,
            Response::HTTP_UNAUTHORIZED
        );

        $adminAktif = User::query()
            ->whereKey($user->getAuthIdentifier())
            ->where('status', User::STATUS_AKTIF)
            ->whereHas(
                'roles',
                fn ($query) => $query->where(
                    'kode',
                    Role::ADMIN_AKADEMIK
                )
            )
            ->exists();

        abort_unless(
            $adminAktif,
            Response::HTTP_FORBIDDEN
        );

        $ringkasan = [
            'program_studi' => DB::table('program_studi')->count(),

            'periode_aktif' => DB::table('periode_akademik')
                ->where('status', 'aktif')
                ->count(),

            'kurikulum_aktif' => DB::table('kurikulum')
                ->where('status', 'aktif')
                ->count(),

            'mata_kuliah_aktif' => DB::table('mata_kuliah')
                ->where('aktif', true)
                ->count(),

            'mahasiswa' => DB::table('mahasiswa')->count(),

            'dosen_aktif' => DB::table('dosen')
                ->where('status', 'aktif')
                ->count(),

            'registrasi_aktif' => DB::table('registrasi_semester')
                ->where('status', 'aktif')
                ->count(),

            'kelas_aktif' => DB::table('kelas_kuliah')
                ->where('status', 'aktif')
                ->count(),

            'krs_diajukan' => DB::table('krs')
                ->where('status', 'diajukan')
                ->count(),

            'pertemuan_berlangsung' => DB::table('pertemuan')
                ->where('status', 'berlangsung')
                ->count(),
        ];

        return view('admin.dashboard', [
            'user' => $user,
            'ringkasan' => $ringkasan,
        ]);
    }
}