@extends('layouts.admin')

@section('title', 'Tambah Mata Kuliah Kurikulum')

@section('content')
    <div class="w-full min-w-0">

        {{-- BREADCRUMB --}}
        <nav class="mb-5 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500" aria-label="Breadcrumb">

            <a href="{{ route('admin.kurikulum.index') }}" class="transition-colors hover:text-siakad-dark">
                Kurikulum
            </a>

            <svg class="h-3.5 w-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
            </svg>

            <a href="{{ route('admin.kurikulum.show', $kurikulum) }}" class="transition-colors hover:text-siakad-dark">
                {{ $kurikulum->kode }}
            </a>

            <svg class="h-3.5 w-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
            </svg>

            <a href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}"
                class="transition-colors hover:text-siakad-dark">
                Susunan Mata Kuliah
            </a>

            <svg class="h-3.5 w-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
            </svg>

            <span class="font-semibold text-slate-800" aria-current="page">
                Tambah Mata Kuliah
            </span>
        </nav>

        {{-- PAGE HEADER --}}
        <header
            class="mb-6 flex flex-col gap-4 border-b border-slate-200 pb-6
            sm:flex-row sm:items-center sm:justify-between">

            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center rounded-md bg-emerald-50
                        px-2.5 py-1 text-xs font-bold text-emerald-800">
                        PENGELOLAAN KURIKULUM
                    </span>

                    <span class="text-xs text-slate-400">
                        Formulir penambahan data
                    </span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900
                    sm:text-3xl">
                    Tambah Mata Kuliah
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Tambahkan mata kuliah ke dalam susunan kurikulum dan tentukan
                    jumlah SKS, semester rekomendasi, serta sifat mata kuliahnya.
                </p>
            </div>

            <a href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}"
                class="inline-flex shrink-0 items-center justify-center gap-2
                    rounded-lg border border-slate-300 bg-white px-4 py-2.5
                    text-sm font-semibold text-slate-700 shadow-sm
                    transition hover:border-slate-400 hover:bg-slate-50
                    focus:outline-none focus:ring-2 focus:ring-siakad-dark
                    focus:ring-offset-2">

                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>

                Kembali ke Daftar
            </a>
        </header>

        {{-- INFORMASI KURIKULUM --}}
        <section class="mb-6 overflow-hidden rounded-xl border border-slate-200
            bg-white shadow-sm"
            aria-label="Informasi kurikulum yang dipilih">

            <div class="flex items-center gap-3 border-b border-slate-100
                px-5 py-3.5 sm:px-6">

                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center
                    rounded-lg bg-emerald-50 text-siakad-dark">

                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-800">
                        Informasi Kurikulum
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Konteks kurikulum untuk penambahan mata kuliah.
                    </p>
                </div>
            </div>

            <div
                class="grid grid-cols-1 divide-y divide-slate-100
                sm:grid-cols-3 sm:divide-x sm:divide-y-0">

                <div class="min-w-0 px-5 py-4 sm:px-6">
                    <p class="mb-1.5 text-xs font-medium text-slate-500">
                        Kode Kurikulum
                    </p>
                    <p class="break-words text-sm font-bold text-slate-800">
                        {{ $kurikulum->kode }}
                    </p>
                </div>

                <div class="min-w-0 px-5 py-4 sm:px-6">
                    <p class="mb-1.5 text-xs font-medium text-slate-500">
                        Nama Kurikulum
                    </p>
                    <p class="break-words text-sm font-semibold leading-5 text-slate-800">
                        {{ $kurikulum->nama }}
                    </p>
                </div>

                <div class="min-w-0 px-5 py-4 sm:px-6">
                    <p class="mb-1.5 text-xs font-medium text-slate-500">
                        Program Studi
                    </p>
                    <p class="break-words text-sm font-semibold leading-5 text-slate-800">
                        {{ $kurikulum->programStudi->nama }}
                    </p>
                </div>

            </div>
        </section>

        {{-- PERINGATAN JIKA TIDAK ADA MATA KULIAH --}}
        @if ($daftarMataKuliah->isEmpty())
            <div class="mb-6 flex items-start gap-3 rounded-xl border
                border-amber-200 bg-amber-50 p-4 sm:p-5"
                role="status">

                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center
                    rounded-lg bg-white text-amber-700 shadow-sm">

                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.7 3.86a2 2 0 0 0-3.4 0z" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-amber-950">
                        Tidak ada mata kuliah yang dapat ditambahkan
                    </p>

                    <p class="mt-1 text-sm leading-6 text-amber-900">
                        Tidak ditemukan mata kuliah aktif yang tersedia untuk
                        ditambahkan ke kurikulum ini. Periksa katalog mata kuliah
                        program studi Anda.
                    </p>

                    <a href="{{ route('admin.mata-kuliah.index', [
                        'program_studi_id' => $kurikulum->program_studi_id,
                    ]) }}"
                        class="mt-2 inline-flex items-center gap-1.5 text-sm
                            font-semibold text-amber-950 underline
                            decoration-amber-400 underline-offset-4
                            hover:text-siakad-dark">

                        Buka Katalog Mata Kuliah

                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M7 7h10v10" />
                        </svg>
                    </a>
                </div>
            </div>
        @endif

        {{-- FORMULIR UTAMA: MENGGUNAKAN SELURUH LEBAR AREA KONTEN --}}
        <section class="w-full min-w-0 overflow-hidden rounded-xl
            border border-slate-200 bg-white shadow-sm"
            aria-labelledby="form-title">

            {{-- FORM HEADER --}}
            <div
                class="flex items-center gap-3 border-b border-slate-200
                bg-slate-50 px-5 py-4 sm:px-6 lg:px-7">

                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center
                    rounded-lg bg-emerald-100 text-siakad-dark">

                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        <rect x="3" y="3" width="18" height="18" rx="3" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <h2 id="form-title" class="text-base font-bold text-slate-900">
                        Formulir Mata Kuliah
                    </h2>

                    <p class="mt-1 text-sm leading-5 text-slate-500">
                        Lengkapi seluruh kolom berikut untuk menambahkan mata kuliah.
                        Kolom bertanda
                        <span class="font-bold text-rose-600">*</span>
                        wajib diisi.
                    </p>
                </div>
            </div>

            {{-- FORM BODY --}}
            <form method="POST" action="{{ route('admin.kurikulum.mata-kuliah.store', $kurikulum) }}"
                class="w-full min-w-0">

                @csrf

                <div class="min-w-0 p-5 sm:p-6 lg:p-7">
                    @include('kurikulum_mata_kuliah._form')
                </div>

                {{-- FORM ACTIONS --}}
                <div
                    class="flex flex-col-reverse gap-3 border-t border-slate-200
                    bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center
                    sm:justify-between sm:px-6 lg:px-7">

                    <p class="hidden text-xs leading-5 text-slate-500 sm:block">
                        Pastikan informasi yang dimasukkan sudah benar sebelum disimpan.
                    </p>

                    <div
                        class="flex w-full flex-col-reverse gap-2
                        sm:w-auto sm:flex-row sm:items-center sm:justify-end">

                        <a href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}"
                            class="inline-flex min-h-10 items-center justify-center
                                rounded-lg border border-slate-300 bg-white
                                px-5 py-2.5 text-sm font-semibold text-slate-700
                                transition hover:bg-slate-100
                                focus:outline-none focus:ring-2
                                focus:ring-slate-400 focus:ring-offset-2">

                            Batal
                        </a>

                        <button type="submit" @disabled($daftarMataKuliah->isEmpty())
                            class="inline-flex min-h-10 items-center justify-center
                                gap-2 rounded-lg bg-siakad-dark px-5 py-2.5
                                text-sm font-semibold text-white shadow-sm
                                transition hover:bg-emerald-800
                                focus:outline-none focus:ring-2
                                focus:ring-siakad-dark focus:ring-offset-2
                                disabled:cursor-not-allowed disabled:opacity-50">

                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>

                            Tambahkan Mata Kuliah
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection
