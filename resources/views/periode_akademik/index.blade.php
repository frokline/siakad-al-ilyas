@extends('layouts.admin')

@section('title', 'Periode Akademik')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Periode Akademik</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola semester, tanggal perkuliahan, dan jadwal KRS.</p>
        </div>

        <a href="{{ route('admin.periode-akademik.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Periode
        </a>
    </div>

    <!-- FILTER / SEARCH CARD -->
    <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 mb-6">
        <form method="GET" action="{{ route('admin.periode-akademik.index') }}"
            class="flex flex-col sm:flex-row gap-4 items-end">

            <div class="flex-1 w-full">
                <label for="q" class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Cari
                    Periode</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                        placeholder="2026 atau GANJIL..." maxlength="30"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white placeholder-slate-400">
                </div>
            </div>

            <div class="w-full sm:w-56">
                <label for="filter-status"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Status</label>
                <select id="filter-status" name="status"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit"
                    class="flex-1 sm:flex-none rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">
                    Terapkan
                </button>
                <a href="{{ route('admin.periode-akademik.index') }}"
                    class="flex-1 sm:flex-none rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DATA TABLE CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">

        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Periode Akademik</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ number_format($daftarPeriode->total(), 0, ',', '.') }} periode akademik
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-12 text-center">No.</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Kode</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Tahun Ajaran</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Perkuliahan</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">KRS Saat Ini</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarPeriode as $periode)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 text-center font-medium text-slate-400">
                                {{ $daftarPeriode->firstItem() + $loop->index }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-800">
                                {{ $periode->kode }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-700">
                                {{ $periode->tahunAjaran() }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $periode->mulai->format('d/m/Y') }} — {{ $periode->selesai->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusBadgeClass = match ($periode->status) {
                                        'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'persiapan' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    $dotClass = match ($periode->status) {
                                        'aktif' => 'bg-emerald-500',
                                        'persiapan' => 'bg-amber-500',
                                        default => 'bg-slate-400',
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusBadgeClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ $statusOptions[$periode->status] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($periode->isKrsOpen())
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Terbuka
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 border border-slate-200 px-2.5 py-1 text-xs font-bold text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Tertutup
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.periode-akademik.show', $periode) }}"
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
                                    <a href="{{ route('admin.periode-akademik.edit', $periode) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-accent transition-colors shadow-sm"
                                        title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div
                                    class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-slate-100 mb-4">
                                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">Tidak ada periode akademik ditemukan</p>
                                <p class="mt-1 text-xs text-slate-500">Sesuaikan kata kunci pencarian atau filter status
                                    Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <x-pagination :paginator="$daftarPeriode" />
    </div>
@endsection
