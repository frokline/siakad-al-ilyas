<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminSessionController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user('web');

        if (
            $user instanceof User
            && $user->hasRole(Role::ADMIN_AKADEMIK)
        ) {
            return to_route('admin.roles.index');
        }

        return response()
            ->view('auth.login')
            ->withHeaders([
                'Cache-Control' => 'no-store, private',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'Referrer-Policy' => 'same-origin',
            ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $input = $request->input('login');

        $request->merge([
            'login' => is_string($input)
                ? Str::lower(trim($input))
                : '',
        ]);

        $validated = $request->validate([
            'login' => [
                'bail',
                'required',
                'string',
                'max:190',
            ],

            'password' => [
                'bail',
                'required',
                'string',
                'max:72',

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail,
                ): void {
                    if (strlen($value) > 72) {
                        $fail('Kata sandi maksimal 72 byte.');
                    }
                },
            ],

            'remember' => [
                'sometimes',
                'boolean',
            ],
        ], [
            'login.required' => 'Masukkan username atau email.',
            'login.max' => 'Username atau email terlalu panjang.',
            'password.required' => 'Masukkan kata sandi.',
            'password.max' => 'Kata sandi terlalu panjang.',
        ]);

        $field = str_contains($validated['login'], '@')
            ? 'email'
            : 'username';

        /** @var SessionGuard $guard */
        $guard = Auth::guard('web');

        $authenticated = $guard->attemptWhen(
            [
                $field => $validated['login'],
                'password' => $validated['password'],
                'status' => User::STATUS_AKTIF,
            ],
            fn(Authenticatable $user): bool => $user instanceof User && $user->hasRole(
                Role::ADMIN_AKADEMIK,
            ),
            $request->boolean('remember'),
        );

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'login' => 'Data masuk tidak valid atau akses admin tidak tersedia.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return to_route('admin.roles.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')
            ->with('success', 'Anda berhasil keluar.');
    }
}

