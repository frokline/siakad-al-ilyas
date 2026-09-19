<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AksesPresensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PresensiSessionController extends Controller
{
    public function create(Request $request, AksesPresensi $akses): View|RedirectResponse
    {
        if (Auth::guard('web')->check() && $akses->masuk($request->user('web'))) {
            return redirect()->route('presensi.index');
        }
        return view('presensi.login');
    }

    public function store(Request $request, AksesPresensi $akses): RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            if ($akses->masuk($request->user('web'))) {
                return redirect()->route('presensi.index');
            }
            throw ValidationException::withMessages(['username' => 'Keluar dari akun yang sedang aktif terlebih dahulu.']);
        }
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:1024'],
        ]);
        $berhasil = Auth::guard('web')->attemptWhen([
            'username' => trim($data['username']),
            'password' => $data['password'],
            'status' => User::STATUS_AKTIF,
        ], fn(User $user): bool => $akses->dosen($user));
        if (! $berhasil) {
            throw ValidationException::withMessages(['username' => 'Akun tidak dapat digunakan. Periksa username/password atau hubungi akademik.']);
        }
        $request->session()->regenerate();
        // Tujuan tetap; abaikan intended URL dari halaman admin yang sempat dikunjungi.
        return redirect()->route('presensi.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('presensi.login');
    }
}
