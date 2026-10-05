<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Jenis Biaya') — SIAKAD Ilyas</title>
    <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
</head>

<body>
    <a class="skip" href="#konten">Langsung ke isi</a>
    <header class="topbar"><a class="brand" href="{{ route('keuangan.dashboard') }}">ILYAS INSTITUTE <small>Jenis
                Biaya</small></a>
        <nav aria-label="Menu utama">
<a
    href="{{ route('keuangan.dashboard') }}"
    @if (request()->routeIs('keuangan.dashboard'))
        aria-current="page"
    @endif
>
    Dashboard
</a>
            <a href="{{ route('keuangan.jenis-biaya.index') }}">Jenis biaya</a>
            @can('akses-berkas')
                <a href="{{ route('berkas.index') }}">Berkas saya</a>
            @endcan
            @can('akses-kegiatan')
                <a href="{{ route('kegiatan.index') }}">Kegiatan</a>
            @endcan
            <span>{{ auth('web')->user()->nama }}</span>
            <form method="post" action="{{ route('berkas.logout') }}">@csrf<button class="secondary"
                    type="submit">Keluar</button></form>

            @can('akses-tagihan')
                <a href="{{ route('tagihan.index') }}">Tagihan SPP</a>
            @endcan

            @can('akses-pembayaran')
                <a href="{{ route('pembayaran.index') }}">Pembayaran SPP</a>
            @endcan
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
    <footer class="container muted">SIAKAD Ilyas Institute · Katalog jenis biaya akademik.</footer>
</body>

</html>
