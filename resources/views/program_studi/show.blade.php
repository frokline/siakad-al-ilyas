@extends('layouts.admin')

@section('title', 'Detail Program Studi')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">{{ $programStudi->nama }}</h1>
            <p class="text-sm text-slate-500 mt-1">Detail informasi program studi.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.program-studi.edit', $programStudi) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Program Studi
            </a>

            <a href="{{ route('admin.program-studi.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                Kembali
            </a>
        </div>
    </div>

    <!-- DETAIL CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Informasi Detail Program Studi</h2>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                <div class="border-b sm:border-b-0 sm:border-r border-slate-100 pb-4 sm:pb-0 sm:pr-6">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode Program Studi</dt>
                    <dd class="mt-1 font-mono font-bold text-slate-800 text-base">{{ $programStudi->kode }}</dd>
                </div>

                <div
                    class="border-b sm:border-b-0 sm:border-r border-slate-100 pb-4 sm:pb-0 sm:pr-6 sm:col-span-2 lg:col-span-2">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Program Studi</dt>
                    <dd class="mt-1 font-bold text-slate-800 text-base">{{ $programStudi->nama }}</dd>
                </div>

                <div class="pb-2">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenjang / Program</dt>
                    <dd class="mt-1 font-semibold text-slate-700">{{ $programStudi->jenjang }}</dd>
                </div>

                <div
                    class="border-t sm:border-t-0 border-slate-100 pt-4 sm:pt-0 sm:col-span-2 lg:col-span-4 grid grid-cols-1 sm:grid-cols-3 gap-6 pt-6 mt-2 border-t border-slate-100">
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status</dt>
                        <dd class="mt-1">
                            @php
                                $statusBadgeClass = $programStudi->aktif
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                    : 'bg-rose-50 text-rose-700 border-rose-100';
                                $dotClass = $programStudi->aktif ? 'bg-emerald-500' : 'bg-rose-500';
                            @endphp
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusBadgeClass }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                {{ $programStudi->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dibuat</dt>
                        <dd class="mt-1 text-slate-600 font-medium">
                            {{ $programStudi->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Diperbarui</dt>
                        <dd class="mt-1 text-slate-600 font-medium">
                            {{ $programStudi->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                </div>
            </dl>

            <div
                class="mt-8 rounded-lg bg-slate-50 border border-slate-200 p-4 text-xs text-slate-500 flex items-start gap-2">
                <svg class="h-4 w-4 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Program studi dapat dinonaktifkan melalui halaman Edit. Datanya tetap tersimpan dengan aman di
                    sistem.</span>
            </div>
        </div>
    </div>
@endsection
