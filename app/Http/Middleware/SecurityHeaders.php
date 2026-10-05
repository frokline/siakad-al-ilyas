<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /** @var Response $response */
        $response = $next($request);

        $headers = $response->headers;

        $this->pasangJikaBelumAda(
            $headers,
            'X-Content-Type-Options',
            'nosniff'
        );

        $this->pasangJikaBelumAda(
            $headers,
            'X-Frame-Options',
            'DENY'
        );

        $this->pasangJikaBelumAda(
            $headers,
            'Referrer-Policy',
            'same-origin'
        );

        $this->pasangJikaBelumAda(
            $headers,
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        $this->pasangJikaBelumAda(
            $headers,
            'Cross-Origin-Opener-Policy',
            'same-origin'
        );

        $this->pasangJikaBelumAda(
            $headers,
            'X-Permitted-Cross-Domain-Policies',
            'none'
        );

        /*
         * HSTS hanya dikirim ketika aplikasi benar-benar berjalan
         * melalui HTTPS pada lingkungan production.
         *
         * Header ini tidak dipasang pada localhost HTTP karena dapat
         * mengganggu pengembangan lokal.
         */
        if (
            app()->environment('production')
            && $request->isSecure()
        ) {
            $this->pasangJikaBelumAda(
                $headers,
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }

    private function pasangJikaBelumAda(
        object $headers,
        string $nama,
        string $nilai
    ): void {
        if (! $headers->has($nama)) {
            $headers->set($nama, $nilai);
        }
    }
}