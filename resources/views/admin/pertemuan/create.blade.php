@extends('layouts.admin')
@section('title', 'Buat Pertemuan')

@section('content')
    @php
        // Bawa filter pencarian kelas agar tidak hilang saat pindah antara daftar kelas dan formulir.
        $bawaFilter = array_filter(
            [
                'q' => $filter['q'] ?? null,
                'periode_id' => $filter['periode_id'] ?? null,
                'kelas_page' => $filter['kelas_page'] ?? null,
            ],
            fn($nilai) => $nilai !== null && $nilai !== '',
        );
    @endphp
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Buat Pertemuan</h1>
            <p class="text-sm text-slate-500 mt-1">Pilih kelas lalu jadwalkan atau catat rencana sesi.</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('admin.pertemuan.index') }}">Daftar seluruh pertemuan</a>
    </div>

    @if ($kelas === null)
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Pilih Kelas Kuliah</h2>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('admin.pertemuan.create') }}"
                    class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <div class="sm:col-span-6 w-full">
                        <label for="q"
                            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Kode Kelas atau
                            MK</label>
                        <input id="q" name="q" type="search" maxlength="80" value="{{ $filter['q'] ?? '' }}"
                            placeholder="Cari..."
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    </div>
                    <div class="sm:col-span-4 w-full">
                        <label for="periode_id"
                            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Periode</label>
                        <select id="periode_id" name="periode_id"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                            <option value="">Semua periode terbuka</option>
                            @foreach ($daftarPeriode as $periode)
                                <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>{{ $periode->kode }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex gap-2 w-full">
                        <button
                            class="flex-1 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors text-center shadow-sm"
                            type="submit">Cari</button>
                        <a class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors text-center shadow-sm"
                            href="{{ route('admin.pertemuan.create') }}">Reset</a>
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto border-t border-slate-200">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Kelas & MK</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Rombel / Periode</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-center">Jml Sesi</th>
                            <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($daftarKelas as $pilihan)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-4">
                                    <strong
                                        class="font-bold text-slate-800 font-mono text-xs block group-hover:text-siakad-dark transition-colors">{{ $pilihan->kode }}</strong>
                                    <span class="text-slate-700 block mt-0.5">{{ $pilihan->nama_mk_snapshot }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $pilihan->rombel->kode }}</div>
                                    <span
                                        class="text-xs text-slate-500 font-normal mt-0.5 block">{{ $pilihan->rombel->periodeAkademik->kode }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-slate-100 text-xs font-bold text-slate-700 border border-slate-200">{{ $pilihan->pertemuan_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a class="inline-flex items-center gap-1 rounded-lg bg-siakad-dark px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-800 transition-colors shadow-sm"
                                        href="{{ route('admin.pertemuan.create', array_merge($bawaFilter, ['kelas_id' => $pilihan->id])) }}">Pilih
                                        kelas</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">Belum ada kelas terbuka
                                    yang sesuai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50"><x-pagination :paginator="$daftarKelas" /></div>
        </div>
    @else
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800">Identitas Kelas Terpilih</h2>
                <a href="{{ route('admin.pertemuan.create', $bawaFilter) }}"
                    class="text-xs font-semibold text-siakad-dark hover:underline">Ganti kelas lain</a>
            </div>
            <div class="p-6">@include('admin.pertemuan._kelas')</div>
        </div>

        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Formulir Rencana Sesi</h2>
            </div>
            <div class="p-6">
                @unless ($boleh)
                    <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700">
                        Kelas atau periode terkait belum terbuka, atau kelas belum memiliki daftar penugasan dosen aktif.
                    </div>
                @endunless
                <form method="POST" action="{{ route('admin.pertemuan.store') }}">@include('admin.pertemuan._form')</form>
            </div>
        </div>

        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Sesi Kelas yang Sudah Tercatat</h2>
            </div>
            @include('admin.pertemuan._daftar-sesi')
        </div>
    @endif
@endsection
