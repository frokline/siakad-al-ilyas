@extends('layouts.admin')

@section('title', 'Detail Paket Semester')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">{{ $paketSemester->nama }}</h1>
            <p class="text-sm text-slate-500 mt-1">Semester {{ $paketSemester->semester_studi }} — Versi
                {{ $paketSemester->versi }}</p>
        </div>

        <div class="flex items-center gap-3">
            @if ($paketSemester->isDraf())
                <a href="{{ route('admin.paket-semester.edit', $paketSemester) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Paket
                </a>
            @endif

            <a href="{{ route('admin.paket-semester.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                Kembali
            </a>
        </div>
    </div>

    <!-- DETAIL CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-6">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Informasi Detail Paket Semester</h2>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Program Studi</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $paketSemester->kurikulum->programStudi->nama }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kurikulum</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        <a href="{{ route('admin.kurikulum.show', $paketSemester->kurikulum) }}"
                            class="text-siakad-dark hover:underline">
                            {{ $paketSemester->kurikulum->kode }} — {{ $paketSemester->kurikulum->nama }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Semester Studi</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $paketSemester->semester_studi }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Versi Paket</dt>
                    <dd class="mt-1 font-mono font-bold text-slate-800">{{ $paketSemester->versi }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Mata Kuliah</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $paketSemester->details->count() }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total SKS Paket</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ number_format((float) $totalSks, 1, ',', '.') }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status</dt>
                    <dd class="mt-1">
                        @php
                            $status = $paketSemester->status;
                            $badgeClass = match ($status) {
                                'diterbitkan' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'arsip' => 'bg-slate-100 text-slate-600 border-slate-200',
                                default => 'bg-amber-50 text-amber-700 border-amber-100',
                            };
                        @endphp
                        <span
                            class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                            {{ $statusOptions[$status] }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="border-t border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Susunan Mata Kuliah</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16 text-center">No.</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Kode</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Mata Kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold">SKS</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Sifat</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status Mata Kuliah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($paketSemester->details as $detail)
                        @php
                            $item = $detail->kurikulumMataKuliah;
                            $mataKuliah = $item->mataKuliah;
                        @endphp

                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 text-center text-slate-500 font-mono text-xs">{{ $loop->iteration }}</td>

                            <td class="px-6 py-4 font-mono font-bold text-slate-800 text-xs">
                                <a href="{{ route('admin.mata-kuliah.show', $mataKuliah) }}"
                                    class="text-siakad-dark hover:underline">
                                    {{ $mataKuliah->kode }}
                                </a>
                            </td>

                            <td class="px-6 py-4 font-bold text-slate-800">{{ $mataKuliah->nama }}</td>

                            <td class="px-6 py-4 font-semibold text-slate-700">
                                {{ number_format((float) $item->sks, 1, ',', '.') }}
                            </td>

                            <td
                                class="px-6 py-4 font-medium {{ $item->sifat === 'wajib' ? 'text-siakad-dark' : 'text-amber-600' }}">
                                {{ $item->sifat === 'wajib' ? 'Wajib' : 'Pilihan' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                @php
                                    $mkAktif = $mataKuliah->aktif;
                                    $mkBadge = $mkAktif
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                        : 'bg-slate-100 text-slate-600 border-slate-200';
                                    $mkDot = $mkAktif ? 'bg-emerald-500' : 'bg-slate-400';
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $mkBadge }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $mkDot }}"></span>
                                    {{ $mkAktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Paket belum memiliki mata kuliah. Gunakan Edit Paket untuk memilih mata kuliah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($paketSemester->isDraf())
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-base font-bold text-slate-800 mb-2">Terbitkan Paket</h2>
            <p class="text-sm text-slate-500 mb-4">
                Setelah diterbitkan, nama dan susunan mata kuliah dikunci. Perubahan berikutnya menggunakan versi paket
                baru.
            </p>

            @if ($paketSemester->details->isEmpty())
                <div class="mb-4 text-xs font-semibold text-rose-600">Tambahkan mata kuliah sebelum menerbitkan paket.</div>
            @endif

            @if (!$indukAktif)
                <div class="mb-4 text-xs font-semibold text-rose-600">Kurikulum dan program studi harus aktif.</div>
            @endif

            @if ($adaMataKuliahNonaktif)
                <div class="mb-4 text-xs font-semibold text-rose-600">Ada mata kuliah nonaktif di dalam paket. Perbaiki
                    susunan paket atau aktifkan mata kuliah tersebut.</div>
            @endif

            <form method="POST" action="{{ route('admin.paket-semester.terbitkan', $paketSemester) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="version" value="{{ $version }}">

                <label class="flex items-center gap-3 cursor-pointer" for="konfirmasi-terbitkan">
                    <input class="h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark"
                        id="konfirmasi-terbitkan" name="konfirmasi" type="checkbox" value="1" required
                        @disabled(!$dapatTerbit)>
                    <span class="text-sm font-medium text-slate-700">
                        Saya sudah memeriksa mata kuliah dan total SKS, serta menyetujui penerbitan paket.
                    </span>
                </label>

                <button type="submit" @disabled(!$dapatTerbit)
                    class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2 disabled:opacity-50">
                    Terbitkan Paket
                </button>
            </form>
        </div>
    @endif

    @if (!$paketSemester->isArsip())
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 p-6">
            <h2 class="text-base font-bold text-slate-800 mb-2">Arsipkan Paket</h2>
            <p class="text-sm text-slate-500 mb-4">
                Paket arsip tetap tersimpan. Paket tidak dapat diedit atau diterbitkan kembali.
            </p>

            <form method="POST" action="{{ route('admin.paket-semester.arsipkan', $paketSemester) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="version" value="{{ $version }}">

                <label class="flex items-center gap-3 cursor-pointer" for="konfirmasi-arsipkan">
                    <input class="h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark"
                        id="konfirmasi-arsipkan" name="konfirmasi" type="checkbox" value="1" required>
                    <span class="text-sm font-medium text-slate-700">Saya menyetujui pengarsipan paket ini.</span>
                </label>

                <button type="submit"
                    class="rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                    Arsipkan Paket
                </button>
            </form>
        </div>
    @else
        <div class="rounded-xl bg-slate-50 border border-slate-200 p-6 text-sm text-slate-600">
            Paket ini sudah diarsipkan. Data dipertahankan sebagai catatan akademik.
        </div>
    @endif
@endsection
