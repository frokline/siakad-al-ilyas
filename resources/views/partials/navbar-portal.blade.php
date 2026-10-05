@php
    $user = auth('web')->user();
    $peran = $user ? $user->roles()->pluck('kode') : collect();

    $dosen = $peran->contains('dosen');
    $mahasiswa = $peran->contains('mahasiswa');
    $adminAkademik = $peran->contains('admin_akademik');
    $adminKeuangan = $peran->contains('admin_keuangan');
@endphp

<nav class="flex flex-col md:flex-row md:items-center md:justify-end gap-4 py-3 md:py-0 w-full"
    aria-label="Navigasi portal">

    <!-- Area Tautan Menu -->
    <div class="flex flex-wrap items-center gap-2 md:gap-3 text-sm font-medium">
        @if ($dosen)
            @if (Route::has('portal.dosen.kelas.index'))
                <a href="{{ route('portal.dosen.kelas.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('portal.dosen.kelas*') ? 'bg-siakad-active text-white font-semibold shadow-sm' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    Kelas Saya
                </a>
            @endif

            @if (Route::has('kegiatan.kelas'))
                <a href="{{ route('kegiatan.kelas') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('kegiatan*') ? 'bg-siakad-active text-white font-semibold shadow-sm' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    Pembelajaran
                </a>
            @endif

            @if (Route::has('presensi.index'))
                <a href="{{ route('presensi.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('presensi*') ? 'bg-siakad-active text-white font-semibold shadow-sm' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    Presensi
                </a>
            @endif

            @if (Route::has('notifikasi.index'))
                <a href="{{ route('notifikasi.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors {{ request()->routeIs('notifikasi*') ? 'bg-siakad-active text-white font-semibold shadow-sm' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                    Notifikasi
                </a>
            @endif
        @elseif ($mahasiswa)
            @if (Route::has('portal.mahasiswa.index'))
                <a href="{{ route('portal.mahasiswa.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">Beranda</a>
            @endif
            @if (Route::has('portal.profil.show'))
                <a href="{{ route('portal.profil.show') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">Profil
                    Saya</a>
            @endif
            @if (Route::has('portal.krs.index'))
                <a href="{{ route('portal.krs.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">KRS
                    Saya</a>
            @endif
            @if (Route::has('portal.jadwal.index'))
                <a href="{{ route('portal.jadwal.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">Jadwal
                    Saya</a>
            @endif
            @if (Route::has('portal.presensi.index'))
                <a href="{{ route('portal.presensi.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">Riwayat
                    Presensi</a>
            @endif
            @if (Route::has('kegiatan.index'))
                <a href="{{ route('kegiatan.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">Pembelajaran</a>
            @endif
            @if (Route::has('berkas.index'))
                <a href="{{ route('berkas.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">Berkas
                    Saya</a>
            @endif
            @if (Route::has('pembayaran.index'))
                <a href="{{ route('pembayaran.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">Pembayaran
                    SPP</a>
            @endif
            @if (Route::has('notifikasi.index'))
                <a href="{{ route('notifikasi.index') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">Notifikasi</a>
            @endif
        @elseif ($adminAkademik)
            @if (Route::has('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">Dashboard
                    Admin</a>
            @endif
        @elseif ($adminKeuangan)
            @if (Route::has('keuangan.dashboard'))
                <a href="{{ route('keuangan.dashboard') }}"
                    class="whitespace-nowrap px-3 py-2 rounded-lg transition-colors hover:bg-white/10 text-emerald-100 hover:text-white">Dashboard
                    Keuangan</a>
            @endif
        @endif
    </div>

    <!-- Area Profil Pengguna & Tombol Keluar -->
    @if ($user)
        <div class="flex flex-shrink-0 items-center gap-3 pt-3 md:pt-0 border-t border-white/10 md:border-t-0 md:ml-4">
            <span class="text-sm font-semibold text-white tracking-wide whitespace-nowrap">{{ $user->nama }}</span>

            @if (Route::has('logout'))
                <form method="post" action="{{ route('logout') }}" class="inline m-0 p-0">
                    @csrf
                    <button type="submit"
                        class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-siakad-dark shadow-sm transition-all hover:bg-emerald-50 hover:shadow">
                        Keluar
                    </button>
                </form>
            @endif
        </div>
    @endif
</nav>
