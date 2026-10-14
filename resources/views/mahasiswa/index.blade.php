@extends('layouts.admin')

@section('title', 'Daftar Mahasiswa')

@section('content')
    <!-- PAGE HEADER -->

    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-slate-800">Mahasiswa</h1>
            <p class="mt-1 text-sm text-slate-500">
                Kelola identitas dan biodata mahasiswa.
            </p>
        </div>

        <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <a href="{{ route('admin.mahasiswa.export', [
                'q' => $filter['q'] ?? null,
                'status_akun' => $filter['status_akun'] ?? null,
            ]) }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-emerald-700 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-800 transition-colors hover:bg-emerald-50 sm:w-auto">

                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12-4-4m4 4 4-4M5 20h14" />
                </svg>

                Ekspor Excel
            </a>

            <a href="{{ route('admin.mahasiswa.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 sm:w-auto">

                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>

                Tambah Mahasiswa
            </a>
        </div>
    </div>


    <!-- FILTER / SEARCH CARD -->
    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 mb-6">
        <form method="GET" action="{{ route('admin.mahasiswa.index') }}"
            class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
            <div class="sm:col-span-6 w-full">
                <label for="q" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Cari
                    Mahasiswa</label>
                <input type="search" id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                    placeholder="NIM, nama, atau email"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
            </div>

            <div class="sm:col-span-4 w-full">
                <label for="status_akun" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status
                    Akun</label>
                <select id="status_akun" name="status_akun"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(($filter['status_akun'] ?? '') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(($filter['status_akun'] ?? '') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2 w-full">
                <button type="submit"
                    class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors text-center">
                    Cari
                </button>
                <a href="{{ route('admin.mahasiswa.index') }}"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DATA TABLE CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Mahasiswa</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ number_format($daftar->total(), 0, ',', '.') }} data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">NIM</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Mahasiswa</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Program Studi Aktif</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status Akun</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftar as $mahasiswa)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 font-mono font-bold text-slate-800 text-xs">
                                {{ $mahasiswa->nim }}
                            </td>
                            <td class="px-6 py-4">
                                <strong class="font-bold text-slate-800 block">{{ $mahasiswa->user?->nama ?? '—' }}</strong>
                                <span
                                    class="text-xs text-slate-500 block mt-0.5">{{ $mahasiswa->user?->email ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-700 font-medium">
                                {{ $mahasiswa->riwayatAktif?->kurikulum?->programStudi?->nama ?? 'Belum ada riwayat aktif' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $isAktif = $mahasiswa->user?->status === 'aktif';
                                    $badgeClass = $isAktif
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                        : 'bg-rose-50 text-rose-700 border-rose-100';
                                    $dotClass = $isAktif ? 'bg-emerald-500' : 'bg-rose-500';
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ $isAktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.mahasiswa.show', $mahasiswa) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                        aria-label="Detail mahasiswa {{ $mahasiswa->nim }}">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}"
                                        class="inline-flex items-center gap-1 rounded-lg bg-siakad-dark px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800 transition-colors shadow-sm"
                                        aria-label="Edit mahasiswa {{ $mahasiswa->nim }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada mahasiswa yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
            @include('mahasiswa._pagination', ['paginator' => $daftar])
        </div>
    </div>
@endsection
