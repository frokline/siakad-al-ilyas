@extends('layouts.admin')

@section('title', 'Detail Mata Kuliah')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">{{ $mataKuliah->nama }}</h1>
            <p class="text-sm text-slate-500 mt-1">Detail mata kuliah {{ $mataKuliah->kode }}.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.mata-kuliah.edit', $mataKuliah) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Mata Kuliah
            </a>

            <a href="{{ route('admin.mata-kuliah.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                Kembali
            </a>
        </div>
    </div>

    <!-- DETAIL CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Informasi Detail Mata Kuliah</h2>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode Mata Kuliah</dt>
                    <dd class="mt-1 font-mono font-bold text-slate-800 text-base">{{ $mataKuliah->kode }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4 sm:col-span-2 lg:grid-cols-2">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Mata Kuliah</dt>
                    <dd class="mt-1 font-bold text-slate-800 text-base">{{ $mataKuliah->nama }}</dd>
                </div>

                <div class="border-b sm:border-b-0 border-slate-100 pb-4 sm:pb-0 sm:col-span-2 lg:col-span-2">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Program Studi</dt>
                    <dd class="mt-1 font-semibold text-slate-700">
                        <a href="{{ route('admin.program-studi.show', $mataKuliah->programStudi) }}"
                            class="text-siakad-dark hover:underline">
                            {{ $mataKuliah->programStudi->kode }} — {{ $mataKuliah->programStudi->nama }}
                        </a>
                    </dd>
                </div>

                <div class="border-b sm:border-b-0 border-slate-100 pb-4 sm:pb-0">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Program Studi</dt>
                    <dd class="mt-1">
                        @php
                            $prodiAktif = $mataKuliah->programStudi->aktif;
                            $prodiBadge = $prodiAktif
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                : 'bg-rose-50 text-rose-700 border-rose-100';
                            $prodiDot = $prodiAktif ? 'bg-emerald-500' : 'bg-rose-500';
                        @endphp
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $prodiBadge }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $prodiDot }}"></span>
                            {{ $prodiAktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </dd>
                </div>

                <div class="sm:col-span-2 lg:col-span-3 border-t border-slate-100 pt-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Mata Kuliah</dt>
                    <dd class="mt-1">
                        @php
                            $mkAktif = $mataKuliah->aktif;
                            $mkBadge = $mkAktif
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                : 'bg-slate-100 text-slate-600 border-slate-200';
                            $mkDot = $mkAktif ? 'bg-emerald-500' : 'bg-slate-400';
                        @endphp
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $mkBadge }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $mkDot }}"></span>
                            {{ $mkAktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
