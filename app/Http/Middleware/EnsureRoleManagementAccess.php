<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleManagementAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user();

        if (! ($actor instanceof User)) {
            if (Route::has('login')) {
                return redirect()->guest(route('login'));
            }

            abort(401, 'Silakan masuk sebagai admin. Halaman login belum tersedia.');
        }

        $freshActor = User::query()->find($actor->getKey());

        abort_unless(
            $freshActor?->isAktif(),
            403,
            'Akun Anda tidak aktif.'
        );

        Gate::forUser($freshActor)->authorize('kelola-peran');

        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'same-origin');

        return $response;
    }
}
