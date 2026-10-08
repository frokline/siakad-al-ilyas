<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-siakad-dark text-white transition-transform duration-300 lg:static lg:translate-x-0 shadow-2xl">
    <div class="flex h-16 shrink-0 items-center px-6 gap-3">
        {{-- Logo kampus: public/images/logo-ilyas-institut.png. Jika file belum ada, tampil ikon lama. --}}
        @php
            $logoKampus = 'images/logo-ilyas-institut.png';
        @endphp
        @if (is_file(public_path($logoKampus)))
            <img src="{{ asset($logoKampus) }}" alt="Logo Ilyas Institute"
                class="h-10 w-10 shrink-0 rounded-lg bg-white object-cover shadow-sm ring-1 ring-white/20">
        @else
            <div
                class="flex h-8 w-8 items-center justify-center rounded bg-siakad-accent text-siakad-dark font-bold text-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
            </div>
        @endif
        <div>
            <h1 class="text-sm font-bold tracking-wide text-white leading-tight">SIAKAD</h1>
            <p class="text-[10px] text-emerald-200">ILYAS INSTITUTE</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
        <a href="{{ url('admin') }}"
            class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->is('admin') ? 'bg-siakad-active text-white font-semibold' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM14 13a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
            </svg>
            <span class="text-sm">Dashboard</span>
        </a>

        <!-- Akun & Akses Dropdown -->
        <div x-data="{ open: {{ request()->routeIs('admin.users*', 'admin.roles*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-emerald-100 transition-colors hover:bg-white/10 hover:text-white">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-sm">Akun & Akses</span>
                </div>
                <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-3 pt-1 space-y-1">
                <a href="{{ route('admin.users.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.users*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Pengguna
                    Sistem</a>
                <a href="{{ route('admin.roles.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.roles*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Peran
                    Pengguna</a>
            </div>
        </div>

        <!-- Master Akademik Dropdown -->
        <div x-data="{ open: {{ request()->routeIs('admin.program-studi*', 'admin.periode-akademik*', 'admin.kurikulum*', 'admin.mata-kuliah*', 'admin.mahasiswa*', 'admin.dosen*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-emerald-100 transition-colors hover:bg-white/10 hover:text-white">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="text-sm">Master Akademik</span>
                </div>
                <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-3 pt-1 space-y-1">
                <a href="{{ route('admin.program-studi.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.program-studi*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Program
                    Studi</a>
                <a href="{{ route('admin.periode-akademik.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.periode-akademik*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Periode
                    Akademik</a>
                <a href="{{ route('admin.kurikulum.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.kurikulum*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Kurikulum</a>
                <a href="{{ route('admin.mata-kuliah.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.mata-kuliah*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Mata
                    Kuliah</a>
                <a href="{{ route('admin.mahasiswa.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.mahasiswa*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Biodata
                    Mahasiswa</a>
                <a href="{{ route('admin.dosen.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.dosen*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Biodata
                    Dosen</a>
            </div>
        </div>

        <!-- Perkuliahan Dropdown -->
        <div x-data="{ open: {{ request()->routeIs('admin.paket-semester*', 'admin.rombel*', 'admin.kelas-kuliah*', 'admin.pengajar-kelas*', 'admin.jadwal-kuliah*', 'admin.krs*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-emerald-100 transition-colors hover:bg-white/10 hover:text-white">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-sm">Perkuliahan</span>
                </div>
                <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-3 pt-1 space-y-1">
                <a href="{{ route('admin.paket-semester.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.paket-semester*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Paket
                    Semester</a>
                <a href="{{ route('admin.rombel.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.rombel*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Rombongan
                    Belajar</a>
                <a href="{{ route('admin.kelas-kuliah.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.kelas-kuliah*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Kelas
                    Kuliah</a>
                <a href="{{ route('admin.pengajar-kelas.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.pengajar-kelas*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Dosen
                    Pengajar</a>
                <a href="{{ route('admin.jadwal-kuliah.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.jadwal-kuliah*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Jadwal
                    Perkuliahan</a>
                <a href="{{ route('admin.krs.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.krs*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">KRS
                    Mahasiswa</a>
            </div>
        </div>

        <!-- Pemantauan Dropdown -->
        <div x-data="{ open: {{ request()->routeIs('admin.pertemuan*') || request()->routeIs('presensi.*') || request()->routeIs('kalender.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-emerald-100 transition-colors hover:bg-white/10 hover:text-white">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-sm">Pemantauan</span>
                </div>
                <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-3 pt-1 space-y-1">
                <a href="{{ route('admin.pertemuan.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('admin.pertemuan*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Sesi
                    Pertemuan</a>
                <a href="{{ route('presensi.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('presensi.*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Rekap
                    Presensi</a>
                <a href="{{ route('kalender.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('kalender.*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Kalender
                    Akademik</a>
                <a href="{{ route('pengumuman.index') }}"
                    class="block rounded-md py-2 text-xs {{ request()->routeIs('pengumuman.*') ? 'text-siakad-accent font-semibold' : 'text-emerald-200 hover:text-white' }}">Pengumuman</a>
            </div>
        </div>
    </nav>

    <div class="p-4 border-t border-white/10 bg-siakad-dark">
        <div class="flex items-center gap-3 mb-4">
            <div
                class="h-10 w-10 shrink-0 rounded-full bg-slate-300 overflow-hidden border border-emerald-500 flex items-center justify-center text-siakad-dark font-bold">
                {{ strtoupper(substr(auth()->user()->nama ?? 'A', 0, 1)) }}
            </div>
            <div class="overflow-hidden">
                <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->nama ?? 'Administrator' }}</p>
                <p class="text-xs text-emerald-300 truncate">NIP: {{ auth()->user()->id ?? 'Admin' }}</p>
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
