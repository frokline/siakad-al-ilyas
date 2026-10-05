@extends('layouts.portal')

@section('title', 'Kelas Saya')

@section('content')
    <!-- HEADER HALAMAN -->
    <div class="mb-8">
        <p class="text-sm font-bold text-siakad-active mb-1 uppercase tracking-wider">Portal Dosen</p>
        <h1 class="text-2xl font-bold text-slate-800">Kelas Saya</h1>
        <p class="text-sm text-slate-500 mt-1">Daftar kelas yang ditugaskan kepada Anda sebagai koordinator atau pengajar.
        </p>
    </div>

    <!-- KARTU FILTER -->
    <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200 mb-8">
        <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filter Kelas
        </h2>

        <form method="get" action="{{ route('portal.dosen.kelas.index') }}"
            class="grid grid-cols-1 sm:grid-cols-12 gap-5 items-end">

            <div class="sm:col-span-6 w-full">
                <label for="q" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kode atau
                    Nama Mata Kuliah</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input id="q" type="search" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Ketik kata kunci..."
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                </div>
            </div>

            <div class="sm:col-span-4 w-full">
                <label for="status" class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status
                    Kelas</label>
                <select id="status" name="status"
                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua status</option>
                    @foreach ($pilihanStatus as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(($filter['status'] ?? '') === $nilai)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-2 w-full">
                <button type="submit"
                    class="flex-1 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors">
                    Terapkan
                </button>
                <a href="{{ route('portal.dosen.kelas.index') }}"
                    class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors flex items-center justify-center shadow-sm">
                    Reset
                </a>
            </div>
        </form>

        @if ($errors->any())
            <div class="mt-5 rounded-lg bg-rose-50 border border-rose-200 p-4 text-rose-800 text-sm shadow-sm"
                role="alert">
                <strong class="font-bold flex items-center gap-2 mb-2">
                    <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Periksa kembali filter Anda:
                </strong>
                <ul class="list-disc list-inside ml-6 text-xs">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- KARTU TABEL DATA -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Kelas Penugasan</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse min-w-[800px]">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Kelas & Mata Kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Periode</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center" title="Jumlah Peserta">Peserta</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center" title="Jadwal Aktif">Jadwal</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center" title="Jumlah Pertemuan">Sesi</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center" title="Jumlah Kegiatan">Kegiatan</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($kelas as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <strong
                                    class="font-bold text-slate-800 font-mono text-sm block group-hover:text-siakad-dark transition-colors">{{ $item->kode }}</strong>
                                <span class="text-slate-700 block mt-1">{{ $item->nama_mk_snapshot }}</span>
                                <span
                                    class="text-xs text-slate-400 font-medium block mt-0.5 border-t border-slate-100 pt-0.5 inline-block">{{ str_replace('.', ',', $item->sks_snapshot) }}
                                    SKS</span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">
                                    {{ $item->rombel?->periodeAkademik?->kode ?? '—' }}</div>
                                @if ($item->rombel?->kode)
                                    <span class="text-xs text-slate-500 mt-1 block">Rombel: <span
                                            class="font-mono text-slate-700">{{ $item->rombel->kode }}</span></span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusLabel = $pilihanStatus[$item->status] ?? $item->status;
                                    $badgeClass =
                                        strtolower($statusLabel) === 'aktif'
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                            : 'bg-slate-100 text-slate-600 border-slate-200';
                                @endphp
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center font-mono font-medium text-slate-700">
                                {{ number_format($item->jumlah_peserta, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4 text-center font-mono font-medium text-slate-700">
                                {{ number_format($item->jumlah_jadwal_aktif, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4 text-center font-mono font-medium text-slate-700">
                                {{ number_format($item->jumlah_pertemuan, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4 text-center font-mono font-medium text-slate-700">
                                {{ number_format($item->jumlah_kegiatan, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('portal.dosen.kelas.show', $item->id) }}"
                                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-siakad-dark hover:text-emerald-500 transition-colors">
                                    Detail
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
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
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">Belum ada kelas penugasan</p>
                                <p class="mt-1 text-xs text-slate-500">Anda belum ditugaskan sebagai koordinator atau
                                    pengajar pada kelas mana pun.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($kelas->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                {{ $kelas->links() }}
            </div>
        @endif
    </div>
@endsection
