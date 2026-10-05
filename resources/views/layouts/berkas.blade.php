<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Berkas Saya') — SIAKAD Ilyas</title>
    <link rel="stylesheet" href="{{ asset('css/berkas.css') }}">
</head>

<body>
    <a class="skip" href="#konten">Langsung ke isi</a>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}">ILYAS INSTITUTE <span>Portal akademik</span></a>
        <nav aria-label="Navigasi berkas">
            @auth('web')
@if (Route::has('portal.mahasiswa.index'))
    <a href="{{ route('portal.mahasiswa.index') }}">
        Beranda
    </a>
@endif

@if (auth('web')->user()->hasRole(\App\Models\Role::MAHASISWA))
    <a href="{{ route('portal.profil.show') }}">
        Profil Saya
    </a>

    <a href="{{ route('portal.krs.index') }}">
        KRS Saya
    </a>

    <a href="{{ route('portal.jadwal.index') }}">
        Jadwal Saya
    </a>

    <a href="{{ route('portal.presensi.index') }}">
        Riwayat Presensi
    </a>
@endif
                @can('akses-presensi')
                    <a href="{{ route('presensi.index') }}">Presensi</a>
                @endcan
                @can('kelola-pertemuan')
                    <a href="{{ route('admin.pertemuan.index') }}">Akademik</a>
                @endcan
                <span>{{ auth('web')->user()->nama }}</span>
                <form action="{{ route('logout') }}" method="post">@csrf<button class="secondary"
                        type="submit">Keluar</button></form>
            @else
                <a href="{{ route('login') }}">Masuk</a>
            @endauth

            @can('akses-jenis-biaya')
                <a href="{{ route('keuangan.jenis-biaya.index') }}">Jenis biaya</a>
            @endcan

            @can('akses-tagihan')
                <a href="{{ route('tagihan.index') }}">Tagihan SPP</a>
            @endcan

            @can('akses-pembayaran')
                <a href="{{ route('pembayaran.index') }}">Pembayaran SPP</a>
            @endcan
        </nav>
    </header>
    <main class="container" id="konten">
        @if (session('info'))
            <div class="notice" role="status">{{ session('info') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice error" role="alert"><strong>Periksa kembali data.</strong>
                <ul>
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    <footer class="container muted">SIAKAD Ilyas Institute</footer>
</body>

</html>
