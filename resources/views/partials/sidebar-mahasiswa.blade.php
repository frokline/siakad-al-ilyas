@php
    $logoKampus = 'images/logo-ilyas-institut.png';
    $menuAktif = 'bg-siakad-active text-white font-semibold';
    $menuBiasa = 'text-emerald-100 hover:bg-white/10 hover:text-white';
    $ikon = [
        'beranda' =>
            'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'profil' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'krs' =>
            'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'jadwal' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'presensi' =>
            'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'kegiatan' =>
            'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'berkas' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
        'tagihan' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
        'pembayaran' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'surat' =>
            'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'kalender' =>
            'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
        'pengumuman' =>
            'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        'notifikasi' =>
            'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    ];
    // [kelompok, gate, route, pola route aktif, label, ikon]
    // Item otomatis disembunyikan jika route belum ada atau Gate menolak.
    $menu = [
        ['Utama', '', 'portal.mahasiswa.index', 'portal.mahasiswa.*', 'Beranda', 'beranda'],
        ['Utama', '', 'portal.profil.show', 'portal.profil.*', 'Profil Saya', 'profil'],
        ['Utama', 'akses-notifikasi', 'notifikasi.index', 'notifikasi.*', 'Notifikasi', 'notifikasi'],
        ['Utama', 'akses-pengumuman', 'pengumuman.index', 'pengumuman.*', 'Pengumuman', 'pengumuman'],

        ['Akademik', '', 'portal.krs.index', 'portal.krs.*', 'KRS Saya', 'krs'],
        ['Akademik', '', 'portal.jadwal.index', 'portal.jadwal.*', 'Jadwal Saya', 'jadwal'],
        ['Akademik', '', 'portal.presensi.index', 'portal.presensi.*', 'Riwayat Presensi', 'presensi'],
        ['Akademik', 'akses-kalender', 'kalender.index', 'kalender.*', 'Kalender Akademik', 'kalender'],

        ['Pembelajaran', 'akses-kegiatan', 'kegiatan.index', 'kegiatan.*', 'Tugas & Kegiatan', 'kegiatan'],
        ['Pembelajaran', 'akses-berkas', 'berkas.index', 'berkas.*', 'Berkas', 'berkas'],

        ['Keuangan', 'akses-tagihan', 'tagihan.index', 'tagihan.*', 'Tagihan', 'tagihan'],
        ['Keuangan', 'akses-pembayaran', 'pembayaran.index', 'pembayaran.*', 'Pembayaran SPP', 'pembayaran'],

        ['Layanan', 'akses-surat', 'surat.index', 'surat.*', 'Surat', 'surat'],
    ];

    // Saring item yang boleh tampil, lalu kelompokkan.
    $tampil = [];
    foreach ($menu as $item) {
        [$kelompok, $gate, $rute] = $item;
        // Lewati jika route belum ada atau membutuhkan parameter (tidak bisa dijadikan menu).
        if (!Route::has($rute) || count(Route::getRoutes()->getByName($rute)->parameterNames()) > 0) {
            continue;
        }
        if ($gate !== '' && !Gate::allows($gate)) {
            continue;
        }
        $tampil[$kelompok][] = $item;
    }
@endphp
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-siakad-dark text-white transition-transform duration-300 lg:static lg:translate-x-0 shadow-2xl">
    <div class="flex h-16 shrink-0 items-center px-6 gap-3">
        @if (is_file(public_path($logoKampus)))
            <img src="{{ asset($logoKampus) }}" alt="Logo Ilyas Institute"
                class="h-10 w-10 shrink-0 rounded-lg bg-white object-cover shadow-sm ring-1 ring-white/20">
        @else
            <div
                class="flex h-8 w-8 items-center justify-center rounded bg-siakad-accent text-siakad-dark font-bold text-lg">
                S</div>
        @endif
        <div>
            <h1 class="text-sm font-bold tracking-wide text-white leading-tight">SIAKAD</h1>
            <p class="text-[10px] text-emerald-200">PORTAL MAHASISWA</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-4">
        @foreach ($tampil as $kelompok => $daftar)
            <div>
                <p class="mb-1 px-3 text-[10px] font-bold uppercase tracking-widest text-emerald-300/70">
                    {{ $kelompok }}</p>
                <div class="space-y-1">
                    @foreach ($daftar as [, , $rute, $pola, $label, $kunci])
                        @php $aktif = request()->routeIs(...(array) $pola); @endphp
                        <a href="{{ route($rute) }}" @if ($aktif) aria-current="page" @endif
                            class="flex items-center gap-3 rounded-lg px-3 py-2 transition-colors {{ $aktif ? $menuAktif : $menuBiasa }}">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon[$kunci] }}" />
                            </svg>
                            <span class="text-sm">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="p-4 border-t border-white/10 bg-siakad-dark">
        <div class="flex items-center gap-3 mb-4">
            <div
                class="h-10 w-10 shrink-0 rounded-full bg-slate-300 overflow-hidden border border-emerald-500 flex items-center justify-center text-siakad-dark font-bold">
                {{ strtoupper(substr(auth()->user()->nama ?? 'M', 0, 1)) }}
            </div>
            <div class="overflow-hidden">
                <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->nama ?? 'Mahasiswa' }}</p>
                <p class="text-xs text-emerald-300 truncate">Mahasiswa</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 rounded bg-siakad-active px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-600 border border-emerald-500">
                Logout
            </button>
        </form>
    </div>
</aside>
