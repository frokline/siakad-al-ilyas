<?php

namespace App\Http\Controllers;

use App\Models\RegistrasiSemester;
use App\Services\AksesProfilMahasiswa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class PortalMahasiswaController extends Controller
{
    public function __construct(
        private readonly AksesProfilMahasiswa $aksesProfil
    ) {}

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
                static fn($registrasi): bool =>
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
                static fn($riwayat): int =>
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


    /**
     * Memperbarui kata sandi akun mahasiswa yang sedang login.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $user !== null,
            Response::HTTP_UNAUTHORIZED
        );

        // Pastikan akun yang mengakses fitur ini adalah mahasiswa.
        abort_unless(
            $this->aksesProfil->masuk($user),
            Response::HTTP_FORBIDDEN
        );

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:current_password',
            ],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
        ]);

        // Periksa kata sandi saat ini sebelum mengubahnya.
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini tidak benar.',
            ]);
        }

        // Simpan kata sandi baru dalam bentuk hash.
        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return redirect()
            ->route('portal.profil.show')
            ->with('info', 'Kata sandi berhasil diperbarui.');
    }
}
