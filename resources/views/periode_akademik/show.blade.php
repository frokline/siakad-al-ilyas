@extends('layouts.admin')

@section('title', 'Detail Periode Akademik')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">{{ $periodeAkademik->kode }}</h1>
            <p class="text-sm text-slate-500 mt-1">Tahun ajaran {{ $periodeAkademik->tahunAjaran() }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.periode-akademik.edit', $periodeAkademik) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Periode
            </a>

            <a href="{{ route('admin.periode-akademik.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                Kembali
            </a>
        </div>
    </div>

    <!-- DETAIL CARD -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Informasi Detail Periode Akademik</h2>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode Periode</dt>
                    <dd class="mt-1 font-mono font-bold text-slate-800 text-base">{{ $periodeAkademik->kode }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tahun Ajaran</dt>
                    <dd class="mt-1 font-semibold text-slate-700">{{ $periodeAkademik->tahunAjaran() }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Semester</dt>
                    <dd class="mt-1 font-semibold text-slate-700">{{ $jenisOptions[$periodeAkademik->jenis] }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mulai Perkuliahan</dt>
                    <dd class="mt-1 text-slate-700 font-medium">{{ $periodeAkademik->mulai->format('d/m/Y') }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Selesai Perkuliahan</dt>
                    <dd class="mt-1 text-slate-700 font-medium">{{ $periodeAkademik->selesai->format('d/m/Y') }}</dd>
                </div>

                <div class="border-b border-slate-100 pb-4">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Periode</dt>
                    <dd class="mt-1">
                        @php
                            $status = $periodeAkademik->status;
                            $badgeClass = match ($status) {
                                'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'persiapan' => 'bg-blue-50 text-blue-700 border-blue-100',
                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                            };
                        @endphp
                        <span
                            class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                            {{ $statusOptions[$status] }}
                        </span>
                    </dd>
                </div>

                <div class="border-b sm:border-b-0 border-slate-100 pb-4 sm:pb-0">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Zona Waktu Jadwal</dt>
                    <dd class="mt-1 text-slate-700 font-mono text-xs">{{ config('siakad.timezone') }}</dd>
                </div>

                <div class="border-b sm:border-b-0 border-slate-100 pb-4 sm:pb-0">
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Awal Pengisian KRS</dt>
                    <dd class="mt-1 text-slate-700 font-medium">
                        {{ $periodeAkademik->krs_mulai?->setTimezone(config('siakad.timezone'))->format('d/m/Y H:i') ?? 'Belum diatur' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Batas Akhir Pengisian KRS</dt>
                    <dd class="mt-1 text-slate-700 font-medium">
                        {{ $periodeAkademik->krs_selesai?->setTimezone(config('siakad.timezone'))->format('d/m/Y H:i') ?? 'Belum diatur' }}
                    </dd>
                </div>
            </dl>

            <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Jendela KRS Saat Ini:</span>
                @if ($periodeAkademik->isKrsOpen())
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Terbuka
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Tertutup
                    </span>
                @endif
            </div>
        </div>
    </div>
@endsection
