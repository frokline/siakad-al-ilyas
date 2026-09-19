<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pengumuman') — SIAKAD Ilyas</title>
    <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pengumuman.css') }}">
</head>

<body><a class="skip" href="#konten">Langsung ke isi</a>
    <header class="topbar"><a class="brand" href="{{ route('pengumuman.index') }}">ILYAS INSTITUTE
            <small>Pengumuman</small></a>
        <nav aria-label="Menu utama"><a href="{{ route('pengumuman.index') }}">Untuk saya</a>
            @can('create', \App\Models\Pengumuman::class)
                <a href="{{ route('pengumuman.index', ['mode' => 'kelola']) }}">Kelola pengumuman</a>
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
            <div class="notice error" role="alert"><strong>Periksa kembali isian.</strong>
                <ul>
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    <footer class="container muted">Waktu ditampilkan dalam WITA (UTC+8).</footer>
</body>

</html>
