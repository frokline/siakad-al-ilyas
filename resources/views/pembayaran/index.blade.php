@extends('layouts.admin')

@section('title', 'Riwayat Pembayaran')

@section('content')
    @php
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $label = 'mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600';
    @endphp

    @include('pembayaran._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Riwayat pembayaran</h1>
            <p class="mt-1 text-sm text-slate-500">Satu pengajuan untuk satu tagihan bulanan. Pengajuan yang menunggu
                belum berarti pembayaran diterima.</p>
        </div>
        <a href="{{ route('tagihan.index') }}"
            class="inline-flex items-center gap-2 self-start rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Pilih
            tagihan untuk mengajukan</a>
    </div>

    <form method="get" action="{{ route('pembayaran.index') }}"
        class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        @if (!empty($filter['tagihan']))
            <input type="hidden" name="tagihan" value="{{ $filter['tagihan'] }}">
        @endif
        <div class="flex-1">
            <label for="q" class="{{ $label }}">Nomor pengajuan</label>
            <input id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                placeholder="Ketik nomor pengajuan" class="{{ $input }}">
        </div>
        <div class="sm:w-56">
            <label for="status" class="{{ $label }}">Status</label>
            <select id="status" name="status" class="{{ $input }}">
                <option value="">Semua</option>
                @foreach (\App\Models\Pembayaran::STATUS as $kode => $nama)
                    <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                class="rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Cari</button>
            <a href="{{ route('pembayaran.index') }}"
                class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Reset</a>
        </div>
    </form>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-5 py-3">Pengajuan</th>
                        <th class="px-5 py-3">Tagihan / mahasiswa</th>
                        <th class="px-5 py-3 text-right">Nominal</th>
                        <th class="px-5 py-3">Transfer</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($daftar as $p)
                        <tr class="hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-800">
                                <a href="{{ route('pembayaran.show', $p) }}"
                                    class="hover:text-siakad-active">{{ $p->nomor_pengajuan }}</a>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-800">{{ $p->tagihan_snapshot['nomor'] ?? '—' }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $p->tagihan_snapshot['snapshot']['nama'] ?? '—' }} &middot;
                                    {{ $p->tagihan_snapshot['snapshot']['nim'] ?? '—' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-slate-800">
                                {{ \App\Services\UangTagihan::rupiah($p->nominal_diajukan) }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-700">
                                {{ $p->tanggal_transfer->format('d-m-Y') }}</td>
                            <td class="px-5 py-4">@include('pembayaran._lencana', ['status' => $p->status])</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('pembayaran.show', $p) }}"
                                    class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                <svg class="mx-auto mb-3 h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Belum ada pengajuan yang dapat ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $daftar->links('pembayaran._pagination') }}
    </section>
@endsection
