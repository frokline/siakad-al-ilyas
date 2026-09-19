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
        <nav aria-label="Menu utama"><a href="{{ route('notifikasi.index') }}">Notifikasi saya</a>
            @can('akses-pengumuman')
                <a href="{{ route('pengumuman.index') }}">Pengumuman</a>
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
