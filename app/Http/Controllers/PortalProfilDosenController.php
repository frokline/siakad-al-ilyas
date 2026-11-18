<?php

namespace App\Http\Controllers;

use App\Actions\PerbaruiKataSandiDosen;
use App\Actions\PerbaruiProfilDosen;
use App\Http\Requests\PortalKataSandiDosenRequest;
use App\Http\Requests\PortalProfilDosenRequest;
use App\Services\AksesProfilDosen;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PortalProfilDosenController extends Controller
{
    public function __construct(
        private readonly AksesProfilDosen $aksesProfil
    ) {}

    /**
     * Menampilkan informasi profil dosen.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user !== null,
            Response::HTTP_UNAUTHORIZED
        );

        abort_unless(
            $this->aksesProfil->masuk($user),
            Response::HTTP_FORBIDDEN
        );

        $dosen = $this->aksesProfil->profil($user);

        return view('portal.dosen.profil.show', [
            'dosen' => $dosen,
            'user' => $user,
        ]);
    }

    /**
     * Menampilkan formulir edit profil dosen.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user !== null,
            Response::HTTP_UNAUTHORIZED
        );

        abort_unless(
            $this->aksesProfil->masuk($user),
            Response::HTTP_FORBIDDEN
        );

        $dosen = $this->aksesProfil->profil($user);

        return view('portal.dosen.profil.edit', [
            'dosen' => $dosen,
            'user' => $user,
        ]);
    }

    /**
     * Menyimpan perubahan informasi kontak dosen.
     */
    public function update(
        PortalProfilDosenRequest $request,
        PerbaruiProfilDosen $perbaruiProfil
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user !== null,
            Response::HTTP_UNAUTHORIZED
        );

        $perbaruiProfil->jalankan(
            $user,
            $request->dataAman()
        );

        return redirect()
            ->route('portal.dosen.profil.show')
            ->with(
                'info',
                'Data pribadi berhasil diperbarui.'
            );
    }

    /**
     * Mengganti kata sandi akun dosen.
     */
    public function updatePassword(
        PortalKataSandiDosenRequest $request,
        PerbaruiKataSandiDosen $perbaruiKataSandi
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user !== null,
            Response::HTTP_UNAUTHORIZED
        );

        $perbaruiKataSandi->jalankan(
            $user,
            $request->dataAman()
        );

        return redirect()
            ->route('portal.dosen.profil.show')
            ->with(
                'info',
                'Kata sandi berhasil diperbarui.'
            );
    }
}