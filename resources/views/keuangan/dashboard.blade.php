@extends('layouts.admin')

@section('title', 'Dashboard Admin Keuangan')

@section('content')
    <!-- HEADING TITLE & WELCOME BANNER -->
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Administrasi Keuangan</p>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard Admin Keuangan</h1>
            <p class="text-sm text-slate-500 mt-1">
                Selamat datang kembali, <span class="font-semibold text-slate-700">{{ $user->nama }}</span>. Berikut
                ringkasan statistik tagihan dan pembayaran mahasiswa.
            </p>
        </div>
    </div>

    <!-- 4 STATS CARDS HIGHLIGHT -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">

        <!-- Card 1: Verifikasi Pembayaran (Alert jika ada) -->
        <div
            class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 flex flex-col justify-between relative overflow-hidden">
            <div class="flex justify-between items-start">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Perlu Verifikasi</p>
                @if (($ringkasan['pembayaran_menunggu'] ?? 0) > 0)
                    <span class="relative flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                    </span>
                @else
                    <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                @endif
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div>
                    <p
                        class="text-3xl font-bold {{ ($ringkasan['pembayaran_menunggu'] ?? 0) > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                        {{ $ringkasan['pembayaran_menunggu'] }}
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5">Pembayaran Menunggu</p>
                </div>
                @can('akses-pembayaran')
                    @if (Route::has('pembayaran.index'))
                        <a href="{{ route('pembayaran.index', ['status' => \App\Models\Pembayaran::MENUNGGU]) }}"
                            class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                            Verifikasi &rarr;
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- Card 2: Tagihan Terbit -->
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Tagihan Terbit</p>
                <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-3xl font-bold text-slate-800">{{ $ringkasan['tagihan_terbit'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Aktif Ditagihkan</p>
            </div>
        </div>

        <!-- Card 3: Total Nominal Terbit -->
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Nominal Tagihan</p>
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-xl font-bold text-slate-800 truncate"
                    title="Rp{{ number_format($ringkasan['nominal_tagihan_terbit'], 0, ',', '.') }}">
                    Rp{{ number_format($ringkasan['nominal_tagihan_terbit'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-500 mt-0.5">Seluruh Tagihan Terbit</p>
            </div>
        </div>

        <!-- Card 4: Total Pembayaran Diterima -->
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Pemasukan Diterima</p>
                <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-xl font-bold text-emerald-700 truncate"
                    title="Rp{{ number_format($ringkasan['nominal_pembayaran_diterima'], 0, ',', '.') }}">
                    Rp{{ number_format($ringkasan['nominal_pembayaran_diterima'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $ringkasan['pembayaran_diterima'] }} Transaksi Disetujui</p>
            </div>
        </div>

    </div>

    <!-- MAIN GRID 2 KOLOM (RINGKASAN TAGIHAN & PEMBAYARAN) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        <!-- Widget Kiri: Ringkasan Tagihan -->
        <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Ringkasan Tagihan
                </h2>
                @can('akses-tagihan')
                    @if (Route::has('tagihan.index'))
                        <a href="{{ route('tagihan.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                            Semua Tagihan &rarr;
                        </a>
                    @endif
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Status / Data</th>
                            <th scope="col" class="px-4 py-3 font-medium text-center">Jumlah</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">Jenis Biaya Aktif</td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                {{ $ringkasan['jenis_biaya_aktif'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @if (Route::has('keuangan.jenis-biaya.index'))
                                    <a href="{{ route('keuangan.jenis-biaya.index') }}"
                                        class="text-xs font-bold text-emerald-600 hover:underline">
                                        Kelola
                                    </a>
                                @endif
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                    Tagihan Draf
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                {{ $ringkasan['tagihan_draf'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('akses-tagihan')
                                    @if (Route::has('tagihan.index'))
                                        <a href="{{ route('tagihan.index', ['status' => \App\Models\Tagihan::DRAF]) }}"
                                            class="text-xs font-semibold text-emerald-600 hover:underline">
                                            Lihat
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-300">&mdash;</span>
                                @endcan
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                    Tagihan Terbit
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                {{ $ringkasan['tagihan_terbit'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('akses-tagihan')
                                    @if (Route::has('tagihan.index'))
                                        <a href="{{ route('tagihan.index', ['status' => \App\Models\Tagihan::TERBIT]) }}"
                                            class="text-xs font-semibold text-emerald-600 hover:underline">
                                            Lihat
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-300">&mdash;</span>
                                @endcan
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                    Tagihan Dibatalkan
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                {{ $ringkasan['tagihan_dibatalkan'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('akses-tagihan')
                                    @if (Route::has('tagihan.index'))
                                        <a href="{{ route('tagihan.index', ['status' => \App\Models\Tagihan::DIBATALKAN]) }}"
                                            class="text-xs font-semibold text-emerald-600 hover:underline">
                                            Lihat
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-300">&mdash;</span>
                                @endcan
                            </td>
                        </tr>

                        <tr class="bg-slate-50 font-semibold">
                            <td class="px-4 py-3 text-slate-800">Total Nominal Terbit</td>
                            <td class="px-4 py-3 text-center text-emerald-700" colspan="2">
                                Rp{{ number_format($ringkasan['nominal_tagihan_terbit'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Widget Kanan: Ringkasan Pembayaran -->
        <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Ringkasan Pembayaran
                </h2>
                @can('akses-pembayaran')
                    @if (Route::has('pembayaran.index'))
                        <a href="{{ route('pembayaran.index') }}"
                            class="text-xs font-semibold text-emerald-600 hover:underline">
                            Semua Pembayaran &rarr;
                        </a>
                    @endif
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Status Verifikasi</th>
                            <th scope="col" class="px-4 py-3 font-medium text-center">Jumlah</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                    Menunggu verifikasi
                                </span>
                            </td>
                            <td
                                class="px-4 py-3 text-center font-bold {{ $ringkasan['pembayaran_menunggu'] > 0 ? 'text-amber-600' : 'text-slate-700' }}">
                                {{ $ringkasan['pembayaran_menunggu'] }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @can('akses-pembayaran')
                                    @if (Route::has('pembayaran.index'))
                                        <a href="{{ route('pembayaran.index', ['status' => \App\Models\Pembayaran::MENUNGGU]) }}"
                                            class="inline-block px-2.5 py-1 text-xs font-semibold rounded bg-amber-100 text-amber-800 hover:bg-amber-200 transition-colors">
                                            Verifikasi
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-300">&mdash;</span>
                                @endcan
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    Diterima
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                {{ $ringkasan['pembayaran_diterima'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('akses-pembayaran')
                                    @if (Route::has('pembayaran.index'))
                                        <a href="{{ route('pembayaran.index', ['status' => \App\Models\Pembayaran::DITERIMA]) }}"
                                            class="text-xs font-semibold text-emerald-600 hover:underline">
                                            Lihat
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-300">&mdash;</span>
                                @endcan
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                    Ditolak
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                {{ $ringkasan['pembayaran_ditolak'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('akses-pembayaran')
                                    @if (Route::has('pembayaran.index'))
                                        <a href="{{ route('pembayaran.index', ['status' => \App\Models\Pembayaran::DITOLAK]) }}"
                                            class="text-xs font-semibold text-emerald-600 hover:underline">
                                            Lihat
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-300">&mdash;</span>
                                @endcan
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-800">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                    Dibatalkan Mahasiswa
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                {{ $ringkasan['pembayaran_dibatalkan'] }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('akses-pembayaran')
                                    @if (Route::has('pembayaran.index'))
                                        <a href="{{ route('pembayaran.index', ['status' => \App\Models\Pembayaran::DIBATALKAN]) }}"
                                            class="text-xs font-semibold text-emerald-600 hover:underline">
                                            Lihat
                                        </a>
                                    @endif
                                @else
                                    <span class="text-slate-300">&mdash;</span>
                                @endcan
                            </td>
                        </tr>

                        <tr class="bg-slate-50 font-semibold">
                            <td class="px-4 py-3 text-slate-800">Total Nominal Diterima</td>
                            <td class="px-4 py-3 text-center text-emerald-700" colspan="2">
                                Rp{{ number_format($ringkasan['nominal_pembayaran_diterima'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- AKSES CEPAT & CATATAN -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Pintasan Akses Cepat (2 Kolom di Layar Besar) -->
        <div class="lg:col-span-2 rounded-xl bg-white p-6 shadow-sm border border-slate-200">
            <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Pintasan Akses Cepat
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @if (Route::has('keuangan.jenis-biaya.index'))
                    <a href="{{ route('keuangan.jenis-biaya.index') }}"
                        class="flex flex-col justify-between p-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 transition-colors group">
                        <div
                            class="p-2 w-fit rounded-lg bg-white text-emerald-600 shadow-sm border border-slate-100 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm font-semibold text-slate-800 group-hover:text-emerald-800">Jenis Biaya</p>
                            <p class="text-xs text-slate-500 mt-1">Kelola master tarif & komponen biaya</p>
                        </div>
                    </a>
                @endif

                @can('akses-tagihan')
                    @if (Route::has('tagihan.index'))
                        <a href="{{ route('tagihan.index') }}"
                            class="flex flex-col justify-between p-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50 hover:border-blue-200 transition-colors group">
                            <div
                                class="p-2 w-fit rounded-lg bg-white text-blue-600 shadow-sm border border-slate-100 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-800">Tagihan Mahasiswa</p>
                                <p class="text-xs text-slate-500 mt-1">Buat, terbitkan, & atur tagihan</p>
                            </div>
                        </a>
                    @endif
                @endcan

                @can('akses-pembayaran')
                    @if (Route::has('pembayaran.index'))
                        <a href="{{ route('pembayaran.index') }}"
                            class="flex flex-col justify-between p-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-amber-50 hover:border-amber-200 transition-colors group">
                            <div
                                class="p-2 w-fit rounded-lg bg-white text-amber-600 shadow-sm border border-slate-100 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm font-semibold text-slate-800 group-hover:text-amber-800">Verifikasi
                                    Pembayaran</p>
                                <p class="text-xs text-slate-500 mt-1">Periksa & terima bukti transfer</p>
                            </div>
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        <!-- Catatan & Informasi Sistem -->
        <div class="rounded-xl bg-slate-50 p-6 border border-slate-200 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Catatan Sistem
                </h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Ringkasan data dihitung secara real-time dari transaksi tagihan dan bukti pembayaran mahasiswa.
                </p>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Halaman ini bersifat mutlak sebagai indikator pemantauan dan tidak mengubah status transaksi secara
                    langsung.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-200 text-[11px] text-slate-400">
                SIAKAD Finance Module &bull; Integrated System
            </div>
        </div>

    </div>
@endsection
