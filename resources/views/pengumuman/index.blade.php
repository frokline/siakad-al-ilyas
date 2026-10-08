@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
    @php
        $zona = \App\Models\Pengumuman::ZONA;
        $kelola = $mode === 'kelola';
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $tab = 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition';
    @endphp

    @include('pengumuman._pesan')

    {{-- Kepala halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $kelola ? 'Kelola pengumuman' : 'Pengumuman untuk saya' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $kelola ? 'Draf, publikasi, dan arsip yang boleh Anda kelola.' : 'Pengumuman yang sedang tayang dan sesuai sasaran akun Anda.' }}
            </p>
        </div>
        @can('create', \App\Models\Pengumuman::class)
            <a href="{{ route('pengumuman.create') }}"
                class="inline-flex items-center gap-2 self-start rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat pengumuman
            </a>
        @endcan
    </div>

    {{-- Tab mode: hanya tampil bagi yang boleh mengelola --}}
    @can('create', \App\Models\Pengumuman::class)
        <div class="mb-5 inline-flex rounded-xl bg-slate-200/70 p-1" role="tablist">
            <a href="{{ route('pengumuman.index') }}"
                class="{{ $tab }} {{ !$kelola ? 'bg-white text-siakad-dark shadow-sm' : 'text-slate-600 hover:text-slate-800' }}">Untuk
                saya</a>
            <a href="{{ route('pengumuman.index', ['mode' => 'kelola']) }}"
                class="{{ $tab }} {{ $kelola ? 'bg-white text-siakad-dark shadow-sm' : 'text-slate-600 hover:text-slate-800' }}">Kelola</a>
        </div>
    @endcan

    {{-- Pencarian --}}
    <form method="get" action="{{ route('pengumuman.index') }}"
        class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <input type="hidden" name="mode" value="{{ $mode }}">
        <div class="flex-1">
            <label for="q" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Cari
                judul</label>
            <input id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                placeholder="Ketik judul pengumuman" class="{{ $input }}">
        </div>
        @if ($kelola)
            <div class="sm:w-48">
                <label for="status"
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Status</label>
                <select id="status" name="status" class="{{ $input }}">
                    <option value="">Semua</option>
                    @foreach (\App\Models\Pengumuman::STATUS as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(($filter['status'] ?? '') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="flex gap-2">
            <button type="submit"
                class="rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Cari</button>
            <a href="{{ route('pengumuman.index', ['mode' => $mode]) }}"
                class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Reset</a>
        </div>
    </form>

    @if ($kelola)
        {{-- Mode kelola: tabel --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="px-5 py-3">Judul</th>
                            <th class="px-5 py-3">Penulis</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Terbit (WITA)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($daftar as $p)
                            <tr class="hover:bg-slate-50/70">
                                <td class="max-w-md px-5 py-4">
                                    <a href="{{ route('pengumuman.show', $p) }}"
                                        class="font-semibold text-slate-800 hover:text-siakad-active">{{ $p->judul }}</a>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $p->pembuat?->nama }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @include('pengumuman._lencana', ['status' => $p->status])
                                        @if ($p->status === 'terbit' && $p->kedaluwarsa())
                                            <span
                                                class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">Masa
                                                tayang berakhir</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-700">
                                    {{ $p->terbit_at?->setTimezone($zona)->format('d-m-Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-slate-500">Belum ada pengumuman
                                    sesuai pencarian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $daftar->links('pengumuman._pagination') }}
        </section>
    @else
        {{-- Mode baca: kartu --}}
        <div class="space-y-4">
            @forelse($daftar as $p)
                <a href="{{ route('pengumuman.show', $p) }}"
                    class="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-siakad-active hover:shadow-md sm:p-6">
                    <div class="flex items-start gap-4">
                        <span
                            class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-siakad-active sm:flex">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg font-bold text-slate-800 group-hover:text-siakad-active">
                                {{ $p->judul }}</h2>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $p->terbit_at?->setTimezone($zona)->locale('id')->translatedFormat('d F Y, H:i') ?? '—' }}
                                WITA &middot; oleh {{ $p->pembuat?->nama ?? 'Petugas' }}
                                @if ($p->berakhir_at)
                                    &middot; tayang sampai
                                    {{ $p->berakhir_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                @endif
                            </p>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600">
                                {{ \Illuminate\Support\Str::limit($p->isi, 220) }}</p>
                            <span class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-siakad-active">Baca
                                selengkapnya
                                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                    <svg class="mx-auto mb-3 h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-sm text-slate-500">Belum ada pengumuman sesuai pencarian.</p>
                </div>
            @endforelse
        </div>
        @if ($daftar->hasPages())
            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{ $daftar->links('pengumuman._pagination') }}
            </div>
        @endif
    @endif
@endsection
