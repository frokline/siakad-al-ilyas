<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'SIAKAD') — Ilyas Institute</title>

    <link rel="stylesheet" href="{{ asset('css/siakad.css') }}">
</head>

<body>
    <a href="#konten-utama" class="skip-link">
        Lewati navigasi
    </a>

    <header class="topbar">
        <div class="topbar-inner">
            <a href="{{ auth('web')->check() ? route('admin.roles.index') : route('login') }}">
                <small>ILYAS INSTITUTE</small>
            </a>

            <div class="account">
                @auth('web')
                    <span>{{ auth('web')->user()->nama }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="button button-secondary button-small">
                            Keluar
                        </button>
                    </form>
                @else
                    <span>Portal Akademik</span>
                @endauth
            </div>
        </div>
    </header>

    <main id="konten-utama" class="container">
        @auth('web')
            <nav class="module-nav" aria-label="Menu administrasi">
                @can('kelola-peran')
                    <a href="{{ route('admin.roles.index') }}" @if (request()->routeIs('admin.roles.*')) aria-current="page" @endif>
                        Peran
                    </a>
                @endcan

                @can('kelola-pengguna')
                    <a href="{{ route('admin.users.index') }}" @if (request()->routeIs('admin.users.*')) aria-current="page" @endif>
                        Pengguna
                    </a>
                @endcan
                @can('kelola-program-studi')
                    <a href="{{ route('admin.program-studi.index') }}"
                        @if (request()->routeIs('admin.program-studi.*')) aria-current="page" @endif>
                        Program Studi
                    </a>
                @endcan

                @can('kelola-periode-akademik')
                    <a href="{{ route('admin.periode-akademik.index') }}"
                        @if (request()->routeIs('admin.periode-akademik.*')) aria-current="page" @endif>
                        Periode Akademik
                    </a>
                @endcan

                @can('kelola-kurikulum')
                    <a href="{{ route('admin.kurikulum.index') }}" @if (request()->routeIs('admin.kurikulum.*')) aria-current="page" @endif>
                        Kurikulum
                    </a>
                @endcan

                @can('kelola-mata-kuliah')
                    <a href="{{ route('admin.mata-kuliah.index') }}"
                        @if (request()->routeIs('admin.mata-kuliah.*')) aria-current="page" @endif>
                        Mata Kuliah
                    </a>
                @endcan

                @can('kelola-dosen')
                    <a href="{{ route('admin.dosen.index') }}" @if (request()->routeIs('admin.dosen.*')) aria-current="page" @endif>
                        Dosen
                    </a>
                @endcan

                @can('kelola-riwayat-studi')
                    <a href="{{ route('admin.riwayat-studi.index') }}"
                        @if (request()->routeIs('admin.riwayat-studi.*')) aria-current="page" @endif>
                        Riwayat Studi
                    </a>
                @endcan

                @can('kelola-mahasiswa')
                    <a href="{{ route('admin.mahasiswa.index') }}"
                        class="{{ request()->routeIs('admin.mahasiswa.*') ? 'active' : '' }}"
                        @if (request()->routeIs('admin.mahasiswa.*')) aria-current="page" @endif>
                        Mahasiswa
                    </a>
                @endcan

                @can('kelola-paket-semester')
                    <a href="{{ route('admin.paket-semester.index') }}"
                        @if (request()->routeIs('admin.paket-semester.*')) aria-current="page" @endif>
                        Paket Semester
                    </a>
                @endcan

                @can('kelola-rombel')
                    <a href="{{ route('admin.rombel.index') }}" @if (request()->routeIs('admin.rombel.*')) aria-current="page" @endif>
                        Rombel
                    </a>
                @endcan

                @can('kelola-registrasi-semester')
                    <a href="{{ route('admin.registrasi-semester.index') }}"
                        @if (request()->routeIs('admin.registrasi-semester.*')) aria-current="page" @endif>
                        Registrasi Semester
                    </a>
                @endcan

                @can('kelola-kelas-kuliah')
                    <a href="{{ route('admin.kelas-kuliah.index') }}"
                        @if (request()->routeIs('admin.kelas-kuliah.*')) aria-current="page" @endif>
                        Kelas Kuliah
                    </a>
                @endcan

                @can('kelola-krs')
                    <a href="{{ route('admin.krs.index') }}" @if (request()->routeIs('admin.krs.*')) aria-current="page" @endif>
                        KRS
                    </a>
                @endcan

                @can('kelola-pengajar-kelas')
                    <a href="{{ route('admin.pengajar-kelas.index') }}"
                        @if (request()->routeIs('admin.pengajar-kelas.*')) aria-current="page" @endif>
                        Pengajar Kelas
                    </a>
                @endcan

                @can('kelola-jadwal-kuliah')
                    <a href="{{ route('admin.jadwal-kuliah.index') }}"
                        @if (request()->routeIs('admin.jadwal-kuliah.*')) aria-current="page" @endif>
                        Jadwal Kuliah
                    </a>
                @endcan

                @can('kelola-pertemuan')
                    <a href="{{ route('admin.pertemuan.index') }}"
                        @if (request()->routeIs('admin.pertemuan.*')) aria-current="page" @endif>
                        Pertemuan Kuliah
                    </a>
                @endcan

                @can('akses-presensi')
                    <a href="{{ route('presensi.index') }}">Presensi Mahasiswa</a>
                @endcan
            </nav>
        @endauth
        <nav class="breadcrumb" aria-label="Lokasi halaman">
            @yield('breadcrumb')
        </nav>

        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @can('akses-berkas')
            <a href="{{ route('berkas.index') }}">Berkas saya</a>
        @endcan

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <strong>Periksa kembali data berikut:</strong>

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
