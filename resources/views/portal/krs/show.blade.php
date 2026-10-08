@extends('layouts.admin')

@section('title', 'Detail KRS')

@section('content')
    <!-- Kepala Halaman -->
    <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <nav aria-label="Breadcrumb" class="mb-1">
                <ol class="flex items-center space-x-2 text-xs text-slate-500">
                    <li><span class="font-medium">Portal</span></li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li><a href="{{ route('portal.krs.index') }}" class="hover:text-siakad-dark hover:underline">KRS Saya</a>
                    </li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">Detail</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Detail Kartu Rencana Studi</h1>
            <p class="text-sm text-slate-500">
                Periode {{ $registrasi->periodeAkademik->kode }} &middot; Semester {{ $registrasi->semester_studi }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('portal.krs.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
            @if ($bolehCetak)
                <a href="{{ route('portal.krs.cetak', $krs) }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak KRS
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Kolom Kiri: Daftar Mata Kuliah -->
        <div class="space-y-6 lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Daftar Mata Kuliah</h2>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                        {{ $krs->totalSks() }} SKS
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 w-12">
                                    No.</th>
                                <th scope="col"
                                    class="px-4 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Kode Kelas</th>
                                <th scope="col"
                                    class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Mata Kuliah</th>
                                <th scope="col"
                                    class="px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                                    SKS</th>
                                <th scope="col"
                                    class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @if ($krs->details && $krs->details->count() > 0)
                                @foreach ($krs->details as $detail)
                                    @php
                                        $detailStatus =
                                            \App\Models\DetailKrs::STATUS[$detail->status] ?? $detail->status;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td
                                            class="whitespace-nowrap px-4 py-4 text-center text-sm font-medium text-slate-500">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-800">
                                            {{ $detail->kelasKuliah->kode }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                            {{ $detail->kelasKuliah->nama_mk_snapshot }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-4 py-4 text-center text-sm font-semibold text-slate-800">
                                            {{ $detail->kelasKuliah->sks_snapshot }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-700">
                                            <span
                                                class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-600/10">
                                                {{ $detailStatus }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                        Detail mata kuliah belum tersedia.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-slate-50 font-semibold text-slate-800">
                            <tr>
                                <td colspan="3"
                                    class="px-6 py-3.5 text-right text-xs uppercase tracking-wider text-slate-600">Total
                                    Beban SKS:</td>
                                <td class="px-4 py-3.5 text-center text-sm font-bold text-siakad-dark">
                                    {{ $krs->totalSks() }} SKS</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if ($krs->catatan)
                <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-1">Catatan Pembimbing</h3>
                    <p class="text-sm text-amber-900 leading-relaxed">{{ $krs->catatan }}</p>
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Identitas & Status KRS -->
        <aside class="space-y-6">
            <!-- Informasi Mahasiswa -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Informasi Mahasiswa</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-slate-500">Nama Lengkap</dt>
                            <dd class="font-medium text-slate-900 mt-0.5">
                                {{ $registrasi->riwayatStudi->mahasiswa->user->nama }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500">NIM</dt>
                            <dd class="font-medium text-siakad-dark mt-0.5">{{ $registrasi->riwayatStudi->mahasiswa->nim }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500">Program Studi</dt>
                            <dd class="font-medium text-slate-900 mt-0.5">
                                {{ $registrasi->riwayatStudi->kurikulum->programStudi->nama }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500">Kurikulum</dt>
                            <dd class="font-medium text-slate-900 mt-0.5">{{ $registrasi->riwayatStudi->kurikulum->nama }}
                            </dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-3">
                            <div>
                                <dt class="text-xs font-semibold text-slate-500">Periode</dt>
                                <dd class="font-medium text-slate-900 mt-0.5">{{ $registrasi->periodeAkademik->kode }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500">Semester Studi</dt>
                                <dd class="font-medium text-slate-900 mt-0.5">Semester {{ $registrasi->semester_studi }}
                                </dd>
                            </div>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500">Rombel</dt>
                            <dd class="font-medium text-slate-900 mt-0.5">{{ $registrasi->rombel->kode ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Panel Status KRS -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Status Pengesahan</h2>
                </div>
                <div class="p-6">
                    <dl class="space-y-4 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-xs font-semibold text-slate-500">Status KRS</dt>
                            <dd>
                                <span
                                    class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $krs->status === \App\Models\Krs::DISAHKAN ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">
                                    {{ \App\Models\Krs::STATUS[$krs->status] ?? $krs->status }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-xs font-semibold text-slate-500">Versi Dokumentasi</dt>
                            <dd class="font-semibold text-slate-800">v{{ $krs->versi }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-xs font-semibold text-slate-500">Total Kredit</dt>
                            <dd class="font-semibold text-slate-800">{{ $krs->totalSks() }} SKS</dd>
                        </div>
                        <div class="border-t border-slate-100 pt-3">
                            <dt class="text-xs font-semibold text-slate-500">Tanggal Diajukan</dt>
                            <dd class="font-medium text-slate-800 mt-0.5">
                                {{ $krs->diajukan_at?->setTimezone('Asia/Makassar')->format('d-m-Y H:i') ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500">Tanggal Disahkan</dt>
                            <dd class="font-medium text-slate-800 mt-0.5">
                                {{ $krs->disahkan_at?->setTimezone('Asia/Makassar')->format('d-m-Y H:i') ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-slate-500">Pengesah / Dosen PA</dt>
                            <dd class="font-medium text-slate-900 mt-0.5">{{ $krs->pengesah?->nama ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </aside>
    </div>
@endsection
