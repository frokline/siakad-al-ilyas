@extends('layouts.admin')

@section('title', 'Beranda Portal Mahasiswa')

@section('content')
    <div class="space-y-6 pb-6 sm:space-y-8">

        {{-- Pesan informasi --}}
        @if (session('info'))
            @include('partials._pesan', ['pesan' => session('info')])
        @endif

        {{-- Hero / Sambutan --}}
        <section
            class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-800 px-6 py-8 text-white shadow-lg sm:px-8 lg:px-10">
            <div class="relative flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                {{-- Foto Profil dan Identitas Mahasiswa --}}
                <div class="flex min-w-0 items-start gap-3 sm:gap-4">

                    {{-- Foto Profil --}}
                    <div
                        class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/20 bg-white/10 shadow-inner sm:h-14 sm:w-14">

                        <img src="{{ route('portal.profil.foto') }}" alt="Foto profil {{ $user->nama }}"
                            class="absolute inset-0 h-full w-full object-cover" loading="eager"
                            onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden')">

                        {{-- Inisial jika foto tidak tersedia --}}
                        <span
                            class="hidden h-full w-full items-center justify-center text-lg font-bold text-white sm:text-xl"
                            aria-hidden="true">
                            {{ mb_strtoupper(mb_substr(trim($user->nama), 0, 1)) }}
                        </span>
                    </div>

                    {{-- Nama dan Sambutan --}}
                    <div class="min-w-0 flex-1">
                        <p class="mb-1 text-sm font-medium text-emerald-100">
                            Selamat datang kembali,
                        </p>

                        <h1 class="break-words text-xl font-bold leading-tight tracking-tight sm:text-3xl">
                            {{ $user->nama }}
                        </h1>

                        <p class="mt-2 break-words text-sm leading-relaxed text-emerald-100/80">
                            Kelola informasi studi, registrasi, jadwal, dan layanan akademik dari satu tempat.
                        </p>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto lg:flex-col">
                    @if (Route::has('portal.krs.index'))
                        <a href="{{ route('portal.krs.index') }}"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-bold text-emerald-950 shadow-sm transition duration-200 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-emerald-900 active:scale-[0.99] sm:w-auto">

                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 4h8a2 2 0 012 2v14l-6-3-6 3V6a2 2 0 012-2z" />
                            </svg>

                            Lihat KRS Saya

                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </a>
                    @endif

                    @if (Route::has('portal.jadwal.index'))
                        <a href="{{ route('portal.jadwal.index') }}"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white transition duration-200 hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/70 active:scale-[0.99] sm:w-auto">

                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="3" y="5" width="18" height="16" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 3v4M8 3v4M3 11h18" />
                            </svg>

                            Lihat Jadwal
                        </a>
                    @endif
                </div>
            </div>
        </section>

    {{-- Ringkasan akademik --}}
    <section aria-labelledby="ringkasan-title" class="space-y-3">
        <div>
            <h2 id="ringkasan-title" class="text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                Ringkasan Akademik
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Gambaran singkat data akademik Anda.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 sm:gap-4">

            {{-- Total riwayat studi --}}
            <article
                class="group min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-500">Riwayat Studi</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                            {{ $jumlahRiwayat }}
                        </p>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">
                            Total riwayat studi tercatat
                        </p>
                    </div>

                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-800 transition group-hover:bg-emerald-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 19.5A2.5 2.5 0 016.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" />
                        </svg>
                    </span>
                </div>
            </article>

            {{-- Total registrasi --}}
            <article
                class="group min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-500">Registrasi Semester</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                            {{ $jumlahRegistrasi }}
                        </p>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">
                            Total registrasi yang tercatat
                        </p>
                    </div>

                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-800 transition group-hover:bg-blue-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="17" rx="2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18m-13 4h3m-3 3h6" />
                        </svg>
                    </span>
                </div>
            </article>

            {{-- Status studi --}}
            <article
                class="group min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-md sm:col-span-2 sm:p-5 xl:col-span-1">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-slate-500">Status Studi</p>

                        <div class="mt-2">
                            @if ($riwayatAktif)
                                <span
                                    class="inline-flex max-w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold ring-1 ring-inset
                                        {{ $riwayatAktif->status === 'aktif'
                                            ? 'bg-emerald-50 text-emerald-800 ring-emerald-600/20'
                                            : 'bg-amber-50 text-amber-800 ring-amber-600/20' }}">
                                    <span
                                        class="h-2 w-2 shrink-0 rounded-full
                                            {{ $riwayatAktif->status === 'aktif' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    <span class="break-words">
                                        {{ \App\Models\RiwayatStudi::STATUS[$riwayatAktif->status] ?? $riwayatAktif->status }}
                                    </span>
                                </span>
                            @else
                                <span
                                    class="inline-flex rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                                    Tidak ada riwayat aktif
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-xs leading-relaxed text-slate-500">
                            Status pada riwayat studi aktif
                        </p>
                    </div>

                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-800 transition group-hover:bg-amber-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                        </svg>
                    </span>
                </div>
            </article>

        </div>
    </section>

    {{-- Identitas dan status semester --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-5 xl:gap-6">

        {{-- Identitas mahasiswa --}}
        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-3">
            <div
                class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-emerald-50/90 to-white px-4 py-4 sm:px-6">
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-900">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-900 sm:text-base">Identitas Mahasiswa</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Informasi profil akademik Anda</p>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">

                    <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                        <dt class="text-xs font-semibold text-slate-500">Nama Lengkap</dt>
                        <dd class="mt-1.5 break-words text-sm font-semibold leading-relaxed text-slate-900">
                            {{ $user->nama }}
                        </dd>
                    </div>

                    <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                        <dt class="text-xs font-semibold text-slate-500">Nomor Induk Mahasiswa</dt>
                        <dd class="mt-1.5 break-all font-mono text-sm font-bold text-slate-900">
                            {{ $mahasiswa->nim }}
                        </dd>
                    </div>

                    <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                        <dt class="text-xs font-semibold text-slate-500">Program Studi</dt>
                        <dd class="mt-1.5 break-words text-sm font-semibold leading-relaxed text-slate-900">
                            {{ $riwayatAktif?->kurikulum?->programStudi?->nama ?? 'Belum tersedia' }}
                        </dd>
                    </div>

                    <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                        <dt class="text-xs font-semibold text-slate-500">Angkatan</dt>
                        <dd class="mt-1.5 text-sm font-semibold text-slate-900">
                            {{ $riwayatAktif?->angkatan ?? 'Belum tersedia' }}
                        </dd>
                    </div>

                    <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 sm:col-span-2">
                        <dt class="text-xs font-semibold text-slate-500">Kurikulum</dt>
                        <dd class="mt-1.5 break-words text-sm font-semibold leading-relaxed text-slate-900">
                            {{ $riwayatAktif?->kurikulum?->nama ?? 'Belum tersedia' }}
                        </dd>
                    </div>

                    <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 sm:col-span-2">
                        <dt class="text-xs font-semibold text-slate-500">Dosen Pembimbing Akademik</dt>
                        <dd class="mt-1.5 break-words text-sm font-semibold leading-relaxed text-slate-900">
                            {{ $riwayatAktif?->dosenPa?->user?->nama ?? 'Belum ditentukan' }}
                        </dd>
                    </div>

                </dl>

                @if (Route::has('portal.profil.show'))
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <a href="{{ route('portal.profil.show') }}"
                            class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700/30 sm:w-auto">
                            Lihat Profil Lengkap
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </section>

        {{-- Status semester --}}
        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div
                class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-blue-50/80 to-white px-4 py-4 sm:px-6">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-900">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="17" rx="2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-900 sm:text-base">Status Semester</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Informasi registrasi aktif</p>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                @if ($registrasiAktif)
                    <dl class="space-y-3">

                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                            <dt class="text-xs font-semibold text-slate-500">Periode Akademik</dt>
                            <dd class="mt-1.5 break-words text-sm font-bold text-slate-900">
                                {{ $registrasiAktif->periodeAkademik?->kode ?? 'Belum tersedia' }}
                            </dd>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                                <dt class="text-xs font-semibold leading-relaxed text-slate-500">Semester Studi</dt>
                                <dd class="mt-1.5 text-xl font-bold text-slate-900">
                                    {{ $registrasiAktif->semester_studi }}
                                </dd>
                            </div>

                            <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                                <dt class="text-xs font-semibold leading-relaxed text-slate-500">Status Registrasi</dt>
                                <dd class="mt-2">
                                    <span
                                        class="inline-flex max-w-full break-words rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-800 ring-1 ring-inset ring-emerald-600/20">
                                        {{ \App\Models\RegistrasiSemester::STATUS[$registrasiAktif->status] ?? $registrasiAktif->status }}
                                    </span>
                                </dd>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                            <dt class="text-xs font-semibold text-slate-500">Rombongan Belajar</dt>
                            <dd class="mt-1.5 break-words text-sm font-bold text-slate-900">
                                {{ $registrasiAktif->rombel?->kode ?? 'Belum ditentukan' }}
                            </dd>
                        </div>

                    </dl>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center">
                        <span
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <rect x="3" y="4" width="18" height="17" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18" />
                            </svg>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-slate-800">
                            Belum ada registrasi aktif
                        </p>
                        <p class="mx-auto mt-1 max-w-xs text-xs leading-relaxed text-slate-500">
                            Informasi semester akan muncul di sini setelah registrasi aktif tersedia.
                        </p>
                    </div>
                @endif
            </div>
        </section>

    </div>

    {{-- Layanan cepat --}}
    <section aria-labelledby="layanan-title" class="space-y-3">
        <div>
            <h2 id="layanan-title" class="text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                Layanan Mahasiswa
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Akses cepat ke layanan akademik dan dokumen Anda.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 sm:gap-4">

            @if (Route::has('portal.profil.show'))
                <a href="{{ route('portal.profil.show') }}"
                    class="group flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-700/30 active:scale-[0.99] sm:p-5">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-800 transition group-hover:bg-emerald-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block break-words text-sm font-bold text-slate-900">Profil Mahasiswa</span>
                        <span class="mt-1 block text-xs leading-relaxed text-slate-500">Identitas dan biodata
                            Anda</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-emerald-800"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            @endif

            @if (Route::has('portal.krs.index'))
                <a href="{{ route('portal.krs.index') }}"
                    class="group flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-700/30 active:scale-[0.99] sm:p-5">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-800 transition group-hover:bg-blue-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 4h8a2 2 0 012 2v14l-6-3-6 3V6a2 2 0 012-2z" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block break-words text-sm font-bold text-slate-900">KRS Saya</span>
                        <span class="mt-1 block text-xs leading-relaxed text-slate-500">Mata kuliah dan status
                            KRS</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-blue-800"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            @endif

            @if (Route::has('portal.jadwal.index'))
                <a href="{{ route('portal.jadwal.index') }}"
                    class="group flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-violet-700/30 active:scale-[0.99] sm:p-5">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-800 transition group-hover:bg-violet-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="17" rx="2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block break-words text-sm font-bold text-slate-900">Jadwal Kuliah</span>
                        <span class="mt-1 block text-xs leading-relaxed text-slate-500">Waktu dan kelas
                            perkuliahan</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-violet-800"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            @endif

            @if (Route::has('portal.presensi.index'))
                <a href="{{ route('portal.presensi.index') }}"
                    class="group flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-700/30 active:scale-[0.99] sm:p-5">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-800 transition group-hover:bg-amber-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block break-words text-sm font-bold text-slate-900">Presensi Saya</span>
                        <span class="mt-1 block text-xs leading-relaxed text-slate-500">Pantau riwayat kehadiran</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-amber-800"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            @endif

            @if (Route::has('berkas.index'))
                <a href="{{ route('berkas.index') }}"
                    class="group flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-rose-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-rose-700/30 active:scale-[0.99] sm:p-5">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-800 transition group-hover:bg-rose-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6M8 13h8M8 17h8" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block break-words text-sm font-bold text-slate-900">Berkas Saya</span>
                        <span class="mt-1 block text-xs leading-relaxed text-slate-500">Akses dokumen dan berkas</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-rose-800"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            @endif

        </div>
    </section>

    {{-- Catatan --}}
    <footer class="border-t border-slate-200 pt-4">
        <p class="text-center text-xs leading-relaxed text-slate-500">
            Portal Mahasiswa &middot; Informasi ditampilkan berdasarkan data akademik yang tercatat pada sistem.
        </p>
    </footer>

    </div>
@endsection
