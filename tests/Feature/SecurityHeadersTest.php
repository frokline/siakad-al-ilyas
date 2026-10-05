<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_halaman_login_memiliki_header_keamanan(): void
    {
        $response = $this->get(
            route('login')
        );

        $response->assertOk();

        $response->assertHeader(
            'X-Content-Type-Options',
            'nosniff'
        );

        $response->assertHeader(
            'X-Frame-Options',
            'DENY'
        );

        $response->assertHeader(
            'Referrer-Policy',
            'same-origin'
        );

        $response->assertHeader(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        $response->assertHeader(
            'Cross-Origin-Opener-Policy',
            'same-origin'
        );

        $response->assertHeader(
            'X-Permitted-Cross-Domain-Policies',
            'none'
        );
    }

    public function test_header_hsts_tidak_dikirim_pada_http_lokal(): void
    {
        $response = $this->get(
            route('login')
        );

        $response->assertOk();

        $response->assertHeaderMissing(
            'Strict-Transport-Security'
        );
    }

    public function test_endpoint_kesehatan_juga_memiliki_header_keamanan(): void
    {
        $response = $this->get('/up');

        $response->assertOk();

        $response->assertHeader(
            'X-Content-Type-Options',
            'nosniff'
        );

        $response->assertHeader(
            'X-Frame-Options',
            'DENY'
        );

        $response->assertHeader(
            'Referrer-Policy',
            'same-origin'
        );
    }
}