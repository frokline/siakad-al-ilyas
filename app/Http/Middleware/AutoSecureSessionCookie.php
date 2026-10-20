<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoSecureSessionCookie
{
    /**
     * Menandai cookie session dengan flag Secure ketika request datang
     * lewat HTTPS. Nilai null pada config('session.secure') berarti
     * mode otomatis; nilai true/false hasil env() tetap dihormati.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('session.secure') === null) {
            config(['session.secure' => $request->isSecure()]);
        }

        return $next($request);
    }
}
