@extends('layouts.admin')
@section('title', 'Edit Jadwal Kuliah')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Edit Jadwal #{{ $jadwal->id }}</h1>
            <p class="text-sm text-slate-500 mt-1">Revisi {{ $jadwal->revisi }} &middot; Kelas <span
                    class="font-mono font-bold">{{ $kelas->kode }}</span></p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('admin.jadwal-kuliah.show', $jadwal) }}">Batal / Detail jadwal</a>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Identitas Kelas dan Tim Pengajar</h2>
        </div>
        <div class="p-6">@include('admin.jadwal-kuliah._kelas')</div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Formulir Perubahan Pola Jadwal</h2>
        </div>
        <div class="p-6">
            @unless ($boleh)
                <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800" role="alert">
                    Isi pola sudah dikunci karena kelas/periode ditutup. Tindakan <strong>Nonaktifkan</strong> saja yang
                    tersedia pada halaman detail selama pola masih berstatus aktif.
                </div>
            @endunless
            <form method="POST" action="{{ route('admin.jadwal-kuliah.update', $jadwal) }}">
                @include('admin.jadwal-kuliah._form')
            </form>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Seluruh Pola Jadwal Kelas Ini</h2>
        </div>
        @include('admin.jadwal-kuliah._pola')
    </div>
@endsection
