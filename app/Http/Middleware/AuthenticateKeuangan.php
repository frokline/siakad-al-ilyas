<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;

class AuthenticateKeuangan extends Authenticate
{
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson()
            ? null
            : route('berkas.login');
    }
}
