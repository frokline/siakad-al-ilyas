<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pembayaran') — Ilyas Institute</title>
    <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
</head>

<body>
    <header class="topbar">
        <a class="brand" href="{{ route('pembayaran.index') }}">
            ILYAS INSTITUTE
            <small>Pembayaran SPP</small>
        </a>

        <nav>
            <a href="{{ route('tagihan.index') }}">Tagihan</a>
            <a href="{{ route('pembayaran.index') }}">Pembayaran</a>
            <a href="{{ route('berkas.index') }}">Berkas saya</a>

            <span>{{ auth()->user()->nama }}</span>

            <form method="POST" action="{{ route('berkas.logout') }}">
                @csrf
                <button type="submit" class="secondary">Keluar</button>
            </form>
        </nav>
    </header>

    <main class="container">
        @if (session('info'))
            <div class="alert success" role="status">
                {{ session('info') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="alert error" role="alert">
                <ul>
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>

</html>
