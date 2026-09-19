@extends('layouts.siakad')

@section('title', 'Masuk Admin')

@section('breadcrumb')
    <span>Masuk Admin Akademik</span>
@endsection

@section('content')
    <div class="auth-shell">
        <div class="page-heading">
            <div>
                <h1>Masuk Admin</h1>

                <p class="subtitle">
                    Gunakan akun Admin Akademik Ilyas Institute.
                </p>
            </div>
        </div>

        <section class="card form-card" aria-label="Formulir masuk admin">
            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="field">
                    <label for="login">Username atau email</label>

                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="{{ old('login') }}"
                        maxlength="190"
                        autocomplete="username"
                        autocapitalize="none"
                        spellcheck="false"
                        required
                        autofocus
                        @error('login')
                            aria-invalid="true"
                            aria-describedby="login-error"
                        @enderror
                    >

                    @error('login')
                        <p id="login-error" class="error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Kata sandi</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        maxlength="72"
                        autocomplete="current-password"
                        required
                        @error('password')
                            aria-invalid="true"
                            aria-describedby="password-error"
                        @enderror
                    >

                    @error('password')
                        <p id="password-error" class="error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="field">
                    <label class="checkbox-label" for="remember">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            value="1"
                            @checked(old('remember'))
                        >

                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="button auth-submit">
                    Masuk
                </button>
            </form>
        </section>
    </div>
@endsection