<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Surat Akademik') — SIAKAD Ilyas</title>
    <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
</head>

<body><a class="skip" href="#konten">Langsung ke isi</a>
    <header class="topbar"><a class="brand" href="{{ route('surat.index') }}">ILYAS INSTITUTE <small>Surat
                Akademik</small></a>
        <nav aria-label="Menu utama"><a href="{{ route('surat.index') }}">Permohonan surat</a>
            @can('akses-jenis-surat')
                <a href="{{ route('admin.jenis-surat.index') }}">Jenis surat</a>
            @endcan
            @can('akses-berkas')
                <a href="{{ route('berkas.index') }}">Berkas saya</a>
            @endcan
            <span>{{ auth('web')->user()->nama }}</span>
            <form method="post" action="{{ route('berkas.logout') }}">@csrf<button type="submit"
                    class="secondary">Keluar</button></form>
        </nav>
    </header>
    <main id="konten" class="container">
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
    <footer class="container muted">Layanan Surat Akademik · Ilyas Institute</footer>
</body>

</html>
