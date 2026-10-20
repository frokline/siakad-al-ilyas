<?php

namespace Tests\Feature;

use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class SecureSessionCookieTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['session.secure' => null]);
    }

    protected function tearDown(): void
    {
        config(['session.secure' => null]);

        parent::tearDown();
    }

    public function test_cookie_session_tidak_ditandai_secure_pada_http(): void
    {
        $response = $this->get('http://localhost/login');

        $response->assertOk();

        $kue = $this->kueSesi($response);

        $this->assertNotNull($kue, 'Cookie session tidak ditemukan pada respons.');

        $this->assertFalse($kue->isSecure());
    }

    public function test_cookie_session_ditandai_secure_pada_https(): void
    {
        $response = $this->get(
            'https://siakad.ilyasinstitute.ac.id/login'
        );

        $response->assertOk();

        $kue = $this->kueSesi($response);

        $this->assertNotNull($kue, 'Cookie session tidak ditemukan pada respons.');

        $this->assertTrue($kue->isSecure());
    }

    public function test_pengaturan_eksplicit_pada_config_dihormati(): void
    {
        config(['session.secure' => false]);

        $response = $this->get(
            'https://siakad.ilyasinstitute.ac.id/login'
        );

        $response->assertOk();

        $kue = $this->kueSesi($response);

        $this->assertNotNull($kue, 'Cookie session tidak ditemukan pada respons.');

        $this->assertFalse($kue->isSecure());
    }

    private function kueSesi(TestResponse $response): ?Cookie
    {
        $nama = (string) config('session.cookie');

        foreach ($response->headers->getCookies() as $kue) {
            if ($kue->getName() === $nama) {
                return $kue;
            }
        }

        return null;
    }
}
