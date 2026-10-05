@extends('layouts.admin')

@section('title', 'Edit Dosen')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Edit Dosen</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $dosen->kode_dosen }} — {{ $dosen->user->nama }}</p>
        </div>
        <a href="{{ route('admin.dosen.show', $dosen) }}"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors self-start shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Batal & Kembali
        </a>
    </div>

    @if (!$memilikiPeranDosen)
        <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-center justify-between"
            role="status">
            <span>Akun terhubung tidak memiliki peran Dosen.</span>
            <a href="{{ route('admin.users.edit', $dosen->user) }}" class="font-semibold underline hover:text-amber-900">
                Kelola peran akun
            </a>
        </div>
    @endif

    <!-- FORM CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Formulir Perubahan Dosen</h2>
        </div>

        <form method="POST" action="{{ route('admin.dosen.update', $dosen) }}" class="p-6">
            @csrf
            @method('PATCH')

            @include('dosen._form')

            <div class="mt-8 flex items-center justify-end gap-3 pt-5 border-t border-slate-200">
                <a href="{{ route('admin.dosen.show', $dosen) }}"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
