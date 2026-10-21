<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\RiwayatStudi;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PortalKtmMahasiswaController extends Controller
{
    /** Menampilkan KTM milik mahasiswa yang sedang login. */
    public function show(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        $mahasiswa = Mahasiswa::query()
            ->where('user_id', $user->id)
            ->with([
                'riwayatStudi.kurikulum.programStudi',
                'riwayatStudi.periodeMulai',
            ])
            ->firstOrFail();

        $riwayatAktif = $mahasiswa->riwayatStudi
            ->firstWhere('status', RiwayatStudi::AKTIF);

        return view('portal.ktm.show', [
            'user' => $user,
            'mahasiswa' => $mahasiswa,
            'riwayatAktif' => $riwayatAktif,
            'programStudi' => $riwayatAktif?->kurikulum?->programStudi,
        ]);
    }
}
