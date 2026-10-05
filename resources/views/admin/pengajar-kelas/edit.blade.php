@extends('layouts.admin')
@section('title', 'Edit Penugasan Dosen')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Edit Penugasan #{{ $penugasan->id }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $penugasan->dosen->user->nama }} — revisi {{ $penugasan->revisi }}</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('admin.pengajar-kelas.show', $penugasan) }}">Detail penugasan</a>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Kelas Kuliah</h2>
        </div>
        <div class="p-6">@include('admin.pengajar-kelas._kelas')</div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Tim Pengajar Saat Ini</h2>
        </div>
        @include('admin.pengajar-kelas._tim')
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Peran dan Status Penugasan</h2>
        </div>
        <div class="p-6">
            @unless ($bolehSimpan)
                <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                    Kelas sudah selesai/diarsipkan atau periode telah diarsipkan. Riwayat penugasan hanya dapat dibaca.
                </div>
            @endunless
            <form method="POST" action="{{ route('admin.pengajar-kelas.update', $penugasan) }}">
                @include('admin.pengajar-kelas._form')
            </form>
        </div>
    </div>
@endsection
