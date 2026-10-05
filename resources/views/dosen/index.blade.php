@extends('layouts.admin')

@section('title', 'Daftar Dosen')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Dosen</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola identitas dan status dosen.</p>
        </div>

        <a href="{{ route('admin.dosen.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 self-start">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Dosen
        </a>
    </div>

    <!-- FILTER CARD -->
    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 mb-6">
        <form class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end" method="GET"
            action="{{ route('admin.dosen.index') }}">
            <div class="sm:col-span-5 w-full">
                <label for="q"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Pencarian</label>
                <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" maxlength="150"
                    placeholder="Kode dosen, NIDN, nama, atau akun"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
            </div>

            <div class="sm:col-span-3 w-full">
                <label for="filter_status"
                    class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status Dosen</label>
                <select id="filter_status" name="status"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $kodeStatus => $labelStatus)
                        <option value="{{ $kodeStatus }}" @selected(($filters['status'] ?? '') === $kodeStatus)>
                            {{ $labelStatus }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 w-full">
                <label for="status_akun" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status
                    Akun</label>
                <select id="status_akun" name="status_akun"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua status</option>
                    @foreach ($statusAkunOptions as $kodeStatus => $labelStatus)
                        <option value="{{ $kodeStatus }}" @selected(($filters['status_akun'] ?? '') === $kodeStatus)>
                            {{ $labelStatus }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2 w-full">
                <button type="submit"
                    class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors text-center">
                    Cari
                </button>
                <a href="{{ route('admin.dosen.index') }}"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DATA TABLE CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Dosen</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ number_format($daftarDosen->total(), 0, ',', '.') }} data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">No.</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Kode Dosen</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Nama Dosen</th>
                        <th scope="col" class="px-6 py-4 font-semibold">NIDN</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status Dosen</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status Akun</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftarDosen as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 text-slate-500 font-mono text-xs">
                                {{ $daftarDosen->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-800 text-xs">{{ $item->kode_dosen }}</td>
                            <td class="px-6 py-4">
                                <strong class="font-bold text-slate-800 block">{{ $item->user->nama }}</strong>
                                @if ($item->gelar !== null)
                                    <span class="text-xs text-slate-500 block mt-0.5">{{ $item->gelar }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-600 text-xs">{{ $item->nidn ?? '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $isAktif = $item->isAktif();
                                    $badgeClass = $isAktif
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                        : 'bg-rose-50 text-rose-700 border-rose-100';
                                    $dotClass = $isAktif ? 'bg-emerald-500' : 'bg-rose-500';
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ $statusOptions[$item->status] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $akunAktif = $item->user->isAktif();
                                    $akunBadge = $akunAktif
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                        : 'bg-slate-100 text-slate-600 border-slate-200';
                                    $akunDot = $akunAktif ? 'bg-emerald-500' : 'bg-slate-400';
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $akunBadge }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $akunDot }}"></span>
                                    {{ $akunAktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.dosen.show', $item) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm"
                                        aria-label="Detail dosen {{ $item->kode_dosen }}">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.dosen.edit', $item) }}"
                                        class="inline-flex items-center gap-1 rounded-lg bg-siakad-dark px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800 transition-colors shadow-sm"
                                        aria-label="Edit dosen {{ $item->kode_dosen }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                Tidak ada dosen yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
            @include('mahasiswa._pagination', ['paginator' => $daftarDosen])
        </div>
    </div>
@endsection
