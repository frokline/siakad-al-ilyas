@extends('layouts.admin')

@section('title', 'Kalender Akademik')

@section('content')
    @php
        $zona = \App\Models\KalenderAkademik::ZONA;
        $hariIni = now($zona)->format('Y-m-d');

        $input =
            'w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-emerald-700/15';
        $label = 'mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600';

        $chip = [
            'krs' => 'border-sky-200 bg-sky-50 text-sky-800 hover:bg-sky-100',
            'kuliah' => 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100',
            'ujian' => 'border-rose-200 bg-rose-50 text-rose-800 hover:bg-rose-100',
            'libur' => 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100',
            'lainnya' => 'border-slate-200 bg-slate-100 text-slate-700 hover:bg-slate-200',
        ];

        $titik = [
            'krs' => 'bg-sky-500',
            'kuliah' => 'bg-emerald-500',
            'ujian' => 'bg-rose-500',
            'libur' => 'bg-amber-500',
            'lainnya' => 'bg-slate-400',
        ];

        $lanjutan =
            !empty($filter['periode_akademik_id']) ||
            !empty($filter['program_studi_id']) ||
            !empty($filter['jenis']) ||
            !empty($filter['status']) ||
            !empty($filter['kampus_saja']);

        $bulanIni = route(
            'kalender.index',
            array_replace($filter, [
                'bulan' => now($zona)->format('Y-m'),
                'hari' => null,
                'page' => null,
            ]),
        );
    @endphp

    @include('kalender_akademik._pesan')

    {{-- Header halaman --}}
    <section
        class="relative mb-6 overflow-hidden rounded-2xl border border-emerald-900/10 bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-800 p-5 text-white shadow-lg shadow-emerald-950/10 sm:rounded-3xl sm:p-7">

        <div
            class="pointer-events-none absolute -right-12 -top-16 h-48 w-48 rounded-full bg-emerald-300/10 blur-3xl sm:h-64 sm:w-64">
        </div>
        <div class="pointer-events-none absolute -bottom-16 left-1/3 h-40 w-40 rounded-full bg-teal-300/10 blur-3xl"></div>

        <div class="relative flex min-w-0 flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div
                    class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-50">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                    </svg>
                    Informasi Akademik
                </div>

                <h1 class="break-words text-2xl font-bold tracking-tight sm:text-3xl">
                    Kalender Akademik
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-emerald-100/85 sm:text-base">
                    Pantau agenda kampus, jadwal perkuliahan, pengisian KRS, ujian, dan hari libur dalam satu tempat.
                </p>

                <div
                    class="mt-4 inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/10 px-3 py-2 text-xs text-emerald-50">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Zona waktu: {{ $zona }}
                </div>
            </div>

            @can('create', \App\Models\KalenderAkademik::class)
                <div class="w-full shrink-0 sm:w-auto">
                    <a href="{{ route('kalender.create') }}"
                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-emerald-950 shadow-sm transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-white/80 focus:ring-offset-2 focus:ring-offset-emerald-900 active:scale-[0.99] sm:w-auto">

                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>

                        Tambah Agenda
                    </a>
                </div>
            @endcan
        </div>
    </section>

    {{-- Filter agenda --}}
    <form method="get" action="{{ route('kalender.index') }}" x-data="{ lanjut: {{ $lanjutan ? 'true' : 'false' }} }"
        class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">

        <div class="mb-4 flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10m-7 6h4" />
                </svg>
            </span>
            <div class="min-w-0">
                <h2 class="text-sm font-bold text-slate-900 sm:text-base">Filter Agenda</h2>
                <p class="mt-1 text-xs leading-relaxed text-slate-500">
                    Pilih bulan dan jenis tampilan, atau gunakan filter lanjutan untuk mempersempit hasil.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="min-w-0">
                <label for="bulan" class="{{ $label }}">Bulan</label>
                <input type="month" id="bulan" name="bulan" min="2000-01" max="2100-12"
                    value="{{ $filter['bulan'] }}" required class="{{ $input }}">
            </div>

            <div class="min-w-0">
                <label for="tampilan" class="{{ $label }}">Tampilan</label>
                <select id="tampilan" name="tampilan" class="{{ $input }}">
                    <option value="bulan" @selected($filter['tampilan'] === 'bulan')>Kalender bulanan</option>
                    <option value="daftar" @selected($filter['tampilan'] === 'daftar')>Daftar agenda</option>
                </select>
            </div>

            <div class="min-w-0 sm:col-span-2">
                <label for="q" class="{{ $label }}">Cari Judul Agenda</label>
                <input id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                    placeholder="Contoh: pengisian KRS atau ujian" class="{{ $input }}">
            </div>
        </div>

        {{-- Filter lanjutan --}}
        <div x-show="lanjut" x-collapse @unless ($lanjutan) x-cloak @endunless>

            <div class="mt-5 grid grid-cols-1 gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2 xl:grid-cols-4">

                <div class="min-w-0">
                    <label for="periode" class="{{ $label }}">Periode Akademik</label>
                    <select id="periode" name="periode_akademik_id" class="{{ $input }}">
                        <option value="">Semua periode</option>
                        @foreach ($periode as $p)
                            <option value="{{ $p->id }}" @selected((string) ($filter['periode_akademik_id'] ?? '') === (string) $p->id)>
                                {{ $p->kode }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-0">
                    <label for="prodi" class="{{ $label }}">Program Studi</label>
                    <select id="prodi" name="program_studi_id" class="{{ $input }}">
                        <option value="">Semua sasaran</option>
                        @foreach ($prodi as $p)
                            <option value="{{ $p->id }}" @selected((string) ($filter['program_studi_id'] ?? '') === (string) $p->id)>
                                {{ $p->nama }} + kampus
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-0">
                    <label for="jenis" class="{{ $label }}">Jenis Agenda</label>
                    <select id="jenis" name="jenis" class="{{ $input }}">
                        <option value="">Semua jenis</option>
                        @foreach (\App\Models\KalenderAkademik::JENIS as $kode => $nama)
                            <option value="{{ $kode }}" @selected(($filter['jenis'] ?? '') === $kode)>
                                {{ $nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-0">
                    <label for="status" class="{{ $label }}">Status Agenda</label>
                    <select id="status" name="status" class="{{ $input }}">
                        <option value="">Semua yang dapat dilihat</option>
                        @foreach (\App\Models\KalenderAkademik::STATUS as $kode => $nama)
                            @if ($kelola || $kode !== 'draf')
                                <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>
                                    {{ $nama }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 xl:col-span-4">
                    <label
                        class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 transition hover:bg-emerald-50">
                        <input type="checkbox" name="kampus_saja" value="1" @checked(!empty($filter['kampus_saja']))
                            class="h-4 w-4 shrink-0 rounded border-slate-300 text-siakad-active focus:ring-siakad-active">

                        <span>Hanya agenda seluruh kampus</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Tombol filter --}}
        <div class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:flex-wrap sm:items-center">
            <button type="submit"
                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-siakad-dark px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-700/30 active:scale-[0.99] sm:w-auto">

                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>

                Tampilkan Agenda
            </button>

            <a href="{{ route('kalender.index') }}"
                class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 sm:w-auto">

                Reset Filter
            </a>

            <button type="button" @click="lanjut = !lanjut" :aria-expanded="lanjut.toString()"
                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-slate-50 px-4 py-3 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-700/20 sm:ml-auto sm:w-auto">

                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10m-7 6h4" />
                </svg>

                <span x-text="lanjut ? 'Sembunyikan filter lanjutan' : 'Filter lanjutan'"></span>

                @if ($lanjutan)
                    <span
                        class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-900">AKTIF</span>
                @endif
            </button>
        </div>
    </form>

    {{-- Navigasi bulan --}}
    <section class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center justify-between gap-2 sm:order-1 sm:justify-start">
                @if ($filter['bulan'] > '2000-01')
                    <a href="{{ route('kalender.index', array_replace($filter, ['bulan' => $bulan->subMonth()->format('Y-m'), 'hari' => null, 'page' => null])) }}"
                        class="inline-flex min-h-10 min-w-10 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-700/20"
                        title="Bulan sebelumnya" aria-label="Bulan sebelumnya">

                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>

                        <span class="hidden sm:inline">Sebelumnya</span>
                    </a>
                @endif

                <a href="{{ $bulanIni }}"
                    class="inline-flex min-h-10 items-center justify-center rounded-xl bg-emerald-50 px-4 text-sm font-bold text-emerald-900 ring-1 ring-inset ring-emerald-200 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-700/20">

                    Bulan Ini
                </a>

                @if ($filter['bulan'] < '2100-12')
                    <a href="{{ route('kalender.index', array_replace($filter, ['bulan' => $bulan->addMonth()->format('Y-m'), 'hari' => null, 'page' => null])) }}"
                        class="inline-flex min-h-10 min-w-10 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-700/20"
                        title="Bulan berikutnya" aria-label="Bulan berikutnya">

                        <span class="hidden sm:inline">Berikutnya</span>

                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endif
            </div>

            <div class="min-w-0 text-center sm:order-2 sm:text-right">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Periode Kalender</p>
                <h2 class="mt-1 break-words text-xl font-bold capitalize tracking-tight text-slate-900 sm:text-2xl">
                    {{ $bulan->locale('id')->translatedFormat('F Y') }}
                </h2>
            </div>
        </div>
    </section>

    {{-- Peringatan jika agenda terlalu banyak --}}
    @if ($lebih)
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 sm:p-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.3 3.86L1.82 18a2 2 0 001.7 3h16.96a2 2 0 001.7-3L13.7 3.86a2 2 0 00-3.4 0z" />
                    </svg>
                </span>

                <div class="min-w-0">
                    <h2 class="font-bold text-amber-950">Agenda terlalu banyak untuk ditampilkan sekaligus</h2>
                    <p class="mt-1 text-sm leading-relaxed text-amber-900">
                        Gunakan filter untuk mempersempit agenda atau buka daftar lengkap.
                    </p>

                    <a class="mt-4 inline-flex min-h-10 items-center justify-center rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-sm font-bold text-amber-950 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                        href="{{ route('kalender.index', array_replace($filter, ['tampilan' => 'daftar', 'page' => null])) }}">

                        Buka Daftar Agenda
                        <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @elseif ($filter['tampilan'] === 'bulan')
        {{-- Tampilan mobile dan tablet: agenda per tanggal --}}
        <div class="space-y-4 lg:hidden">
            @php
                $adaAgendaMobile = false;
            @endphp

            @foreach ($minggu as $pekan)
                @foreach ($pekan as $i => $hari)
                    @if ($hari['bulan_ini'] && $hari['agenda']->count() > 0)
                        @php
                            $adaAgendaMobile = true;
                            $tgl = $hari['tanggal']->format('Y-m-d');
                        @endphp

                        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div
                                class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-3.5 sm:px-5">
                                <div
                                    class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl
                                    {{ $tgl === $hariIni ? 'bg-siakad-dark text-white' : 'bg-white text-slate-800 ring-1 ring-slate-200' }}">

                                    <span class="text-lg font-bold leading-none">
                                        {{ $hari['tanggal']->day }}
                                    </span>
                                    <span class="mt-1 text-[9px] font-bold uppercase tracking-wider opacity-80">
                                        {{ $hari['tanggal']->locale('id')->translatedFormat('M') }}
                                    </span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 class="break-words text-sm font-bold text-slate-900">
                                        {{ $hari['tanggal']->locale('id')->translatedFormat('l') }}
                                    </h3>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $hari['tanggal']->locale('id')->translatedFormat('d F Y') }}
                                    </p>
                                </div>

                                @if ($tgl === $hariIni)
                                    <span
                                        class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-900">
                                        Hari ini
                                    </span>
                                @endif
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach ($hari['agenda'] as $a)
                                    <a href="{{ route('kalender.show', $a) }}"
                                        class="group block min-w-0 p-4 transition hover:bg-slate-50 focus:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-700/20 sm:px-5">

                                        <div class="flex min-w-0 items-start gap-3">
                                            <span
                                                class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $titik[$a->jenis] ?? $titik['lainnya'] }}"></span>

                                            <div class="min-w-0 flex-1">
                                                <h4
                                                    class="break-words text-sm font-bold leading-relaxed text-slate-900 transition group-hover:text-emerald-900 {{ $a->status === 'batal' ? 'line-through opacity-60' : '' }}">
                                                    {{ $a->judul }}
                                                </h4>

                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    @include('kalender_akademik._lencana', [
                                                        'tipe' => 'jenis',
                                                        'nilai' => $a->jenis,
                                                    ])

                                                    <span class="text-xs text-slate-500">
                                                        {{ $a->periodeAkademik->kode }}
                                                    </span>
                                                </div>

                                                <div
                                                    class="mt-3 flex flex-col gap-2 text-xs text-slate-600 sm:flex-row sm:flex-wrap sm:items-center sm:gap-4">
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                            <circle cx="12" cy="12" r="9" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M12 7v5l3 2" />
                                                        </svg>

                                                        {{ $a->mulai_at->setTimezone($zona)->format('H:i') }}
                                                        &ndash;
                                                        {{ $a->selesai_at->setTimezone($zona)->format('H:i') }}
                                                        WITA
                                                    </span>

                                                    <span class="inline-flex items-center gap-1.5">
                                                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z" />
                                                            <circle cx="12" cy="10" r="2.5" />
                                                        </svg>

                                                        <span class="break-words">
                                                            {{ $a->programStudi?->nama ?? 'Seluruh kampus' }}
                                                        </span>
                                                    </span>
                                                </div>
                                            </div>

                                            <svg class="mt-1 h-4 w-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-emerald-700"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                            </svg>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            @endforeach

            @unless ($adaAgendaMobile)
                <section
                    class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center shadow-sm sm:py-12">
                    <span
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-800">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                        </svg>
                    </span>

                    <h3 class="mt-4 text-sm font-bold text-slate-900">Belum ada agenda pada bulan ini</h3>
                    <p class="mx-auto mt-1 max-w-sm text-sm leading-relaxed text-slate-500">
                        Agenda yang sesuai filter akan ditampilkan di sini. Coba ganti bulan atau sesuaikan filter.
                    </p>

                    <a href="{{ $bulanIni }}"
                        class="mt-4 inline-flex min-h-10 items-center justify-center rounded-xl bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-900 ring-1 ring-inset ring-emerald-200 transition hover:bg-emerald-100">
                        Kembali ke Bulan Ini
                    </a>
                </section>
            @endunless
        </div>

        {{-- Tampilan desktop: kalender grid --}}
        <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:block">
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Kalender bulanan">
                <table class="w-full min-w-[900px] table-fixed border-collapse">
                    <caption class="sr-only">
                        {{ $bulan->locale('id')->translatedFormat('F Y') }} &middot; WITA
                    </caption>

                    <thead>
                        <tr class="bg-siakad-dark text-white">
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $i => $h)
                                <th scope="col"
                                    class="border-r border-white/10 px-2 py-3.5 text-center text-xs font-bold uppercase tracking-wider last:border-r-0 {{ $i === 6 ? 'text-rose-200' : '' }}">
                                    {{ $h }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($minggu as $pekan)
                            <tr>
                                @foreach ($pekan as $i => $hari)
                                    @php
                                        $tgl = $hari['tanggal']->format('Y-m-d');
                                    @endphp

                                    <td
                                        class="h-36 border border-slate-100 p-2 align-top {{ $hari['bulan_ini'] ? 'bg-white' : 'bg-slate-50/60' }}">
                                        <div class="mb-2 flex justify-end">
                                            <time datetime="{{ $tgl }}"
                                                class="flex h-8 min-w-8 items-center justify-center rounded-full px-1.5 text-xs font-bold
                                                {{ $tgl === $hariIni
                                                    ? 'bg-siakad-dark text-white shadow-sm'
                                                    : ($hari['bulan_ini']
                                                        ? ($i === 6
                                                            ? 'text-rose-600'
                                                            : 'text-slate-700')
                                                        : 'text-slate-300') }}">

                                                {{ $hari['tanggal']->day }}
                                            </time>
                                        </div>

                                        @if ($hari['bulan_ini'])
                                            <div class="space-y-1.5">
                                                @foreach ($hari['agenda']->take(3) as $a)
                                                    <a href="{{ route('kalender.show', $a) }}"
                                                        title="{{ $a->judul }} · {{ \App\Models\KalenderAkademik::JENIS[$a->jenis] }} · {{ \App\Models\KalenderAkademik::STATUS[$a->status] }}"
                                                        class="block truncate rounded-lg border px-2 py-1.5 text-[11px] font-semibold leading-tight transition focus:outline-none focus:ring-2 focus:ring-emerald-700/30 {{ $chip[$a->jenis] ?? $chip['lainnya'] }} {{ $a->status === 'batal' ? 'line-through opacity-60' : '' }}">

                                                        @if ($a->status === 'draf')
                                                            <span class="mr-0.5 font-bold">[Draf]</span>
                                                        @endif

                                                        {{ $a->judul }}
                                                    </a>
                                                @endforeach

                                                @if ($hari['agenda']->count() > 3)
                                                    <a href="{{ route('kalender.index', array_replace($filter, ['tampilan' => 'daftar', 'hari' => $tgl, 'page' => null])) }}"
                                                        class="block rounded-md px-1.5 py-1 text-[11px] font-bold text-emerald-800 transition hover:bg-emerald-50 hover:underline">

                                                        + {{ $hari['agenda']->count() - 3 }} agenda lainnya
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Legenda kategori --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-3 border-t border-slate-100 bg-slate-50/80 px-5 py-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Legenda</span>

                @foreach (\App\Models\KalenderAkademik::JENIS as $kode => $nama)
                    <span class="inline-flex items-center gap-2 text-xs font-medium text-slate-600">
                        <span class="h-2.5 w-2.5 rounded-full {{ $titik[$kode] }}"></span>
                        {{ $nama }}
                    </span>
                @endforeach

                <span class="inline-flex items-center gap-2 text-xs text-slate-500">
                    <span class="font-semibold line-through">Agenda</span>
                    Dibatalkan
                </span>
            </div>
        </div>

        <p class="mt-3 hidden text-xs leading-relaxed text-slate-500 lg:block">
            Klik judul agenda untuk melihat waktu dan detail. Pada setiap tanggal, maksimal tiga agenda ditampilkan di
            kalender; agenda berikutnya tersedia melalui tautan agenda lainnya.
        </p>
    @else
        {{-- Penanda filter tanggal --}}
        @if (!empty($filter['hari']))
            <div
                class="mb-4 flex flex-col gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-950 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-2">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-sky-700" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                    </svg>

                    <p>
                        Menampilkan agenda untuk tanggal
                        <strong>{{ $filter['hari'] }}</strong>.
                    </p>
                </div>

                <a class="inline-flex min-h-10 items-center justify-center rounded-xl border border-sky-200 bg-white px-4 py-2 text-sm font-bold text-sky-900 transition hover:bg-sky-100"
                    href="{{ route('kalender.index', array_replace($filter, ['hari' => null, 'page' => null])) }}">

                    Lihat Seluruh Bulan
                </a>
            </div>
        @endif

        {{-- Daftar agenda untuk mobile dan tablet --}}
        <div class="space-y-3 lg:hidden">
            @forelse ($daftar as $a)
                <a href="{{ route('kalender.show', $a) }}"
                    class="group block min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-emerald-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-700/30 active:scale-[0.995] sm:p-5">

                    <div class="flex min-w-0 items-start gap-3">
                        <span
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $a->jenis === 'krs' ? 'bg-sky-50 text-sky-800' : ($a->jenis === 'kuliah' ? 'bg-emerald-50 text-emerald-800' : ($a->jenis === 'ujian' ? 'bg-rose-50 text-rose-800' : ($a->jenis === 'libur' ? 'bg-amber-50 text-amber-800' : 'bg-slate-100 text-slate-700'))) }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                            </svg>
                        </span>

                        <div class="min-w-0 flex-1">
                            <h3
                                class="break-words text-sm font-bold leading-relaxed text-slate-900 transition group-hover:text-emerald-900">
                                {{ $a->judul }}
                            </h3>

                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                @include('kalender_akademik._lencana', [
                                    'tipe' => 'jenis',
                                    'nilai' => $a->jenis,
                                ])

                                <span class="break-words text-xs text-slate-500">
                                    {{ $a->periodeAkademik->kode }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-2 text-xs text-slate-600">
                                <div class="flex items-start gap-2">
                                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                                    </svg>

                                    <span class="break-words leading-relaxed">
                                        {{ $a->mulai_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                        &ndash;
                                        {{ $a->selesai_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                        WITA
                                    </span>
                                </div>

                                <div class="flex items-start gap-2">
                                    <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z" />
                                        <circle cx="12" cy="10" r="2.5" />
                                    </svg>

                                    <span class="break-words leading-relaxed">
                                        {{ $a->programStudi?->nama ?? 'Seluruh kampus' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <svg class="mt-1 h-4 w-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-emerald-700"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
                        @include('kalender_akademik._lencana', [
                            'tipe' => 'status',
                            'nilai' => $a->status,
                        ])

                        <span class="text-xs font-semibold text-emerald-800">
                            Lihat Detail
                            <span aria-hidden="true">&rarr;</span>
                        </span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center shadow-sm">
                    <span
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-800">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 01-2 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                        </svg>
                    </span>

                    <h3 class="mt-4 text-sm font-bold text-slate-900">Tidak ada agenda yang ditemukan</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                        Coba ubah filter pencarian atau pilih bulan lain.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Tabel daftar untuk desktop --}}
        <section class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:block">
            <div
                class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Daftar Agenda</h2>
                    <p class="mt-1 text-xs text-slate-500">Agenda yang sesuai dengan filter saat ini.</p>
                </div>

                <span
                    class="rounded-full bg-white px-3 py-1.5 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200">
                    {{ number_format($daftar->total(), 0, ',', '.') }} agenda
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[780px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th scope="col" class="px-5 py-4">Agenda</th>
                            <th scope="col" class="px-5 py-4">Waktu (WITA)</th>
                            <th scope="col" class="px-5 py-4">Sasaran</th>
                            <th scope="col" class="px-5 py-4">Status</th>
                            <th scope="col" class="px-5 py-4 text-right">Detail</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($daftar as $a)
                            <tr class="transition hover:bg-emerald-50/40">
                                <td class="px-5 py-4">
                                    <a href="{{ route('kalender.show', $a) }}"
                                        class="break-words font-bold text-slate-900 transition hover:text-emerald-800 {{ $a->status === 'batal' ? 'line-through opacity-70' : '' }}">

                                        {{ $a->judul }}
                                    </a>

                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        @include('kalender_akademik._lencana', [
                                            'tipe' => 'jenis',
                                            'nilai' => $a->jenis,
                                        ])

                                        <span class="text-xs text-slate-500">
                                            {{ $a->periodeAkademik->kode }}
                                        </span>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-slate-700">
                                    <div class="font-semibold">
                                        {{ $a->mulai_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        sampai {{ $a->selesai_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ $a->programStudi?->nama ?? 'Seluruh kampus' }}
                                </td>

                                <td class="px-5 py-4">
                                    @include('kalender_akademik._lencana', [
                                        'tipe' => 'status',
                                        'nilai' => $a->status,
                                    ])
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('kalender.show', $a) }}"
                                        class="inline-flex min-h-9 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700/20">

                                        Detail

                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M5 12h14m-6-6 6 6-6 6" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <p class="text-sm font-bold text-slate-800">Tidak ada agenda yang ditemukan</p>
                                    <p class="mt-1 text-sm text-slate-500">Coba sesuaikan filter pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Pagination --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
            {{ $daftar->links('kalender_akademik._pagination') }}
        </div>

    @endif

    {{-- Keterangan --}}
    <footer class="mt-6 border-t border-slate-200 pt-4">
        <p class="text-center text-xs leading-relaxed text-slate-500">
            Kalender Akademik &middot; Semua waktu ditampilkan berdasarkan zona waktu {{ $zona }}.
        </p>
    </footer>
@endsection
