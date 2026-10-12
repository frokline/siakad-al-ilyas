<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk Portal Akademik') | Ilyas Institut</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login-mobile.css') }}">
</head>

<body class="login-page">
    <main class="login-shell" aria-label="Login Portal Akademik Ilyas Institut">
        <section class="login-visual" aria-label="Gedung Ilyas Institut">
            <img class="login-visual-image" src="{{ asset('images/gedung-ilyas.jpg') }}" alt="Gedung Ilyas Institut"
                fetchpriority="high">

            <div class="visual-content">
                <p class="visual-label">Portal Akademik</p>
                <h1>Selamat datang di <span>Ilyas Institut.</span></h1>
                <p class="visual-description">
                    Akses layanan akademik Anda melalui satu portal yang mudah, nyaman, dan terintegrasi.
                </p>
                <div class="visual-rule" aria-hidden="true"></div>
            </div>
        </section>

        <section class="login-panel" aria-labelledby="login-title">
            <div class="login-brand">
                <img class="login-logo" src="{{ asset('images/logo-ilyas-institut.png') }}" alt="Logo Ilyas Institut"
                    fetchpriority="high">
            </div>

            <header class="login-heading">
                <p class="login-eyebrow">Sistem Informasi Akademik</p>
                <h2 id="login-title">Masuk ke akun Anda</h2>
                <p>Silakan masukkan username atau email dan kata sandi untuk melanjutkan.</p>
            </header>

            @if (session('status'))
                <div class="login-alert" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="login-form">
                @csrf

                <div class="login-field">
                    <label for="login">Username atau email</label>
                    <div class="login-input-wrap">
                        <svg class="login-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3.5" y="5" width="17" height="14" rx="3" stroke="currentColor"
                                stroke-width="1.7" />
                            <path d="m5 7 7 5.5L19 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        <input class="login-input" type="text" id="login" name="login"
                            value="{{ is_string(old('login')) ? old('login') : '' }}"
                            placeholder="Masukkan username atau email" maxlength="190" autocomplete="username"
                            autocapitalize="none" spellcheck="false" required autofocus
                            @error('login')
                                aria-invalid="true"
                                aria-describedby="login-error"
                            @enderror>
                    </div>
                    @error('login')
                        <p class="login-error" id="login-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="login-field">
                    <label for="password">Kata sandi</label>
                    <div class="login-input-wrap">
                        <svg class="login-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="4.5" y="10" width="15" height="11" rx="2.5" stroke="currentColor"
                                stroke-width="1.7" />
                            <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" />
                            <path d="M12 14.5v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                        </svg>
                        <input class="login-input login-input-password" type="password" id="password" name="password"
                            placeholder="Masukkan kata sandi" maxlength="72" autocomplete="current-password" required
                            @error('password')
                                aria-invalid="true"
                                aria-describedby="password-error"
                            @enderror>
                        <button class="password-toggle" type="button" id="toggle-password"
                            aria-label="Tampilkan kata sandi" aria-pressed="false">
                            <svg id="password-eye-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M2.5 12s3.3-6 9.5-6 9.5 6 9.5 6-3.3 6-9.5 6-9.5-6-9.5-6Z"
                                    stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                                <circle cx="12" cy="12" r="2.7" stroke="currentColor"
                                    stroke-width="1.7" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="login-error" id="password-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="login-options">
                    <label class="login-check" for="remember">
                        <input type="checkbox" id="remember" name="remember" value="1"
                            @checked(old('remember'))>
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="login-submit">
                    <span>Masuk ke Portal</span>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4.5 12h14m-5.5-5.5L18.5 12 13 17.5" stroke="currentColor" stroke-width="1.9"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </form>

            <p class="login-help">
                Pastikan akun Anda aktif. Hubungi administrator akademik jika mengalami kendala saat masuk.
            </p>

            <footer class="login-footer">
                © {{ date('Y') }} Ilyas Institut. Portal akademik untuk pengguna terdaftar.
            </footer>
        </section>
    </main>

    <script>
        (() => {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('toggle-password');
            const eyeIcon = document.getElementById('password-eye-icon');

            if (!passwordInput || !toggleButton || !eyeIcon) return;

            toggleButton.addEventListener('click', () => {
                const showPassword = passwordInput.type === 'password';
                passwordInput.type = showPassword ? 'text' : 'password';
                toggleButton.setAttribute('aria-pressed', String(showPassword));
                toggleButton.setAttribute(
                    'aria-label',
                    showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'
                );

                eyeIcon.innerHTML = showPassword ?
                    '<path d="M3 3l18 18M10.6 10.7a2 2 0 0 0 2.7 2.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.2 0 9.5 7 9.5 7a15 15 0 0 1-3 3.8M6.2 6.3C3.8 8 2.5 12 2.5 12s3.3 7 9.5 7c1.3 0 2.5-.3 3.5-.8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>' :
                    '<path d="M2.5 12s3.3-6 9.5-6 9.5 6 9.5 6-3.3 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="12" r="2.7" stroke="currentColor" stroke-width="1.7"/>';
            });
        })();
    </script>
</body>

</html>
