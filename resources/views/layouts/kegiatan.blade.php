<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tugas dan Ujian') — SIAKAD Ilyas</title>
    <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
</head>

<body>
    <a class="skip" href="#konten">Langsung ke isi</a>
    <header class="topbar">
        <a class="brand" href="{{ route('kegiatan.index') }}">ILYAS INSTITUTE <small>Kegiatan Kuliah</small></a>
        <nav aria-label="Menu utama">
            <a href="{{ route('kegiatan.index') }}">Kegiatan</a>
            @can('akses-materi')
                <a href="{{ route('materi.index') }}">Materi</a>
            @endcan
            @can('akses-berkas')
                <a href="{{ route('berkas.index') }}">Berkas saya</a>
            @endcan
            @can('akses-presensi')
                <a href="{{ route('presensi.index') }}">Presensi</a>
            @endcan
            <span>{{ auth('web')->user()->nama }}</span>
            <form method="post" action="{{ route('berkas.logout') }}">@csrf<button type="submit"
                    class="secondary">Keluar</button></form>
            @can('akses-pengumpulan')
                <a href="{{ route('pengumpulan.index') }}">Jawaban</a>
            @endcan
        </nav>
    </header>
    <main id="konten" class="container">
        @if (session('info'))
            <div class="notice" role="status">{{ session('info') }}</div>
        @endif
        @if (isset($errors) && $errors->any())
            <div class="notice error" role="alert"><strong>Data belum disimpan.</strong>
                <ul>
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    <footer class="container muted">SIAKAD Ilyas Institute · Kegiatan hanya untuk peserta kelas yang berhak.</footer>
</body>

</html>
