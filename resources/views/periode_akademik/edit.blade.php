@extends('layouts.admin')

@section('title', 'Edit Periode Akademik')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Edit Periode Akademik</h1>
            <p class="text-sm text-slate-500 mt-1">Kode: {{ $periodeAkademik->kode }}</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('admin.periode-akademik.index') }}">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Formulir Perubahan Periode Akademik</h2>
        </div>
        <div class="p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.periode-akademik.update', $periodeAkademik) }}">
                @method('PATCH')
                @include('periode_akademik._form', ['version' => $version])

                <div class="mt-8 flex items-center justify-end gap-3 pt-6 border-t border-slate-200">
                    <a class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
                        href="{{ route('admin.periode-akademik.index') }}">
                        Batal
                    </a>
                    <button type="submit"
                        class="rounded-lg bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
