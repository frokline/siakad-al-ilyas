<?php

namespace App\Http\Controllers;

use App\Actions\PerbaruiProfilMahasiswa;
use App\Http\Requests\PortalProfilMahasiswaRequest;
use App\Services\AksesProfilMahasiswa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PortalProfilMahasiswaController extends Controller
{
    public function __construct(
        private readonly AksesProfilMahasiswa $aksesProfil
    ) {
    }

    /**
     * Menampilkan profil dan riwayat akademik mahasiswa.
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

        $mahasiswa = $this->aksesProfil->profil($user);

        $riwayatAktif = $mahasiswa->riwayatStudi
            ->firstWhere('status', 'aktif');

        $jumlahRegistrasi = $mahasiswa->riwayatStudi
            ->sum(
                static fn ($riwayat): int =>
                    $riwayat->registrasiSemester->count()
            );

        return view('portal.profil.show', [
            'mahasiswa' => $mahasiswa,
            'user' => $user,
            'riwayatAktif' => $riwayatAktif,
            'jumlahRegistrasi' => $jumlahRegistrasi,
        ]);
    }

    /**
     * Menampilkan formulir perubahan data pribadi.
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

        $mahasiswa = $this->aksesProfil->profil($user);

        return view('portal.profil.edit', [
            'mahasiswa' => $mahasiswa,
            'user' => $user,
        ]);
    }

    /**
     * Menyimpan nomor telepon dan alamat.
     */
    public function update(
        PortalProfilMahasiswaRequest $request,
        PerbaruiProfilMahasiswa $perbaruiProfil
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
            ->route('portal.profil.show')
            ->with(
                'info',
                'Data pribadi berhasil diperbarui.'
            );
    }
}