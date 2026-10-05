@extends('layouts.siakad')

@section('title', 'Masuk Portal Akademik')

@section('breadcrumb')
    <span>Portal Akademik</span>
    <span aria-hidden="true">/</span>
    <span>Masuk</span>
@endsection

@section('content')
    <div class="auth-shell">
        <div class="page-heading">
            <div>
                <p class="eyebrow">SIAKAD ILYAS INSTITUTE</p>
                <h1>Selamat datang kembali</h1>
                <p class="subtitle">Satu akun untuk portal akademik, dosen, mahasiswa, dan keuangan.</p>
            </div>
        </div>

        <section class="card form-card" aria-labelledby="judul-form-login">
            <div class="auth-card-heading">
                <h2 id="judul-form-login">Masuk ke akun Anda</h2>
                <p>Sistem akan mengarahkan Anda ke portal sesuai peran akun.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="stack">
                @csrf

                <div class="field">
                    <label for="login">Username atau email</label>
                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="{{ is_string(old('login')) ? old('login') : '' }}"
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
                        <p id="login-error" class="error" role="alert">{{ $message }}</p>
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
                        <p id="password-error" class="error" role="alert">{{ $message }}</p>
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

                <button type="submit" class="button auth-submit">Masuk ke Portal</button>
            </form>

            <p class="auth-help">
                Pastikan akun aktif dan memiliki profil sesuai peran. Hubungi admin akademik jika akses ditolak.
            </p>
        </section>
    </div>
@endsection
