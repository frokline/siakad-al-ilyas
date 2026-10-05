@extends('layouts.admin')

@section('title', 'Detail Rombel')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Rombel {{ $rombel->kode }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $rombel->periodeAkademik->kode }} —
                {{ $rombel->paketSemester->kurikulum->programStudi->nama }}</p>
        </div>

        <div class="flex items-center gap-3">
            @if ($rombel->dapatDiubah())
                <a href="{{ route('admin.rombel.edit', $rombel) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Rombel
                </a>
            @endif

            <a href="{{ route('admin.rombel.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                Kembali
            </a>
        </div>
    </div>

    <!-- DETAIL CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-6">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Informasi Detail Rombel</h2>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode Rombel</dt>
                    <dd class="mt-1 font-mono font-bold text-slate-800 text-base">{{ $rombel->kode }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Periode Akademik</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        <a href="{{ route('admin.periode-akademik.show', $rombel->periodeAkademik) }}"
                            class="text-siakad-dark hover:underline">
                            {{ $rombel->periodeAkademik->kode }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Periode</dt>
                    <dd class="mt-1">
                        @php
                            $pStatus = $rombel->periodeAkademik->status;
                            $pBadge =
                                $pStatus === 'aktif'
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                    : 'bg-blue-50 text-blue-700 border-blue-100';
                        @endphp
                        <span
                            class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $pBadge }}">
                            {{ $statusPeriodeOptions[$pStatus] ?? $pStatus }}
                        </span>
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Program Studi</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        <a href="{{ route('admin.program-studi.show', $rombel->paketSemester->kurikulum->programStudi) }}"
                            class="text-siakad-dark hover:underline">
                            {{ $rombel->paketSemester->kurikulum->programStudi->nama }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kurikulum</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        <a href="{{ route('admin.kurikulum.show', $rombel->paketSemester->kurikulum) }}"
                            class="text-siakad-dark hover:underline">
                            {{ $rombel->paketSemester->kurikulum->kode }} — {{ $rombel->paketSemester->kurikulum->nama }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Paket Semester</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        <a href="{{ route('admin.paket-semester.show', $rombel->paketSemester) }}"
                            class="text-siakad-dark hover:underline">
                            {{ $rombel->paketSemester->nama }} — Versi {{ $rombel->paketSemester->versi }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Paket</dt>
                    <dd class="mt-1">
                        @php
                            $sPaket = $rombel->paketSemester->status;
                            $sBadge =
                                $sPaket === 'diterbitkan'
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                    : 'bg-amber-50 text-amber-700 border-amber-100';
                        @endphp
                        <span
                            class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $sBadge }}">
                            {{ $statusPaketOptions[$sPaket] ?? $sPaket }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Semester Studi</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $rombel->paketSemester->semester_studi }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kapasitas Mahasiswa</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        @if ($rombel->kapasitas === null)
                            Tanpa batas kapasitas
                        @else
                            {{ $rombel->kapasitas }} mahasiswa
                        @endif
                    </dd>
                </div>
            </dl>

            @if (!$rombel->dapatDiubah())
                <div class="mt-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800">
                    Periode sudah diarsipkan. Data rombel hanya dapat dilihat.
                </div>
            @endif

            @if ($rombel->paketSemester->isArsip())
                <div class="mt-4 rounded-lg bg-slate-50 border border-slate-200 p-4 text-xs text-slate-600">
                    Paket telah diarsipkan. Rombel ini tetap menyimpan hubungan dengan versi paket yang digunakan.
                </div>
            @endif

            <div class="mt-6 pt-5 border-t border-slate-200 flex items-center justify-end">
                <a href="{{ route('admin.paket-semester.show', $rombel->paketSemester) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                    Lihat Isi Paket
                </a>
            </div>
        </div>
    </div>
@endsection
