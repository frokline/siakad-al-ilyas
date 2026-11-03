<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TujuanPortal;
use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PortalSessionController extends Controller
{
    public function home(Request $request, TujuanPortal $tujuan): RedirectResponse
    {
        $user = $request->user('web');

        if ($user instanceof User) {
            $namaRoute = $tujuan->namaRoute($user);

            if ($namaRoute !== null) {
                return to_route($namaRoute);
            }
        }

        return to_route('login');
    }

    public function create(Request $request, TujuanPortal $tujuan): Response|RedirectResponse
    {
        $user = $request->user('web');

        if ($user instanceof User) {
            $namaRoute = $tujuan->namaRoute($user);

            if ($namaRoute !== null) {
                return to_route($namaRoute);
            }

            $this->akhiriSesi($request);
        }

        return response()
            ->view('auth.login')
            ->withHeaders($this->headerPrivat());
    }

    public function store(Request $request, TujuanPortal $tujuan): RedirectResponse
    {
        $userAktif = $request->user('web');

        if ($userAktif instanceof User) {
            $namaRoute = $tujuan->namaRoute($userAktif);

            if ($namaRoute !== null) {
                return to_route($namaRoute);
            }

            $this->akhiriSesi($request);
        }

        $input = $request->input('login', $request->input('username'));

        $request->merge([
            'login' => is_string($input)
                ? Str::lower(trim($input))
                : '',
        ]);

        $validated = $request->validate([
            'turnstile_token' => [
                'bail',
                'required',
                'string',
                'max:2048',
            ],
            'login' => ['bail', 'required', 'string', 'max:190'],
            'password' => [
                'bail',
                'required',
                'string',
                'max:72',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value) && strlen($value) > 72) {
                        $fail('Kata sandi maksimal 72 byte.');
                    }
                },
            ],
            'remember' => ['sometimes', 'boolean'],
        ], [
            'login.required' => 'Masukkan username atau email.',
            'login.max' => 'Username atau email terlalu panjang.',
            'password.required' => 'Masukkan kata sandi.',
            'password.max' => 'Kata sandi terlalu panjang.',
        ]);

        $field = str_contains($validated['login'], '@')
            ? 'email'
            : 'username';
        // Verifikasi Turnstile wajib dilakukan di server.
        $secretKey = config('services.turnstile.secret_key');

        if (! is_string($secretKey) || trim($secretKey) === '') {
            throw ValidationException::withMessages([
                'turnstile_token' =>
                'Pemeriksaan keamanan belum dikonfigurasi. Hubungi administrator.',
            ]);
        }

        try {
            $hasilCaptcha = Http::asForm()
                ->connectTimeout(3)
                ->timeout(6)
                ->post(
                    'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                    [
                        'secret' => $secretKey,
                        'response' => $validated['turnstile_token'],
                    ]
                );
        } catch (ConnectionException $exception) {
            throw ValidationException::withMessages([
                'turnstile_token' =>
                'Pemeriksaan keamanan sedang tidak tersedia. Silakan coba lagi.',
            ]);
        }

        if (
            ! $hasilCaptcha->successful()
            || $hasilCaptcha->json('success') !== true
        ) {
            throw ValidationException::withMessages([
                'turnstile_token' =>
                'Verifikasi keamanan gagal atau kedaluwarsa. Silakan coba lagi.',
            ]);
        }

        /** @var SessionGuard $guard */
        $guard = Auth::guard('web');

        $berhasil = $guard->attemptWhen(
            [
                $field => $validated['login'],
                'password' => $validated['password'],
                'status' => User::STATUS_AKTIF,
            ],
            fn(Authenticatable $user): bool => $user instanceof User
                && $tujuan->dapatMasuk($user),
            $request->boolean('remember'),
        );

        if (! $berhasil) {
            throw ValidationException::withMessages([
                'login' => 'Data masuk tidak valid atau akun belum memiliki akses portal.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        /** @var User $user */
        $user = $guard->user();
        $namaRoute = $tujuan->namaRoute($user);

        if ($namaRoute === null) {
            $this->akhiriSesi($request);

            throw ValidationException::withMessages([
                'login' => 'Akun belum memiliki akses portal yang lengkap.',
            ]);
        }

        return to_route($namaRoute);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->akhiriSesi($request);

        return to_route('login')
            ->with('success', 'Anda berhasil keluar.');
    }

    private function akhiriSesi(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /** @return array<string, string> */
    private function headerPrivat(): array
    {
        return [
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'same-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
        ];
    }
}
