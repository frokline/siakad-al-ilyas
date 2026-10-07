@extends('layouts.admin')

@section('title', 'Rekap Jawaban Mahasiswa')

@section('content')
    @include('pengumpulan._pesan')

    @php
        $belum = max(0, $total - $sudah);
        $persen = $total > 0 ? ($sudah / $total) * 100 : 0;
        $persenTeks = number_format($persen, 1, ',', '.');

        $input =
            'w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white';
        $label = 'mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700';
        $waktu = fn($teks) => \Illuminate\Support\Carbon::parse($teks, 'UTC')->setTimezone($zona)->format('d-m-Y H:i');
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Rekap dosen</p>
            <h1 class="text-xl font-bold text-slate-800">Rekap jawaban mahasiswa</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $kegiatan->judul }} &mdash;
                {{ $kegiatan->kelasKuliah?->kode ?? 'Kelas' }}</p>
        </div>

        <a href="{{ route('kegiatan.show', $kegiatan) }}"
            class="self-start rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">&larr;
            Kembali ke pembelajaran</a>
    </div>

    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div>
                <p class="text-xs text-slate-400">Jumlah peserta</p>
                <p class="text-2xl font-bold text-slate-800">{{ $total }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Sudah mengumpulkan</p>
                <p class="text-2xl font-bold text-emerald-700">{{ $sudah }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Belum mengumpulkan</p>
                <p class="text-2xl font-bold text-rose-700">{{ $belum }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Persentase</p>
                <p class="text-2xl font-bold text-slate-800">{{ $persenTeks }}%</p>
            </div>
        </div>

        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar"
            aria-valuenow="{{ round($persen) }}" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full rounded-full bg-siakad-active" style="width: {{ round($persen, 1) }}%"></div>
        </div>
    </section>

    <form method="get" action="{{ route('pengumpulan.rekap', $kegiatan) }}"
        class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="q" class="{{ $label }}">Nama atau NIM</label>
                <input id="q" name="q" type="search" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                    class="{{ $input }}">
            </div>

            <div>
                <label for="status" class="{{ $label }}">Status pengumpulan</label>
                <select id="status" name="status" class="{{ $input }}">
                    <option value="">Semua peserta</option>
                    <option value="sudah" @selected(($filter['status'] ?? '') === 'sudah')>Sudah mengumpulkan</option>
                    <option value="belum" @selected(($filter['status'] ?? '') === 'belum')>Belum mengumpulkan</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">Terapkan</button>
                <a href="{{ route('pengumpulan.rekap', $kegiatan) }}"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </div>
    </form>

    <section class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Daftar peserta</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">NIM</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Nama mahasiswa</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Waktu pengumpulan</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($peserta as $item)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-3 font-mono text-xs">{{ $item->nim }}</td>
                            <td class="px-6 py-3 font-medium text-slate-800">{{ $item->nama }}</td>
                            <td class="px-6 py-3">
                                @if ($item->pengumpulan_id)
                                    <span
                                        class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">Sudah
                                        mengumpulkan</span>
                                @else
                                    <span
                                        class="inline-flex rounded-full border border-rose-200 bg-rose-50 px-2.5 py-0.5 text-[11px] font-bold text-rose-700">Belum
                                        mengumpulkan</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs">
                                @if ($item->diubah_at)
                                    Diubah: {{ $waktu($item->diubah_at) }}
                                @elseif ($item->dikirim_at)
                                    {{ $waktu($item->dikirim_at) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                @if ($item->pengumpulan_id)
                                    <a href="{{ route('pengumpulan.show', $item->pengumpulan_id) }}"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Lihat
                                        jawaban</a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center italic text-slate-500">Tidak ada peserta sesuai
                                filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $peserta->links('pengumpulan._pagination') }}
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Riwayat pengumpulan</h2>
            <p class="mt-1 text-xs text-slate-500">Riwayat tetap disimpan untuk pemeriksaan, termasuk jawaban yang pernah
                dibatalkan.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Mahasiswa</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Dikirim</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Terakhir diubah</th>
                        <th scope="col" class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($historis as $item)
                        @php $batal = $item->status === \App\Models\Pengumpulan::DIBATALKAN; @endphp
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-3 font-medium text-slate-800">{{ $item->pemilik?->nama ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span
                                    class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $batal ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">{{ \App\Models\Pengumpulan::STATUS[$item->status] ?? $item->status }}</span>
                            </td>
                            <td class="px-6 py-3 text-xs">
                                {{ $item->dikirim_at ? $item->dikirim_at->setTimezone($zona)->format('d-m-Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-3 text-xs">
                                @if ($item->dibatalkan_at)
                                    Dibatalkan: {{ $item->dibatalkan_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                @elseif ($item->diubah_at)
                                    {{ $item->diubah_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('pengumpulan.show', $item) }}"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center italic text-slate-500">Belum ada riwayat
                                pengumpulan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $historis->links('pengumpulan._pagination') }}
    </section>
@endsection
