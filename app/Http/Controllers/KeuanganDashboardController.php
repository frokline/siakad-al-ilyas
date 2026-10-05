<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Role;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class KeuanganDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user instanceof User,
            Response::HTTP_UNAUTHORIZED
        );

        $adminKeuanganAktif = User::query()
            ->whereKey($user->getAuthIdentifier())
            ->where('status', User::STATUS_AKTIF)
            ->whereHas(
                'roles',
                fn ($query) => $query->where(
                    'kode',
                    Role::ADMIN_KEUANGAN
                )
            )
            ->exists();

        abort_unless(
            $adminKeuanganAktif,
            Response::HTTP_FORBIDDEN
        );

        $tagihanTerbit = DB::table('tagihan')
            ->where('status', Tagihan::TERBIT);

        $pembayaranDiterima = DB::table('pembayaran')
            ->where('status', Pembayaran::DITERIMA);

        $ringkasan = [
            'jenis_biaya_aktif' => DB::table('jenis_biaya')
                ->where('aktif', true)
                ->count(),

            'tagihan_draf' => DB::table('tagihan')
                ->where('status', Tagihan::DRAF)
                ->count(),

            'tagihan_terbit' => (clone $tagihanTerbit)->count(),

            'tagihan_dibatalkan' => DB::table('tagihan')
                ->where('status', Tagihan::DIBATALKAN)
                ->count(),

            'nominal_tagihan_terbit' => (int) (clone $tagihanTerbit)
                ->sum('nominal'),

            'pembayaran_menunggu' => DB::table('pembayaran')
                ->where('status', Pembayaran::MENUNGGU)
                ->count(),

            'pembayaran_diterima' => (clone $pembayaranDiterima)
                ->count(),

            'pembayaran_ditolak' => DB::table('pembayaran')
                ->where('status', Pembayaran::DITOLAK)
                ->count(),

            'pembayaran_dibatalkan' => DB::table('pembayaran')
                ->where('status', Pembayaran::DIBATALKAN)
                ->count(),

            'nominal_pembayaran_diterima' =>
                (int) (clone $pembayaranDiterima)
                    ->sum('nominal_diajukan'),
        ];

        return view('keuangan.dashboard', [
            'user' => $user,
            'ringkasan' => $ringkasan,
        ]);
    }
}