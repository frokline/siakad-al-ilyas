@extends('layouts.admin')

@section('title', 'Beranda Portal Mahasiswa')

@section('content')
    <div class="space-y-6 pb-6 sm:space-y-7">
        @if (session('info'))
            @include('partials._pesan', ['pesan' => session('info')])
        @endif

        {{-- Judul halaman --}}
        <header class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#087454]">Portal Mahasiswa</p>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Beranda</h1>
            <p class="text-sm leading-relaxed text-slate-500">Ringkasan akademik dan akses cepat ke layanan kampus.</p>
        </header>

        {{-- Sambutan: satu warna hijau yang sama dengan sidebar --}}
        <section aria-label="Sambutan mahasiswa"
            class="overflow-hidden rounded-2xl bg-[#065f46] px-4 py-5 text-white sm:px-6 sm:py-6 lg:px-7">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                    <div
                        class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/25 bg-white/10 sm:h-14 sm:w-14">
                        <img src="{{ route('portal.profil.foto') }}" alt="Foto profil {{ $user->nama }}"
                            class="absolute inset-0 h-full w-full object-cover" loading="eager"
                            onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden')">
                        <span
                            class="hidden h-full w-full items-center justify-center text-lg font-bold text-white sm:text-xl"
                            aria-hidden="true">
                            {{ mb_strtoupper(mb_substr(trim($user->nama), 0, 1)) }}
                        </span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-emerald-100">Selamat datang,</p>
                        <h2 class="mt-0.5 break-words text-xl font-bold leading-tight sm:text-2xl">{{ $user->nama }}</h2>
                        <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-emerald-50/90">
                            Informasi studi dan layanan akademik Anda tersedia dari halaman ini.
                        </p>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-44">
                    @if (Route::has('portal.biodata.show'))
                        <a href="{{ route('portal.biodata.show') }}"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-[#065f46] transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-[#065f46]">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 3.5a2.1 2.1 0 013 3L9 17l-4 1 1-4 10.5-10.5z" />
                            </svg>
                            Buka Biodata
                        </a>
                    @endif
                    @if (Route::has('portal.jadwal.index'))
                        <a href="{{ route('portal.jadwal.index') }}"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-white/30 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/70">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="16" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 3v4M8 3v4M3 11h18" />
                            </svg>
                            Lihat Jadwal
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- Ringkasan singkat --}}
        <section aria-labelledby="ringkasan-title" class="space-y-3">
            <div>
                <h2 id="ringkasan-title" class="text-base font-bold text-slate-900 sm:text-lg">Ringkasan akademik</h2>
                <p class="mt-1 text-sm text-slate-500">Status dan data utama yang tercatat di sistem.</p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                <article class="min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
                    <p class="text-xs font-medium leading-relaxed text-slate-500 sm:text-sm">Riwayat studi</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $jumlahRiwayat }}</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">Data studi tercatat</p>
                </article>

                <article class="min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
                    <p class="text-xs font-medium leading-relaxed text-slate-500 sm:text-sm">Registrasi semester</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $jumlahRegistrasi }}</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">Total registrasi tercatat</p>
                </article>

                <article class="col-span-2 min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:col-span-1 sm:p-5">
                    <p class="text-xs font-medium leading-relaxed text-slate-500 sm:text-sm">Status studi</p>
                    <div class="mt-2">
                        @if ($riwayatAktif)
                            <span
                                class="inline-flex max-w-full items-center gap-2 rounded-md bg-emerald-50 px-2.5 py-1.5 text-sm font-semibold text-[#065f46] ring-1 ring-inset ring-emerald-700/20">
                                <span
                                    class="h-2 w-2 shrink-0 rounded-full {{ $riwayatAktif->status === 'aktif' ? 'bg-emerald-600' : 'bg-amber-500' }}"></span>
                                <span
                                    class="break-words">{{ \App\Models\RiwayatStudi::STATUS[$riwayatAktif->status] ?? $riwayatAktif->status }}</span>
                            </span>
                        @else
                            <span
                                class="inline-flex rounded-md bg-slate-100 px-2.5 py-1.5 text-sm font-medium text-slate-600">Tidak
                                ada riwayat aktif</span>
                        @endif
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500">Mengikuti riwayat studi aktif</p>
                </article>
            </div>
        </section>

        {{-- Identitas dan registrasi semester --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-5 xl:gap-5">
            <section aria-labelledby="identitas-title"
                class="min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-white xl:col-span-3">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-4 sm:px-5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-[#065f46]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h2 id="identitas-title" class="font-bold text-slate-900">Identitas mahasiswa</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Informasi dasar akademik</p>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="min-w-0 rounded-lg bg-slate-50 p-3.5">
                            <dt class="text-xs font-medium text-slate-500">Nama lengkap</dt>
                            <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">{{ $user->nama }}</dd>
                        </div>
                        <div class="min-w-0 rounded-lg bg-slate-50 p-3.5">
                            <dt class="text-xs font-medium text-slate-500">Nomor induk mahasiswa</dt>
                            <dd class="mt-1.5 break-all font-mono text-sm font-semibold text-slate-900">
                                {{ $mahasiswa->nim }}</dd>
                        </div>
                        <div class="min-w-0 rounded-lg bg-slate-50 p-3.5">
                            <dt class="text-xs font-medium text-slate-500">Program studi</dt>
                            <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">
                                {{ $riwayatAktif?->kurikulum?->programStudi?->nama ?? 'Belum tersedia' }}</dd>
                        </div>
                        <div class="min-w-0 rounded-lg bg-slate-50 p-3.5">
                            <dt class="text-xs font-medium text-slate-500">Angkatan</dt>
                            <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">
                                {{ $riwayatAktif?->angkatan ?? 'Belum tersedia' }}</dd>
                        </div>
                        <div class="min-w-0 rounded-lg bg-slate-50 p-3.5 sm:col-span-2">
                            <dt class="text-xs font-medium text-slate-500">Kurikulum</dt>
                            <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">
                                {{ $riwayatAktif?->kurikulum?->nama ?? 'Belum tersedia' }}</dd>
                        </div>
                        <div class="min-w-0 rounded-lg bg-slate-50 p-3.5 sm:col-span-2">
                            <dt class="text-xs font-medium text-slate-500">Dosen pembimbing akademik</dt>
                            <dd class="mt-1.5 break-words text-sm font-semibold text-slate-900">
                                {{ $riwayatAktif?->dosenPa?->user?->nama ?? 'Belum ditentukan' }}</dd>
                        </div>
                    </dl>

                    @if (Route::has('portal.profil.show'))
                        <div class="mt-4 border-t border-slate-100 pt-4">
                            <a href="{{ route('portal.profil.show') }}"
                                class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-700/30 hover:bg-emerald-50 hover:text-[#065f46] focus:outline-none focus:ring-2 focus:ring-emerald-700/30 sm:w-auto">
                                Lihat profil lengkap
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </section>

            <section aria-labelledby="semester-title"
                class="min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-white xl:col-span-2">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-4 sm:px-5">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-[#065f46]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="17" rx="2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h2 id="semester-title" class="font-bold text-slate-900">Registrasi semester</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Informasi registrasi aktif</p>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    @if ($registrasiAktif)
                        <dl class="space-y-3">
                            <div class="rounded-lg bg-slate-50 p-3.5">
                                <dt class="text-xs font-medium text-slate-500">Periode akademik</dt>
                                <dd class="mt-1.5 break-words text-sm font-bold text-slate-900">
                                    {{ $registrasiAktif->periodeAkademik?->kode ?? 'Belum tersedia' }}</dd>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="min-w-0 rounded-lg bg-slate-50 p-3.5">
                                    <dt class="text-xs font-medium text-slate-500">Semester studi</dt>
                                    <dd class="mt-1.5 text-xl font-bold text-slate-900">
                                        {{ $registrasiAktif->semester_studi }}</dd>
                                </div>
                                <div class="min-w-0 rounded-lg bg-slate-50 p-3.5">
                                    <dt class="text-xs font-medium text-slate-500">Status registrasi</dt>
                                    <dd class="mt-2">
                                        <span
                                            class="inline-flex max-w-full break-words rounded-md bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-[#065f46] ring-1 ring-inset ring-emerald-700/20">
                                            {{ \App\Models\RegistrasiSemester::STATUS[$registrasiAktif->status] ?? $registrasiAktif->status }}
                                        </span>
                                    </dd>
                                </div>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3.5">
                                <dt class="text-xs font-medium text-slate-500">Rombongan belajar</dt>
                                <dd class="mt-1.5 break-words text-sm font-bold text-slate-900">
                                    {{ $registrasiAktif->rombel?->kode ?? 'Belum ditentukan' }}</dd>
                            </div>
                        </dl>
                    @else
                        <div class="rounded-lg border border-dashed border-slate-300 px-4 py-8 text-center">
                            <span
                                class="mx-auto flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-[#065f46]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="17" rx="2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18" />
                                </svg>
                            </span>
                            <p class="mt-3 text-sm font-semibold text-slate-800">Belum ada registrasi aktif</p>
                            <p class="mx-auto mt-1 max-w-xs text-xs leading-relaxed text-slate-500">Informasi semester akan
                                muncul setelah registrasi aktif tersedia.</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        {{-- Akses cepat: layanan yang paling sering dibutuhkan --}}
        <section aria-labelledby="layanan-title" class="space-y-3">
            <div>
                <h2 id="layanan-title" class="text-base font-bold text-slate-900 sm:text-lg">Akses cepat</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih layanan yang ingin Anda buka.</p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @if (Route::has('portal.krs.index'))
                    <a href="{{ route('portal.krs.index') }}"
                        class="group flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-emerald-700/30 hover:bg-emerald-50/40 focus:outline-none focus:ring-2 focus:ring-emerald-700/30">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-[#065f46]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 4h8a2 2 0 012 2v14l-6-3-6 3V6a2 2 0 012-2z" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-slate-900">KRS
                                Saya</span><span class="mt-0.5 block text-xs leading-relaxed text-slate-500">Mata kuliah
                                dan KRS</span></span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-[#065f46]"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                @endif

                @if (Route::has('portal.jadwal.index'))
                    <a href="{{ route('portal.jadwal.index') }}"
                        class="group flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-emerald-700/30 hover:bg-emerald-50/40 focus:outline-none focus:ring-2 focus:ring-emerald-700/30">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-[#065f46]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="17" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-slate-900">Jadwal
                                kuliah</span><span class="mt-0.5 block text-xs leading-relaxed text-slate-500">Waktu dan
                                kelas</span></span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-[#065f46]"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                @endif

                @if (Route::has('portal.presensi.index'))
                    <a href="{{ route('portal.presensi.index') }}"
                        class="group flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-emerald-700/30 hover:bg-emerald-50/40 focus:outline-none focus:ring-2 focus:ring-emerald-700/30">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-[#065f46]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1"><span
                                class="block text-sm font-semibold text-slate-900">Presensi</span><span
                                class="mt-0.5 block text-xs leading-relaxed text-slate-500">Riwayat kehadiran</span></span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-[#065f46]"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                @endif

                @if (Route::has('berkas.index'))
                    <a href="{{ route('berkas.index') }}"
                        class="group flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-emerald-700/30 hover:bg-emerald-50/40 focus:outline-none focus:ring-2 focus:ring-emerald-700/30">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-[#065f46]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6M8 13h8M8 17h8" />
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-slate-900">Berkas
                                saya</span><span class="mt-0.5 block text-xs leading-relaxed text-slate-500">Dokumen
                                mahasiswa</span></span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-[#065f46]"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                @endif
            </div>
        </section>

        <footer class="border-t border-slate-200 pt-4">
            <p class="text-center text-xs leading-relaxed text-slate-500">Portal Mahasiswa · Informasi ditampilkan
                berdasarkan data akademik yang tercatat pada sistem.</p>
        </footer>
    </div>
@endsection
