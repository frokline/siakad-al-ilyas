<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'Pengumpulan Jawaban') — SIAKAD Ilyas</title>
    <link rel="stylesheet" href="{{ asset('css/materi.css') }}">
</head>

<body>
    <a class="skip" href="#konten">Langsung ke isi</a>
    <header class="topbar"><a class="brand" href="{{ route('pengumpulan.index') }}">ILYAS INSTITUTE <small>Pengumpulan
                Jawaban</small></a>
        <nav aria-label="Menu utama">
            <a href="{{ route('pengumpulan.index') }}">Jawaban</a>
            @can('akses-kegiatan')
                <a href="{{ route('kegiatan.index') }}">Tugas dan Ujian</a>
            @endcan
            @can('akses-berkas')
                <a href="{{ route('berkas.index') }}">Berkas saya</a>
            @endcan
            <span>{{ auth('web')->user()->nama }}</span>
            <form method="post" action="{{ route('berkas.logout') }}">@csrf<button class="secondary"
                    type="submit">Keluar</button></form>
        </nav>
    </header>
    <main id="konten" class="container">
        @if (session('info'))
            <div class="notice" role="status">{{ session('info') }}</div>
        @endif
        @if (isset($errors) && $errors->any())
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
    <footer class="container muted">Simpan draf tidak sama dengan mengirim jawaban. Penilaian belum diaktifkan.</footer>
</body>

</html>
