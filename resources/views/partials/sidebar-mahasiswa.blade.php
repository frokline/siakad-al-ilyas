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
    ];
    // [gate, route index, pola route aktif, label, ikon]
    $menu = [
        ['', 'portal.mahasiswa.index', 'portal.mahasiswa.*', 'Beranda', 'beranda'],
        ['', 'portal.profil.show', 'portal.profil.*', 'Profil Saya', 'profil'],
        ['', 'portal.krs.index', 'portal.krs.*', 'KRS Saya', 'krs'],
        ['', 'portal.jadwal.index', 'portal.jadwal.*', 'Jadwal Saya', 'jadwal'],
        ['', 'portal.presensi.index', 'portal.presensi.*', 'Riwayat Presensi', 'presensi'],
        ['akses-kegiatan', 'kegiatan.index', ['kegiatan.*', 'pengumpulan.*'], 'Tugas & Kegiatan', 'kegiatan'],
        ['akses-berkas', 'berkas.index', 'berkas.*', 'Berkas', 'berkas'],
    ];
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

    <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
        @foreach ($menu as [$gate, $rute, $pola, $label, $kunci])
            @continue(!Route::has($rute))
            @continue($gate !== '' && !Gate::allows($gate))
            <a href="{{ route($rute) }}" @if (request()->routeIs(...(array) $pola)) aria-current="page" @endif
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs(...(array) $pola) ? $menuAktif : $menuBiasa }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon[$kunci] }}" />
                </svg>
                <span class="text-sm">{{ $label }}</span>
            </a>
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
