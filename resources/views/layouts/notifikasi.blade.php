<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifikasi — SIAKAD Ilyas</title>
    <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
</head>

<body><a class="skip" href="#konten">Langsung ke isi</a>
    <header class="topbar"><a class="brand" href="{{ route('notifikasi.index') }}">ILYAS INSTITUTE
            <small>Notifikasi</small></a>
        @include('partials.navbar-portal')
    </header>
    <main class="container" id="konten">
        @if (session('info'))
            <div class="notice" role="status">{{ session('info') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice error" role="alert">
                <ul>
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    <footer class="container muted">Waktu WITA (UTC+8) · Notifikasi bukan bukti persetujuan akademik.</footer>
</body>

</html>
