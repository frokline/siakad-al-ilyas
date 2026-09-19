<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AksesBerkas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BerkasSessionController extends Controller
{
    public function create(Request $request, AksesBerkas $akses): View|RedirectResponse
    {
        if (Auth::guard('web')->check() && $akses->masuk($request->user('web'))) {
            return redirect()->route('berkas.index');
        }
        return view('berkas.login');
    }
    public function store(Request $request, AksesBerkas $akses): RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            if ($akses->masuk($request->user('web'))) {
                return redirect()->route('berkas.index');
            }
            throw ValidationException::withMessages(['username' => 'Keluar dari akun yang sedang aktif terlebih dahulu.']);
        }
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:1024']
        ]);
        $ok = Auth::guard('web')->attemptWhen([
            'username' => trim($data['username']),
            'password' => $data['password'],
            'status' => 'aktif'
        ], fn(User $user): bool => $akses->masuk($user));
        if (! $ok) {
            throw ValidationException::withMessages(['username' => 'Akun tidak dapat digunakan. Periksa data masuk atau hubungi akademik.']);
        }
        $request->session()->regenerate();
        return redirect()->route('berkas.index');
    }
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('berkas.login');
    }
}
