@extends('layouts.admin')

@section('title', 'Jenis Biaya')

@section('content')
    @php
        $zona = (string) config('siakad.timezone', 'Asia/Makassar');
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $label = 'mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600';
    @endphp

    @include('jenis_biaya._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Jenis biaya</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola jenis biaya yang dipakai pada modul Tagihan.</p>
        </div>
        @can('create', \App\Models\JenisBiaya::class)
            <a href="{{ route('keuangan.jenis-biaya.create') }}"
                class="inline-flex items-center gap-2 self-start rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah jenis biaya
            </a>
        @endcan
    </div>

    <form method="get" action="{{ route('keuangan.jenis-biaya.index') }}"
        class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="q" class="{{ $label }}">Kode / nama biaya</label>
            <input id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                placeholder="Ketik kode atau nama biaya" class="{{ $input }}">
        </div>
        <div class="sm:w-52">
            <label for="status" class="{{ $label }}">Status</label>
            <select id="status" name="status" class="{{ $input }}">
                <option value="">Semua status</option>
                <option value="aktif" @selected(($filter['status'] ?? '') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(($filter['status'] ?? '') === 'nonaktif')>Nonaktif</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                class="rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Tampilkan</button>
            <a href="{{ route('keuangan.jenis-biaya.index') }}"
                class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Reset</a>
        </div>
    </form>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3">Nama biaya</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Revisi</th>
                        <th class="px-5 py-3">Diperbarui</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($daftar as $j)
                        <tr class="hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-semibold text-slate-700">
                                {{ $j->kode }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('keuangan.jenis-biaya.show', $j) }}"
                                    class="font-semibold text-slate-800 hover:text-siakad-active">{{ $j->nama }}</a>
                            </td>
                            <td class="px-5 py-4">@include('jenis_biaya._lencana', ['aktif' => $j->aktif])</td>
                            <td class="px-5 py-4 text-slate-700">{{ $j->revisi }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-700">
                                {{ $j->updated_at?->setTimezone($zona)->format('d-m-Y H:i') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <a href="{{ route('keuangan.jenis-biaya.show', $j) }}"
                                    class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Detail</a>
                                @can('update', $j)
                                    <a href="{{ route('keuangan.jenis-biaya.edit', $j) }}"
                                        class="ml-1 inline-flex rounded-lg bg-siakad-dark px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                Belum ada jenis biaya sesuai filter. Jenis awal yang digunakan proyek ini adalah SPP.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $daftar->links('jenis_biaya._pagination') }}
    </section>
@endsection
