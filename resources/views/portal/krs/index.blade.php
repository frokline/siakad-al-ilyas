@extends('layouts.admin')

@section('title', 'KRS Saya')

@section('content')
    <!-- Kepala Halaman -->
    <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <nav aria-label="Breadcrumb" class="mb-1">
                <ol class="flex items-center space-x-2 text-xs text-slate-500">
                    <li><span class="font-medium">Portal</span></li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">KRS Saya</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Kartu Rencana Studi</h1>
            <p class="text-sm text-slate-500">Daftar Kartu Rencana Studi (KRS) setiap semester akademik.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('portal.krs.index') }}"
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-7 items-end">
            <div class="md:col-span-3">
                <label for="periode_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                    Periode Akademik
                </label>
                <select id="periode_id" name="periode_id"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 transition-colors focus:border-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/20">
                    <option value="">Semua Periode</option>
                    @if ($daftarPeriode && $daftarPeriode->count() > 0)
                        @foreach ($daftarPeriode as $periode)
                            <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>
                                {{ $periode->kode }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="status" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                    Status KRS
                </label>
                <select id="status" name="status"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 transition-colors focus:border-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/20">
                    <option value="">Semua Status</option>
                    @foreach (\App\Models\Krs::STATUS as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(($filter['status'] ?? '') === $nilai)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 md:col-span-2">
                <button type="submit"
                    class="flex-1 rounded-xl bg-siakad-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/50">
                    Terapkan
                </button>
                <a href="{{ route('portal.krs.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar KRS -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Daftar KRS Mahasiswa</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Periode
                        </th>
                        <th scope="col"
                            class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Semester
                        </th>
                        <th scope="col"
                            class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Rombel
                        </th>
                        <th scope="col"
                            class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Total
                            SKS</th>
                        <th scope="col"
                            class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Versi
                        </th>
                        <th scope="col"
                            class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @if ($daftarKrs && $daftarKrs->count() > 0)
                        @foreach ($daftarKrs as $krs)
                            @php
                                $registrasi = $krs->registrasiSemester;
                                $statusLabel = \App\Models\Krs::STATUS[$krs->status] ?? $krs->status;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-900">
                                    {{ $registrasi->periodeAkademik->kode }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                    Semester {{ $registrasi->semester_studi }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                    {{ $registrasi->rombel->kode ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
                                    {{ $krs->totalSks() }} SKS
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span
                                        class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $krs->status === \App\Models\Krs::DISAHKAN ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                    v{{ $krs->versi }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('portal.krs.show', $krs) }}"
                                            class="text-siakad-dark hover:underline font-semibold">
                                            Detail
                                        </a>
                                        @if ($krs->status === \App\Models\Krs::DISAHKAN)
                                            <span class="text-slate-300">&middot;</span>
                                            <a href="{{ route('portal.krs.cetak', $krs) }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 text-emerald-700 hover:text-emerald-900 font-semibold">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                                Cetak
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">
                                Belum ada Kartu Rencana Studi (KRS) yang dapat ditampilkan.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if ($daftarKrs->hasPages())
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50">
                {{ $daftarKrs->links() }}
            </div>
        @endif
    </div>
@endsection
