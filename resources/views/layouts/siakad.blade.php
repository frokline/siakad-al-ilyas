<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'SIAKAD') — ILYAS INSTITUT</title>

    <!-- Tailwind CSS CDN (Wajib ditambahkan agar desain modern split-screen aktif) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="{{ asset('css/siakad.css') }}">
</head>

<body class="bg-slate-100 min-h-screen flex flex-col">
    <a href="#konten-utama" class="skip-link">
        Lewati navigasi
    </a>

    <header class="topbar">
        <div class="topbar-inner">
            <a href="{{ route('home') }}">
                <small>ILYAS INSTITUT</small>
            </a>

            <div class="account">
                @auth('web')
                    <span>{{ auth('web')->user()->nama }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="button button-secondary button-small">
                            Keluar
                        </button>
                    </form>
                @else
                    <span>Portal Akademik</span>
                @endauth
            </div>
        </div>
    </header>

    <main id="konten-utama" class="container flex-1">
        @auth('web')
            @include('partials.navbar-portal')
        @endauth

        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @can('akses-berkas')
            <a href="{{ route('berkas.index') }}">Berkas saya</a>
        @endcan

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <strong>Periksa kembali data berikut:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>

</html>
