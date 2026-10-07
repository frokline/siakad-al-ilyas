@extends('layouts.admin')

@section('title', 'Daftar Pengumpulan')

@section('content')
    @include('pengumpulan._pesan')

    @php
        $input =
            'w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white';
        $label = 'mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700';
    @endphp

    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Daftar pengumpulan</h1>
        <p class="mt-1 text-sm text-slate-500">Daftar jawaban tugas yang dapat Anda akses.</p>
    </div>

    <form method="get" action="{{ route('pengumpulan.index') }}"
        class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="md:col-span-1">
                <label for="q" class="{{ $label }}">Judul pembelajaran</label>
                <input id="q" name="q" type="search" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                    class="{{ $input }}">
            </div>

            <div>
                <label for="status" class="{{ $label }}">Status jawaban</label>
                <select id="status" name="status" class="{{ $input }}">
                    <option value="">Semua status</option>
                    <option value="berlaku" @selected(($filter['status'] ?? '') === 'berlaku')>Jawaban aktif</option>
                    <option value="{{ \App\Models\Pengumpulan::TERKIRIM }}" @selected(($filter['status'] ?? '') === \App\Models\Pengumpulan::TERKIRIM)>Terkirim</option>
                    <option value="{{ \App\Models\Pengumpulan::DIBATALKAN }}" @selected(($filter['status'] ?? '') === \App\Models\Pengumpulan::DIBATALKAN)>Dibatalkan
                    </option>
                </select>
            </div>

            @if (!empty($filter['kegiatan']))
                <input type="hidden" name="kegiatan" value="{{ $filter['kegiatan'] }}">
            @endif

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">Cari</button>
                <a href="{{ route('pengumpulan.index') }}"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </div>
    </form>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Pembelajaran</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Mahasiswa</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Waktu</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftar as $item)
                        @php
                            $status = \App\Models\Pengumpulan::STATUS[$item->status] ?? $item->status;
                            $aktif = in_array((int) $item->id, $berlakuIds, true);
                            $batal = $item->status === \App\Models\Pengumpulan::DIBATALKAN;
                        @endphp

                        <tr class="align-top hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <strong
                                    class="block text-slate-800">{{ $item->kegiatan?->judul ?? 'Pembelajaran tidak tersedia' }}</strong>
                                <span
                                    class="text-xs text-slate-500">{{ $item->kegiatan?->kelasKuliah?->kode ?? '—' }}</span>
                            </td>

                            <td class="px-6 py-4">{{ $item->pemilik?->nama ?? '—' }}</td>

                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $batal ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">{{ $status }}</span>
                                @if ($aktif)
                                    <span class="mt-1 block text-[11px] text-slate-400">Jawaban aktif</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs">
                                @if ($batal && $item->dibatalkan_at)
                                    Dibatalkan: {{ $item->dibatalkan_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                @elseif ($item->diubah_at)
                                    Diubah: {{ $item->diubah_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                @elseif ($item->dikirim_at)
                                    Dikirim: {{ $item->dikirim_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('pengumpulan.show', $item) }}"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center italic text-slate-500">Belum ada pengumpulan
                                yang dapat ditampilkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $daftar->links('pengumpulan._pagination') }}
    </section>
@endsection
