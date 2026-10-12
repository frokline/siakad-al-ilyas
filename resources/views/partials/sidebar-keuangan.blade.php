@php
    $logoKampus = 'images/logo-ilyas-institut.png';
    $menuAktif = 'bg-siakad-active text-white font-semibold';
    $menuBiasa = 'text-emerald-100 hover:bg-white/10 hover:text-white';
    $ikon = [
        'dashboard' =>
            'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'jenis' =>
            'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
        'tagihan' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
        'pembayaran' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'berkas' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
        'pengumuman' =>
            'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        'kalender' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'notifikasi' =>
            'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    ];
    // [kelompok, gate, route, pola route aktif, label, ikon]
    // Item otomatis disembunyikan jika route belum ada, butuh parameter, atau Gate menolak.
    $menu = [
        ['Utama', '', 'keuangan.dashboard', 'keuangan.dashboard', 'Dashboard Keuangan', 'dashboard'],
        ['Utama', 'akses-notifikasi', 'notifikasi.index', 'notifikasi.*', 'Notifikasi', 'notifikasi'],
        ['Utama', 'akses-pengumuman', 'pengumuman.index', 'pengumuman.*', 'Pengumuman', 'pengumuman'],
        ['Utama', 'akses-kalender', 'kalender.index', 'kalender.*', 'Kalender Akademik', 'kalender'],

        [
            'Keuangan',
            'akses-jenis-biaya',
            'keuangan.jenis-biaya.index',
            'keuangan.jenis-biaya.*',
            'Jenis Biaya',
            'jenis',
        ],
        ['Keuangan', 'akses-tagihan', 'tagihan.index', 'tagihan.*', 'Tagihan', 'tagihan'],
        ['Keuangan', 'akses-pembayaran', 'pembayaran.index', 'pembayaran.*', 'Verifikasi Pembayaran', 'pembayaran'],

        ['Arsip', 'akses-berkas', 'berkas.index', 'berkas.*', 'Berkas', 'berkas'],
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
            <img src="{{ asset($logoKampus) }}" alt="Logo ILYAS INSTITUT"
                class="h-10 w-10 shrink-0 rounded-lg bg-white object-cover shadow-sm ring-1 ring-white/20">
        @else
            <div
                class="flex h-8 w-8 items-center justify-center rounded bg-siakad-accent text-siakad-dark font-bold text-lg">
                S</div>
        @endif
        <div>
            <h1 class="text-sm font-bold tracking-wide text-white leading-tight">SIAKAD</h1>
            <p class="text-[10px] text-emerald-200">ADMIN KEUANGAN</p>
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
                {{ strtoupper(substr(auth()->user()->nama ?? 'K', 0, 1)) }}
            </div>
            <div class="overflow-hidden">
                <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->nama ?? 'Admin Keuangan' }}</p>
                <p class="text-xs text-emerald-300 truncate">Admin Keuangan</p>
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
