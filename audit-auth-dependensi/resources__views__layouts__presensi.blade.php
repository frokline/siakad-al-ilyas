Layout portal dosen berdiri sendiri agar navigasinya tidak bergantung pada izin modul administrasi.

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Presensi') — Ilyas Institute</title>
    <link rel="stylesheet" href="{{ asset('css/presensi.css') }}">
</head>

<body>
    <a class="skip-link" href="#konten">Lewati ke isi halaman</a>
    <header class="topbar">
        <a class="brand" href="{{ route('presensi.index') }}">ILYAS INSTITUTE <span>Presensi perkuliahan</span></a>
        <nav aria-label="Navigasi presensi">
            @auth('web')
                @can('akses-presensi')
                    <a href="{{ route('presensi.index') }}">Daftar pertemuan</a>
                @endcan
                @can('kelola-pertemuan')
                    <a href="{{ route('admin.pertemuan.index') }}">Akademik</a>
                @endcan
                <span>{{ auth('web')->user()->nama }}</span>
                <form method="post" action="{{ route('presensi.logout') }}">@csrf<button class="secondary"
                        type="submit">Keluar</button></form>
            @else
                <a href="{{ route('login') }}">Login admin</a>
            @endauth
        </nav>
    </header>
    <main id="konten" class="container">
        @if (session('success'))
            <div class="notice success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice error" role="alert" tabindex="-1">
                <strong>Periksa kembali data berikut.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    <footer class="container muted">SIAKAD Ilyas Institute · Presensi dicatat oleh pengajar.</footer>
</body>

</html>
