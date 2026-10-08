@extends('layouts.admin')

@section('title', 'Buat Tagihan')

@section('content')
    @php
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
    @endphp

    @include('tagihan._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('tagihan.index') }}" class="hover:text-siakad-active">Tagihan</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Buat draf</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Buat draf tagihan</h1>
            <p class="mt-1 text-sm text-slate-500">Tagihan tersimpan sebagai draf dan baru terlihat mahasiswa setelah
                diterbitkan.</p>
        </div>
        <a href="{{ route('tagihan.index') }}"
            class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
            Daftar tagihan</a>
    </div>

    {{-- Penanda langkah --}}
    <ol class="mb-6 flex items-center gap-3 text-sm">
        <li class="flex items-center gap-2">
            <span
                class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $pilihan ? 'bg-siakad-active text-white' : 'bg-siakad-dark text-white ring-4 ring-emerald-100' }}">1</span>
            <span class="font-semibold {{ $pilihan ? 'text-slate-500' : 'text-slate-800' }}">Pilih registrasi</span>
        </li>
        <span class="h-px w-8 bg-slate-300"></span>
        <li class="flex items-center gap-2">
            <span
                class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $pilihan ? 'bg-siakad-dark text-white ring-4 ring-emerald-100' : 'bg-slate-200 text-slate-500' }}">2</span>
            <span class="font-semibold {{ $pilihan ? 'text-slate-800' : 'text-slate-500' }}">Isi tagihan</span>
        </li>
    </ol>

    @if ($pilihan)
        <div class="grid gap-6 lg:grid-cols-3">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Formulir tagihan</h2>
                </div>
                <form class="p-6" method="post" action="{{ route('tagihan.store') }}">
                    @include('tagihan._form', ['baru' => true])
                </form>
            </section>

            <aside class="space-y-4">
                <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-200">Registrasi terpilih</h2>
                    <p class="mt-3 text-lg font-bold">{{ $pilihan->nama }}</p>
                    <p class="text-sm text-emerald-100">{{ $pilihan->nim }}</p>
                    <dl class="mt-4 space-y-2 border-t border-white/10 pt-4 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-emerald-200">Registrasi</dt>
                            <dd class="font-semibold">#{{ $pilihan->id }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-emerald-200">Periode</dt>
                            <dd class="font-semibold">#{{ $pilihan->periode_akademik_id }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-emerald-200">Semester</dt>
                            <dd class="font-semibold">{{ $pilihan->semester_studi }}</dd>
                        </div>
                    </dl>
                    <a href="{{ route('tagihan.create') }}"
                        class="mt-5 inline-flex rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20">Pilih
                        registrasi lain</a>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wider">Periksa bulan</h2>
                    <p class="mt-2">Pastikan bulan yang ditagihkan sesuai registrasi terpilih. Tagihan tidak dibuat
                        otomatis untuk 12 bulan.</p>
                </div>
            </aside>
        </div>
    @else
        <form method="get" action="{{ route('tagihan.create') }}"
            class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="cari" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Cari
                    NIM / nama</label>
                <input id="cari" name="cari" maxlength="100" value="{{ $filter['cari'] ?? '' }}"
                    placeholder="Ketik NIM atau nama mahasiswa" class="{{ $input }}">
            </div>
            <button type="submit"
                class="rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Cari
                registrasi</button>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="px-5 py-3">Mahasiswa</th>
                            <th class="px-5 py-3">Registrasi</th>
                            <th class="px-5 py-3">Periode / semester</th>
                            <th class="px-5 py-3 text-right">Pilih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($daftar as $r)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $r->nama }}</p>
                                    <p class="text-xs text-slate-500">{{ $r->nim }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">#{{ $r->id }} &middot;
                                    <span
                                        class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ $r->status }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-700">#{{ $r->periode_akademik_id }} / semester
                                    {{ $r->semester_studi }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('tagihan.create', ['registrasi' => $r->id]) }}"
                                        class="inline-flex rounded-lg bg-siakad-dark px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800">Pilih</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-slate-500">Tidak ada registrasi
                                    terdaftar atau aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $daftar->links('tagihan._pagination') }}
        </section>
    @endif
@endsection
