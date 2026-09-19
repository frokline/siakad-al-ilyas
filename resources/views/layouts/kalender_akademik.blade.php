<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kalender Akademik') — SIAKAD Ilyas</title>
    <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kalender.css') }}">
</head>

<body><a class="skip" href="#konten">Langsung ke isi</a>
    <header class="topbar"><a class="brand" href="{{ route('kalender.index') }}">ILYAS INSTITUTE <small>Kalender
                Akademik</small></a>
        <nav aria-label="Menu utama"><a href="{{ route('kalender.index') }}">Kalender</a>
            @can('akses-surat')
                <a href="{{ route('surat.index') }}">Surat akademik</a>
            @endcan
            @can('akses-berkas')
                <a href="{{ route('berkas.index') }}">Berkas saya</a>
            @endcan
            <span>{{ auth('web')->user()->nama }}</span>
            <form method="post" action="{{ route('berkas.logout') }}">@csrf<button type="submit"
                    class="secondary">Keluar</button></form>
        </nav>
    </header>
    <main class="container" id="konten">
        @if (session('info'))
            <div class="notice" role="status">{{ session('info') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice error" role="alert"><strong>Permintaan belum berhasil.</strong>
                <ul>
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    <footer class="container muted">Waktu kalender: WITA (UTC+8) · Ilyas Institute</footer>
</body>

</html>
