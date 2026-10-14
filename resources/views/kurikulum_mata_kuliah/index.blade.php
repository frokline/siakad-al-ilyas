@extends('layouts.admin')

@section('title', 'Susunan Mata Kuliah')

@section('content')
    <!-- BREADCRUMB -->
    <nav class="mb-4 flex items-center gap-2 text-xs font-medium text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('admin.kurikulum.index') }}" class="hover:text-siakad-dark transition-colors">Kurikulum</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('admin.kurikulum.show', $kurikulum) }}"
            class="hover:text-siakad-dark transition-colors">{{ $kurikulum->kode }}</a>
        <span aria-hidden="true">/</span>
        <span class="text-slate-800 font-bold">Susunan Mata Kuliah</span>
    </nav>

    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Susunan Mata Kuliah</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $kurikulum->kode }} — {{ $kurikulum->nama }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $kurikulum->programStudi->nama }}</p>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            @if ($bisaTambah)
                <a href="{{ route('admin.kurikulum.mata-kuliah.create', $kurikulum) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Mata Kuliah
                </a>
            @endif

            <a href="{{ route('admin.kurikulum.show', $kurikulum) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                Detail Kurikulum
            </a>
        </div>
    </div>

    @if (!$bisaUbah)
        <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm"
            role="status">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/80 text-lg font-bold"
                aria-hidden="true">!</span>
            <div>
                <p class="font-semibold">Kurikulum terkunci.</p>
                <p class="mt-0.5 text-amber-800">Susunan mata kuliah tidak dapat diubah karena kurikulum sudah aktif atau
                    diarsipkan.</p>
            </div>
        </div>
    @elseif (!$kurikulum->programStudi->aktif)
        <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm"
            role="status">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/80 text-lg font-bold"
                aria-hidden="true">!</span>
            <div>
                <p class="font-semibold">Program studi nonaktif.</p>
                <p class="mt-0.5 text-amber-800">Penambahan mata kuliah belum tersedia.</p>
            </div>
        </div>
    @endif

    <!-- STATS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Jumlah Mata Kuliah</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">{{ number_format($ringkasan->jumlah, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-500">Dalam seluruh susunan kurikulum</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Bobot</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">
                {{ number_format((float) $ringkasan->total_sks, 1, ',', '.') }}
                <span class="text-sm font-semibold text-slate-500">SKS</span>
            </p>
            <p class="mt-1 text-xs text-slate-500">Akumulasi SKS mata kuliah</p>
        </div>
    </div>

    <!-- FILTER / SEARCH CARD -->
    <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 mb-6">
        <form method="GET" action="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}"
            class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">

            <div class="sm:col-span-4 w-full">
                <label for="q"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Pencarian</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                        placeholder="Kode atau nama mata kuliah..." maxlength="150"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white placeholder-slate-400">
                </div>
            </div>

            <div class="sm:col-span-3 w-full">
                <label for="semester"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Semester</label>
                <input id="semester" name="semester" type="number" value="{{ $filters['semester'] ?? '' }}"
                    min="1" max="14" step="1" placeholder="Semua semester"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
            </div>

            <div class="sm:col-span-2 w-full">
                <label for="filter_sifat"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Sifat</label>
                <select id="filter_sifat" name="sifat"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua sifat</option>
                    @foreach ($sifatOptions as $kodeSifat => $labelSifat)
                        <option value="{{ $kodeSifat }}" @selected(($filters['sifat'] ?? '') === $kodeSifat)>
                            {{ $labelSifat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 flex gap-2 w-full">
                <button type="submit"
                    class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">
                    Cari
                </button>
                <a href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DATA TABLE CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">

        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Daftar Mata Kuliah</h2>
                <p class="mt-0.5 text-xs text-slate-500">Menampilkan hasil sesuai pencarian dan filter yang aktif.</p>
            </div>
            <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ number_format($daftarDetail->total(), 0, ',', '.') }} mata kuliah
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <caption class="sr-only">Daftar mata kuliah dalam kurikulum {{ $kurikulum->kode }}</caption>
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-12 text-center">No.</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Kode</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Mata Kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">SKS</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Semester</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Sifat</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarDetail as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 text-center font-medium text-slate-400">
                                {{ $daftarDetail->firstItem() + $loop->index }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-800">
                                {{ $item->mataKuliah->kode }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800 group-hover:text-siakad-dark transition-colors">
                                    {{ $item->mataKuliah->nama }}
                                </div>
                                @if (!$item->mataKuliah->aktif)
                                    <span
                                        class="inline-flex items-center rounded bg-rose-50 border border-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700 mt-1">
                                        Nonaktif di katalog
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-700">
                                {{ str_replace('.', ',', $item->sks) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex items-center justify-center min-w-8 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                                    {{ $item->semester_rekomendasi }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $sifatOptions[$item->sifat] ?? $item->sifat }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.kurikulum.mata-kuliah.show', [$kurikulum, $item]) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                        title="Detail {{ $item->mataKuliah->kode }}" aria-label="Detail {{ $item->mataKuliah->kode }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @if ($bisaUbah)
                                        <a href="{{ route('admin.kurikulum.mata-kuliah.edit', [$kurikulum, $item]) }}"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-accent transition-colors shadow-sm"
                                            title="Edit {{ $item->mataKuliah->kode }}" aria-label="Edit {{ $item->mataKuliah->kode }}">
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
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div
                                    class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-slate-100 mb-4">
                                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">Belum ada mata kuliah yang cocok</p>
                                <p class="mt-1 text-xs text-slate-500">Sesuaikan pencarian atau filter di atas,
                                    atau tambah mata kuliah baru ke kurikulum ini.</p>
                                <div class="mt-4 flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}"
                                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                                        Reset Filter
                                    </a>
                                    @if ($bisaTambah)
                                        <a href="{{ route('admin.kurikulum.mata-kuliah.create', $kurikulum) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-siakad-dark px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-800 transition-colors">
                                            + Tambah Mata Kuliah
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <x-pagination :paginator="$daftarDetail" />
    </div>
@endsection
