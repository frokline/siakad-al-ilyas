@extends('layouts.admin')

@section('title', 'Kalender Akademik')

@section('content')
    @php
        $zona = \App\Models\KalenderAkademik::ZONA;
        $hariIni = now($zona)->format('Y-m-d');
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $label = 'mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600';
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
            array_replace($filter, ['bulan' => now($zona)->format('Y-m'), 'hari' => null, 'page' => null]),
        );
    @endphp

    @include('kalender_akademik._pesan')

    {{-- Kepala halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Kalender akademik</h1>
            <p class="mt-1 text-sm text-slate-500">Agenda kampus dan program studi &middot; waktu WITA</p>
        </div>
        @can('create', \App\Models\KalenderAkademik::class)
            <a href="{{ route('kalender.create') }}"
                class="inline-flex items-center gap-2 self-start rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah agenda
            </a>
        @endcan
    </div>

    {{-- Filter --}}
    <form method="get" action="{{ route('kalender.index') }}" x-data="{ lanjut: {{ $lanjutan ? 'true' : 'false' }} }"
        class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="bulan" class="{{ $label }}">Bulan</label>
                <input type="month" id="bulan" name="bulan" min="2000-01" max="2100-12"
                    value="{{ $filter['bulan'] }}" required class="{{ $input }}">
            </div>
            <div>
                <label for="tampilan" class="{{ $label }}">Tampilan</label>
                <select id="tampilan" name="tampilan" class="{{ $input }}">
                    <option value="bulan" @selected($filter['tampilan'] === 'bulan')>Kalender bulanan</option>
                    <option value="daftar" @selected($filter['tampilan'] === 'daftar')>Daftar agenda</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="q" class="{{ $label }}">Cari judul</label>
                <input id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                    placeholder="Ketik judul agenda" class="{{ $input }}">
            </div>
        </div>

        <div x-show="lanjut" x-collapse @unless ($lanjutan) x-cloak @endunless>
            <div class="mt-4 grid gap-4 border-t border-slate-100 pt-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="periode" class="{{ $label }}">Periode</label>
                    <select id="periode" name="periode_akademik_id" class="{{ $input }}">
                        <option value="">Semua periode</option>
                        @foreach ($periode as $p)
                            <option value="{{ $p->id }}" @selected((string) ($filter['periode_akademik_id'] ?? '') === (string) $p->id)>{{ $p->kode }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="prodi" class="{{ $label }}">Program studi</label>
                    <select id="prodi" name="program_studi_id" class="{{ $input }}">
                        <option value="">Semua sasaran</option>
                        @foreach ($prodi as $p)
                            <option value="{{ $p->id }}" @selected((string) ($filter['program_studi_id'] ?? '') === (string) $p->id)>{{ $p->nama }} + kampus
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="jenis" class="{{ $label }}">Jenis</label>
                    <select id="jenis" name="jenis" class="{{ $input }}">
                        <option value="">Semua jenis</option>
                        @foreach (\App\Models\KalenderAkademik::JENIS as $kode => $nama)
                            <option value="{{ $kode }}" @selected(($filter['jenis'] ?? '') === $kode)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="{{ $label }}">Status</label>
                    <select id="status" name="status" class="{{ $input }}">
                        <option value="">Semua yang dapat dilihat</option>
                        @foreach (\App\Models\KalenderAkademik::STATUS as $kode => $nama)
                            @if ($kelola || $kode !== 'draf')
                                <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $nama }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="kampus_saja" value="1" @checked(!empty($filter['kampus_saja']))
                            class="h-4 w-4 rounded border-slate-300 text-siakad-active focus:ring-siakad-active">
                        Hanya agenda seluruh kampus
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-3">
            <button type="submit"
                class="rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Tampilkan</button>
            <a href="{{ route('kalender.index') }}"
                class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Reset</a>
            <button type="button" @click="lanjut = !lanjut"
                class="ml-auto inline-flex items-center gap-1.5 text-sm font-semibold text-siakad-active hover:text-siakad-dark">
                <span x-text="lanjut ? 'Sembunyikan filter lanjutan' : 'Filter lanjutan'"></span>
                @if ($lanjutan)
                    <span
                        class="rounded-full bg-siakad-accent px-2 py-0.5 text-[10px] font-bold text-siakad-dark">AKTIF</span>
                @endif
            </button>
        </div>
    </form>

    {{-- Navigasi bulan --}}
    <nav class="mb-4 flex flex-wrap items-center justify-between gap-3" aria-label="Navigasi bulan">
        <div class="flex items-center gap-2">
            @if ($filter['bulan'] > '2000-01')
                <a href="{{ route('kalender.index', array_replace($filter, ['bulan' => $bulan->subMonth()->format('Y-m'), 'hari' => null, 'page' => null])) }}"
                    class="inline-flex h-10 items-center gap-1 rounded-xl border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                    title="Bulan sebelumnya">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </a>
            @endif
            @if ($filter['bulan'] < '2100-12')
                <a href="{{ route('kalender.index', array_replace($filter, ['bulan' => $bulan->addMonth()->format('Y-m'), 'hari' => null, 'page' => null])) }}"
                    class="inline-flex h-10 items-center gap-1 rounded-xl border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                    title="Bulan berikutnya">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @endif
            <a href="{{ $bulanIni }}"
                class="inline-flex h-10 items-center rounded-xl bg-emerald-50 px-4 text-sm font-semibold text-siakad-active ring-1 ring-inset ring-emerald-200 hover:bg-emerald-100">Bulan
                ini</a>
        </div>
        <h2 class="text-xl font-bold text-slate-800">{{ $bulan->locale('id')->translatedFormat('F Y') }}</h2>
    </nav>

    @if ($lebih)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            Agenda terlalu banyak untuk satu kalender. Gunakan filter atau
            <a class="font-semibold underline"
                href="{{ route('kalender.index', array_replace($filter, ['tampilan' => 'daftar', 'page' => null])) }}">buka
                daftar lengkap</a>.
        </div>
    @elseif ($filter['tampilan'] === 'bulan')
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto" tabindex="0" role="region"
                aria-label="Kalender bulanan; dapat digeser pada layar kecil">
                <table class="w-full min-w-[860px] table-fixed border-collapse">
                    <caption class="sr-only">{{ $bulan->locale('id')->translatedFormat('F Y') }} &middot; WITA
                    </caption>
                    <thead>
                        <tr class="bg-siakad-dark text-white">
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $i => $h)
                                <th scope="col"
                                    class="px-2 py-3 text-center text-xs font-bold uppercase tracking-wider {{ $i === 6 ? 'text-rose-200' : '' }}">
                                    {{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($minggu as $pekan)
                            <tr>
                                @foreach ($pekan as $i => $hari)
                                    @php $tgl = $hari['tanggal']->format('Y-m-d'); @endphp
                                    <td
                                        class="h-32 border border-slate-100 p-1.5 align-top {{ $hari['bulan_ini'] ? 'bg-white' : 'bg-slate-50/70' }}">
                                        <div class="mb-1 flex justify-end">
                                            <time datetime="{{ $tgl }}"
                                                class="flex h-7 min-w-7 items-center justify-center rounded-full px-1 text-xs font-bold
                                                {{ $tgl === $hariIni ? 'bg-siakad-dark text-white' : ($hari['bulan_ini'] ? ($i === 6 ? 'text-rose-600' : 'text-slate-700') : 'text-slate-300') }}">{{ $hari['tanggal']->day }}</time>
                                        </div>
                                        @if ($hari['bulan_ini'])
                                            <div class="space-y-1">
                                                @foreach ($hari['agenda']->take(3) as $a)
                                                    <a href="{{ route('kalender.show', $a) }}"
                                                        title="{{ $a->judul }} · {{ \App\Models\KalenderAkademik::JENIS[$a->jenis] }} · {{ \App\Models\KalenderAkademik::STATUS[$a->status] }}"
                                                        class="block truncate rounded-md border px-1.5 py-1 text-[11px] font-semibold leading-tight transition {{ $chip[$a->jenis] ?? $chip['lainnya'] }} {{ $a->status === 'batal' ? 'line-through opacity-60' : '' }}">
                                                        @if ($a->status === 'draf')
                                                            <span class="mr-0.5 font-bold not-italic">[Draf]</span>
                                                        @endif
                                                        {{ $a->judul }}
                                                    </a>
                                                @endforeach
                                                @if ($hari['agenda']->count() > 3)
                                                    <a href="{{ route('kalender.index', array_replace($filter, ['tampilan' => 'daftar', 'hari' => $tgl, 'page' => null])) }}"
                                                        class="block px-1 text-[11px] font-semibold text-siakad-active hover:underline">+
                                                        {{ $hari['agenda']->count() - 3 }} agenda lain</a>
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

            {{-- Legenda --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-slate-100 bg-slate-50 px-5 py-3">
                @foreach (\App\Models\KalenderAkademik::JENIS as $kode => $nama)
                    <span class="inline-flex items-center gap-2 text-xs text-slate-600"><span
                            class="h-2.5 w-2.5 rounded-full {{ $titik[$kode] }}"></span>{{ $nama }}</span>
                @endforeach
                <span class="inline-flex items-center gap-2 text-xs text-slate-600"><span
                        class="text-xs line-through">Dibatalkan</span></span>
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">Agenda beberapa hari tampil pada tiap hari yang dicakupnya. Klik judul
            untuk jam dan detail. Penanda <strong>Dibatalkan</strong> (judul dicoret) berarti agenda tidak berlaku.</p>
    @else
        @if (!empty($filter['hari']))
            <div
                class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                <span>Daftar untuk tanggal <strong>{{ $filter['hari'] }}</strong>.</span>
                <a class="font-semibold underline"
                    href="{{ route('kalender.index', array_replace($filter, ['hari' => null, 'page' => null])) }}">Lihat
                    seluruh bulan</a>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="px-5 py-3">Agenda</th>
                            <th class="px-5 py-3">Waktu (WITA)</th>
                            <th class="px-5 py-3">Sasaran</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($daftar as $a)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <a href="{{ route('kalender.show', $a) }}"
                                        class="font-semibold text-slate-800 hover:text-siakad-active {{ $a->status === 'batal' ? 'line-through opacity-70' : '' }}">{{ $a->judul }}</a>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                        @include('kalender_akademik._lencana', [
                                            'tipe' => 'jenis',
                                            'nilai' => $a->jenis,
                                        ])
                                        <span class="text-xs text-slate-500">{{ $a->periodeAkademik->kode }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-slate-700">
                                    {{ $a->mulai_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                    <div class="text-xs text-slate-500">sampai
                                        {{ $a->selesai_at->setTimezone($zona)->format('d-m-Y H:i') }}</div>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $a->programStudi?->nama ?? 'Seluruh kampus' }}
                                </td>
                                <td class="px-5 py-4">
                                    @include('kalender_akademik._lencana', [
                                        'tipe' => 'status',
                                        'nilai' => $a->status,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                                    <svg class="mx-auto mb-3 h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Tidak ada agenda sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $daftar->links('kalender_akademik._pagination') }}
        </section>
    @endif
@endsection
