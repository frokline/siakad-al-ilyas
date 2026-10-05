@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Data Pengguna Sistem</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola akun, peran, dan hak akses pengguna SIAKAD.</p>
        </div>

        <a href="{{ route('admin.users.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Pengguna
        </a>
    </div>

    <!-- FILTER / SEARCH CARD -->
    <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">

            <div class="flex-1 w-full">
                <label for="q" class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Cari
                    Pengguna</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                        placeholder="Nama, username, atau email..." maxlength="190"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white placeholder-slate-400">
                </div>
            </div>

            <div class="w-full sm:w-56">
                <label for="filter-status"
                    class="block text-xs font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Status Akun</label>
                <select id="filter-status" name="status"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua Status</option>
                    <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(($filters['status'] ?? '') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>

            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit"
                    class="flex-1 sm:flex-none rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">
                    Terapkan
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="flex-1 sm:flex-none rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DATA TABLE CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">

        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Pengguna Sistem</h2>
            <span
                class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">{{ number_format($users->total(), 0, ',', '.') }}
                Akun Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-12 text-center">No.</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Identitas Pengguna</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Peran Akses</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $account)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 text-center font-medium text-slate-400">
                                {{ $users->firstItem() + $loop->index }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 shrink-0 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-sm text-slate-600">
                                        {{ strtoupper(substr($account->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 group-hover:text-siakad-dark transition-colors">
                                            {{ $account->nama }}</p>
                                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $account->username }} &bull;
                                            {{ $account->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($account->roles as $role)
                                        <span
                                            class="inline-flex items-center rounded bg-slate-100 border border-slate-200 px-2 py-1 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                                            {{ $role->nama }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">Belum Ditetapkan</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($account->isAktif())
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 border border-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Tombol Detail -->
                                    <a href="{{ route('admin.users.show', $account) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                        title="Lihat Detail">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <!-- Tombol Edit -->
                                    <a href="{{ route('admin.users.edit', $account) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 hover:bg-slate-50 hover:text-siakad-accent transition-colors shadow-sm"
                                        title="Edit Pengguna">
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
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div
                                    class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-slate-100 mb-4">
                                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">Tidak ada pengguna ditemukan</p>
                                <p class="mt-1 text-xs text-slate-500">Sesuaikan kata kunci pencarian atau filter status
                                    Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION (Custom Styling untuk manual links) -->
        @if ($users->hasPages())
            <div
                class="border-t border-slate-200 bg-white px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <span class="text-xs font-semibold text-slate-500">
                    Halaman <span class="text-slate-900">{{ $users->currentPage() }}</span> dari <span
                        class="text-slate-900">{{ $users->lastPage() }}</span>
                </span>

                <div class="flex items-center gap-2">
                    @if ($users->previousPageUrl())
                        <a href="{{ $users->previousPageUrl() }}"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                            &larr; Sebelumnya
                        </a>
                    @else
                        <button disabled
                            class="inline-flex items-center justify-center rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-300 cursor-not-allowed">
                            &larr; Sebelumnya
                        </button>
                    @endif

                    @if ($users->nextPageUrl())
                        <a href="{{ $users->nextPageUrl() }}"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                            Berikutnya &rarr;
                        </a>
                    @else
                        <button disabled
                            class="inline-flex items-center justify-center rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-300 cursor-not-allowed">
                            Berikutnya &rarr;
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
