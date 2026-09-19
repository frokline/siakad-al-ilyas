<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tagihan') — Ilyas Institute</title>
    <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
</head>

<body>
    <header class="topbar"><a class="brand" href="{{ route('tagihan.index') }}">ILYAS INSTITUTE <small>Tagihan
                Bulanan</small></a>
        <nav><a href="{{ route('tagihan.index') }}">Tagihan</a>
            @can('akses-jenis-biaya')
                <a href="{{ route('keuangan.jenis-biaya.index') }}">Jenis biaya</a>
            @endcan
            <a href="{{ route('berkas.index') }}">Berkas saya</a>
            <span>{{ auth()->user()->nama }}</span>
            <form method="post" action="{{ route('berkas.logout') }}">@csrf<button type="submit"
                    class="secondary">Keluar</button></form>

            @can('akses-pembayaran')
                <a href="{{ route('pembayaran.index') }}">Pembayaran SPP</a>
            @endcan
        </nav>
    </header>
    <main class="container">
        @if (session('info'))
            <div class="alert success" role="status">{{ session('info') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert error" role="alert"><strong>Periksa isian:</strong>
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
