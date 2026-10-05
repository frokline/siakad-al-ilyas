<?php

namespace App\Http\Controllers;

use App\Models\RegistrasiSemester;
use App\Services\AksesProfilMahasiswa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PortalMahasiswaController extends Controller
{
    public function __construct(
        private readonly AksesProfilMahasiswa $aksesProfil
    ) {
    }

    /**
     * Menampilkan beranda terpadu milik mahasiswa.
     */
    public function index(Request $request): View
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

        $registrasiAktif = $riwayatAktif
            ?->registrasiSemester
            ->first(
                static fn ($registrasi): bool =>
                    in_array(
                        $registrasi->status,
                        [
                            RegistrasiSemester::TERDAFTAR,
                            RegistrasiSemester::AKTIF,
                        ],
                        true
                    )
            );

        $jumlahRegistrasi = $mahasiswa->riwayatStudi
            ->sum(
                static fn ($riwayat): int =>
                    $riwayat->registrasiSemester->count()
            );

        return view('portal.mahasiswa.index', [
            'user' => $user,
            'mahasiswa' => $mahasiswa,
            'riwayatAktif' => $riwayatAktif,
            'registrasiAktif' => $registrasiAktif,
            'jumlahRiwayat' =>
                $mahasiswa->riwayatStudi->count(),
            'jumlahRegistrasi' => $jumlahRegistrasi,
        ]);
    }
}