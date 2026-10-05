<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(
        function (Middleware $middleware): void {
            /*
             * Dipasang untuk seluruh respons HTTP aplikasi.
             */
            $middleware->append(
                SecurityHeaders::class
            );
        }
    )
    ->withExceptions(
        function (Exceptions $exceptions): void {
            /*
             * URL pertemuan tidak boleh dimasukkan kembali
             * ke session ketika validasi gagal.
             */
            $exceptions->dontFlash([
                'tautan_pertemuan',
            ]);
        }
    )
    ->create();