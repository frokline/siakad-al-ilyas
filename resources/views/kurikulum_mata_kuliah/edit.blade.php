@extends('layouts.admin')

@section('title', 'Edit Mata Kuliah Kurikulum')

@section('content')
    <nav class="mb-4 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('admin.kurikulum.index') }}" class="transition-colors hover:text-siakad-dark">Kurikulum</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.kurikulum.show', $kurikulum) }}"
            class="transition-colors hover:text-siakad-dark">{{ $kurikulum->kode }}</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}"
            class="transition-colors hover:text-siakad-dark">Susunan Mata Kuliah</a>
        <span aria-hidden="true">/</span>
        <span class="font-bold text-slate-800">Edit</span>
    </nav>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">Pengelolaan Kurikulum</p>
            <h1 class="text-xl font-bold text-slate-800">Edit Mata Kuliah</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $kurikulum->kode }} — {{ $kurikulum->nama }}
            </p>
            <p class="mt-1 text-xs text-slate-500">{{ $kurikulum->programStudi->nama }}</p>
        </div>

        <a href="{{ route('admin.kurikulum.mata-kuliah.show', [$kurikulum, $detail]) }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Detail
        </a>
    </div>

    <div class="mx-auto max-w-4xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">
            <h2 class="text-sm font-bold text-slate-800">Perbarui Rincian Mata Kuliah</h2>
            <p class="mt-1 text-xs text-slate-500">
                Periksa kembali nilai yang ada sebelum menyimpan perubahan.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.kurikulum.mata-kuliah.update', [$kurikulum, $detail]) }}"
            class="p-5 sm:p-6">
            @csrf
            @method('PATCH')

            @include('kurikulum_mata_kuliah._form')

            <div class="mt-7 flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:flex-row sm:items-center">
                <a href="{{ route('admin.kurikulum.mata-kuliah.show', [$kurikulum, $detail]) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                    Batal
                </a>

                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2 sm:ml-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
