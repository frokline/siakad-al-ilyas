@extends('layouts.admin')

@section('title', 'Paket Semester')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Paket Semester</h1>
            <p class="text-sm text-slate-500 mt-1">Susunan mata kuliah untuk KRS per paket.</p>
        </div>

        <a href="{{ route('admin.paket-semester.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Paket
        </a>
    </div>

    <!-- FILTER / SEARCH CARD -->
    <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 mb-6">
        <form method="GET" action="{{ route('admin.paket-semester.index') }}"
            class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">

            <div class="sm:col-span-3 w-full">
                <label for="q"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Pencarian</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                        placeholder="Nama paket / kode kurikulum..." maxlength="150"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white placeholder-slate-400">
                </div>
            </div>

            <div class="sm:col-span-3 w-full">
                <label for="kurikulum_id"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Kurikulum</label>
                <select id="kurikulum_id" name="kurikulum_id"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua kurikulum</option>
                    @foreach ($daftarKurikulum as $kurikulum)
                        <option value="{{ $kurikulum->id }}" @selected((string) ($filters['kurikulum_id'] ?? '') === (string) $kurikulum->id)>
                            {{ $kurikulum->programStudi->nama }} — {{ $kurikulum->kode }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 w-full">
                <label for="semester_studi"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Semester</label>
                <input type="number" id="semester_studi" name="semester_studi" min="1" max="32767" step="1"
                    value="{{ $filters['semester_studi'] ?? '' }}" placeholder="Semua"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
            </div>

            <div class="sm:col-span-2 w-full">
                <label for="status"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Status</label>
                <select id="status" name="status"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $kode => $label)
                        <option value="{{ $kode }}" @selected(($filters['status'] ?? '') === $kode)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2 w-full">
                <button type="submit"
                    class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">
                    Cari
                </button>
                <a href="{{ route('admin.paket-semester.index') }}"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DATA TABLE CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">

        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Paket Semester</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ number_format($daftarPaket->total(), 0, ',', '.') }} paket
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-12 text-center">No.</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Paket</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Program / Kurikulum</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Semester</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Versi</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Mata Kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarPaket as $paket)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 text-center font-medium text-slate-400">
                                {{ $daftarPaket->firstItem() + $loop->index }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-800 group-hover:text-siakad-dark transition-colors">
                                {{ $paket->nama }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $paket->kurikulum->programStudi->nama }}</div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">{{ $paket->kurikulum->kode }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-slate-700">
                                {{ $paket->semester_studi }}
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs text-slate-600">
                                v{{ $paket->versi }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex items-center rounded bg-slate-100 border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ $paket->details_count }} MK
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusBadgeClass = match ($paket->status) {
                                        'diterbitkan' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'draf' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    $dotClass = match ($paket->status) {
                                        'diterbitkan' => 'bg-emerald-500',
                                        'draf' => 'bg-amber-500',
                                        default => 'bg-slate-400',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusBadgeClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ $statusOptions[$paket->status] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.paket-semester.show', $paket) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                        title="Detail">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @if ($paket->isDraf())
                                        <a href="{{ route('admin.paket-semester.edit', $paket) }}"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-accent transition-colors shadow-sm"
                                            title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div
                                    class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-slate-100 mb-4">
                                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">Tidak ada paket yang ditemukan</p>
                                <p class="mt-1 text-xs text-slate-500">Sesuaikan kata kunci pencarian atau filter Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <x-pagination :paginator="$daftarPaket" />
    </div>
@endsection
