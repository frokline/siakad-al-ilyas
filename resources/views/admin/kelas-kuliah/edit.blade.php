@extends('layouts.admin')

@section('title', 'Edit Kelas Kuliah')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Edit Kelas Kuliah</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $kelas->kode }} — {{ $kelas->nama_mk_snapshot }}</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('admin.kelas-kuliah.show', $kelas) }}">
            Detail Kelas
        </a>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-6">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Informasi Rombel & Periode</h2>
        </div>
        <div class="p-6">
            @include('admin.kelas-kuliah._identitas')
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Kode dan Status Kelas</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.kelas-kuliah.update', $kelas) }}">
                @include('admin.kelas-kuliah._form')
            </form>
        </div>
    </div>
@endsection
