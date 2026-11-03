@extends('layouts.admin')

@section('title', 'Edit Kurikulum')

@section('content')
    {{-- PAGE HEADER --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-slate-800">
                Edit Kurikulum
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                {{ $kurikulum->kode }} — {{ $kurikulum->nama }}
            </p>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-wrap items-center gap-3">
            {{-- Susunan Mata Kuliah --}}
            <a href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg
                    border border-siakad-dark bg-siakad-dark/10
                    px-4 py-2.5 text-sm font-semibold text-siakad-dark
                    shadow-sm transition-colors hover:bg-siakad-dark/20
                    focus:outline-none focus:ring-2 focus:ring-siakad-dark
                    focus:ring-offset-2">

                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5
                            S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18
                            7.5 18s3.332.477 4.5 1.253m0-13
                            C13.168 5.477 14.754 5 16.5 5
                            c1.747 0 3.332.477 4.5 1.253v13
                            C19.832 18.477 18.247 18 16.5 18
                            c-1.746 0-3.332.477-4.5 1.253" />
                </svg>

                Susunan Mata Kuliah
            </a>

            {{-- Kembali ke Detail --}}
            <a href="{{ route('admin.kurikulum.show', $kurikulum) }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg
                    border border-slate-300 bg-white px-4 py-2.5
                    text-sm font-semibold text-slate-700 shadow-sm
                    transition-colors hover:bg-slate-50
                    focus:outline-none focus:ring-2 focus:ring-slate-400
                    focus:ring-offset-2">

                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>

                Batal & Kembali
            </a>
        </div>
    </div>

    {{-- STATUS PROGRAM STUDI --}}
    @if (!$kurikulum->programStudi->aktif)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50
            p-4 text-sm text-amber-800"
            role="status">
            Program studi sedang nonaktif. Aktivasi kurikulum memerlukan
            program studi aktif.
        </div>
    @endif

    {{-- FORM CARD --}}
    <div class="w-full overflow-hidden rounded-xl border border-slate-200
        bg-white shadow-sm">

        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">
                Formulir Perubahan Kurikulum
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Ubah informasi kurikulum atau statusnya melalui formulir berikut.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.kurikulum.update', $kurikulum) }}" class="p-6">

            @csrf
            @method('PATCH')

            @include('kurikulum._form')

            {{-- FORM ACTIONS --}}
            <div
                class="mt-8 flex flex-col-reverse gap-3 border-t
                border-slate-200 pt-5 sm:flex-row sm:items-center
                sm:justify-end">

                <a href="{{ route('admin.kurikulum.show', $kurikulum) }}"
                    class="inline-flex justify-center rounded-lg
                        border border-slate-300 bg-white px-5 py-2.5
                        text-sm font-semibold text-slate-700
                        transition-colors hover:bg-slate-50">
                    Batal
                </a>

                <button type="submit"
                    class="inline-flex justify-center rounded-lg
                        bg-siakad-dark px-5 py-2.5 text-sm
                        font-semibold text-white shadow-sm
                        transition-colors hover:bg-emerald-800
                        focus:outline-none focus:ring-2
                        focus:ring-siakad-dark focus:ring-offset-2">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
