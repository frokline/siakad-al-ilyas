@extends('layouts.admin')

@section('title', 'Beranda Portal Mahasiswa')

@section('content')
    <!-- Flash Messages (Info) -->
    @if (session('info'))
        @include('partials._pesan', ['pesan' => session('info')])
    @endif

    <!-- Kepala Halaman -->
    <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <nav aria-label="Breadcrumb" class="mb-1">
                <ol class="flex items-center space-x-2 text-xs text-slate-500">
                    <li><span class="font-medium">Portal</span></li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">Beranda Mahasiswa</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Selamat datang, {{ $user->nama }}</h1>
            <p class="text-sm text-slate-500">Ringkasan informasi akademik dan akses layanan mahasiswa.</p>
        </div>
    </div>

    <!-- Konten Utama: Grid 3 Kolom -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Kolom Kiri: Konten Utama (2 span) -->
        <div class="space-y-6 lg:col-span-2">

            <!-- Kartu: Identitas Mahasiswa -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Identitas Mahasiswa</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Nama</dt>
                            <dd class="text-sm text-slate-900">{{ $user->nama }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">NIM</dt>
                            <dd class="text-sm text-slate-900">{{ $mahasiswa->nim }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Status Akun</dt>
                            <dd class="text-sm text-slate-900">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $user->status === 'aktif' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-50 text-slate-700 ring-slate-600/20' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Program Studi</dt>
                            <dd class="text-sm text-slate-900">
                                {{ $riwayatAktif?->kurikulum?->programStudi?->nama ?? 'Belum tersedia' }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Kurikulum</dt>
                            <dd class="text-sm text-slate-900">
                                {{ $riwayatAktif?->kurikulum?->nama ?? 'Belum tersedia' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Angkatan</dt>
                            <dd class="text-sm text-slate-900">{{ $riwayatAktif?->angkatan ?? 'Belum tersedia' }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Dosen Pembimbing Akademik</dt>
                            <dd class="text-sm text-slate-900">
                                {{ $riwayatAktif?->dosenPa?->user?->nama ?? 'Belum ditentukan' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Kartu: Status Semester -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Status Semester</h2>
                </div>
                <div class="p-6">
                    @if ($registrasiAktif)
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Periode Akademik</dt>
                                <dd class="text-sm text-slate-900">
                                    {{ $registrasiAktif->periodeAkademik?->kode ?? 'Belum tersedia' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Semester Studi</dt>
                                <dd class="text-sm text-slate-900">{{ $registrasiAktif->semester_studi }}</dd>
                            </div>
                            <div>
                                <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Status Registrasi</dt>
                                <dd class="text-sm text-slate-900">
                                    <span
                                        class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                        {{ \App\Models\RegistrasiSemester::STATUS[$registrasiAktif->status] ?? $registrasiAktif->status }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="mb-1.5 block text-sm font-semibold text-slate-700">Rombongan Belajar</dt>
                                <dd class="text-sm text-slate-900">
                                    {{ $registrasiAktif->rombel?->kode ?? 'Belum ditentukan' }}
                                </dd>
                            </div>
                        </dl>
                    @else
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                            Belum ada registrasi semester aktif untuk saat ini.
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Aside (1 span) -->
        <aside class="space-y-6">

            <!-- Kartu: Ringkasan Akademik (Panel Hijau) -->
            <div class="overflow-hidden rounded-2xl border border-siakad-dark bg-siakad-dark text-white shadow-sm">
                <div class="border-b border-emerald-800 px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-50">Ringkasan Akademik</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="flex items-center justify-between border-b border-emerald-800 pb-2">
                            <dt class="text-sm font-medium text-emerald-100">Jumlah Riwayat Studi</dt>
                            <dd class="text-base font-bold">{{ $jumlahRiwayat }}</dd>
                        </div>
                        <div class="flex items-center justify-between border-b border-emerald-800 pb-2">
                            <dt class="text-sm font-medium text-emerald-100">Jumlah Registrasi Semester</dt>
                            <dd class="text-base font-bold">{{ $jumlahRegistrasi }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-sm font-medium text-emerald-100">Status Studi Aktif</dt>
                            <dd class="text-base font-bold text-siakad-accent">
                                {{ $riwayatAktif ? \App\Models\RiwayatStudi::STATUS[$riwayatAktif->status] ?? $riwayatAktif->status : 'Tidak ada riwayat aktif' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Kartu: Layanan Cepat -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Layanan Cepat</h2>
                </div>
                <div class="p-2">
                    <nav aria-label="Layanan mahasiswa" class="flex flex-col space-y-1">
                        @if (Route::has('portal.profil.show'))
                            <a href="{{ route('portal.profil.show') }}"
                                class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-siakad-dark">
                                <span>Profil Mahasiswa</span>
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endif

                        @if (Route::has('portal.krs.index'))
                            <a href="{{ route('portal.krs.index') }}"
                                class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-siakad-dark">
                                <span>KRS Saya</span>
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endif

                        @if (Route::has('portal.jadwal.index'))
                            <a href="{{ route('portal.jadwal.index') }}"
                                class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-siakad-dark">
                                <span>Jadwal Kuliah</span>
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endif

                        @if (Route::has('portal.presensi.index'))
                            <a href="{{ route('portal.presensi.index') }}"
                                class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-siakad-dark">
                                <span>Presensi Saya</span>
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endif

                        @if (Route::has('berkas.index'))
                            <a href="{{ route('berkas.index') }}"
                                class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-siakad-dark">
                                <span>Berkas Saya</span>
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endif
                    </nav>
                </div>
            </div>

        </aside>
    </div>
@endsection
