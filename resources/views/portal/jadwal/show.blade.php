@extends('layouts.admin')

@section('title', 'Detail Jadwal Kuliah')

@section('content')
    <!-- Kepala Halaman -->
    <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <nav aria-label="Breadcrumb" class="mb-1">
                <ol class="flex items-center space-x-2 text-xs text-slate-500">
                    <li><span class="font-medium">Portal</span></li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li>
                        <a href="{{ route('portal.jadwal.index') }}" class="hover:underline">Jadwal Kuliah</a>
                    </li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">Detail Jadwal</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Detail Jadwal Kuliah</h1>
            <p class="text-sm text-slate-500">
                {{ $kelas?->nama_mk_snapshot ?? 'Informasi kelas perkuliahan' }}
            </p>
        </div>
        <a href="{{ route('portal.jadwal.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Jadwal
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Kartu 1: Informasi Kelas & Akademik -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Informasi Kelas</h2>
            </div>
            <div class="p-6">
                <dl class="divide-y divide-slate-100">
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-slate-500">Kode Kelas</dt>
                        <dd class="text-sm font-semibold text-slate-900">{{ $kelas?->kode ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-slate-500">Mata Kuliah</dt>
                        <dd class="text-sm font-semibold text-slate-900 text-right">{{ $kelas?->nama_mk_snapshot ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-slate-500">Bobot SKS</dt>
                        <dd class="text-sm font-semibold text-slate-900">{{ $kelas?->sks_snapshot ? $kelas->sks_snapshot . ' SKS' : '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-slate-500">Rombel</dt>
                        <dd class="text-sm font-semibold text-slate-900">{{ $kelas?->rombel?->kode ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-slate-500">Periode Akademik</dt>
                        <dd class="text-sm font-semibold text-slate-900">{{ $kelas?->rombel?->periodeAkademik?->kode ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Kartu 2: Waktu, Tempat, & Pertemuan -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Waktu dan Tempat</h2>
            </div>
            <div class="p-6">
                <dl class="divide-y divide-slate-100 mb-4">
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-slate-500">Hari</dt>
                        <dd class="text-sm font-semibold text-slate-900">{{ \App\Models\JadwalKuliah::HARI[$jadwal->hari] ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-slate-500">Jam Perkuliahan</dt>
                        <dd class="text-sm font-semibold text-slate-900">
                            {{ substr((string) $jadwal->jam_mulai, 0, 5) }} – {{ substr((string) $jadwal->jam_selesai, 0, 5) }} WIB
                        </dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-slate-500">Masa Berlaku</dt>
                        <dd class="text-sm font-semibold text-slate-900">
                            {{ $jadwal->berlaku_mulai?->format('d-m-Y') }} s.d. {{ $jadwal->berlaku_selesai?->format('d-m-Y') }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-slate-500">Metode Kuliah</dt>
                        <dd class="text-sm font-semibold text-slate-900">
                            {{ \App\Models\JadwalKuliah::METODE[$jadwal->metode] ?? ucfirst($jadwal->metode) }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <dt class="text-sm text-slate-500">Lokasi Ruangan</dt>
                        <dd class="text-sm font-semibold text-slate-900">{{ $jadwal->lokasi ?: '—' }}</dd>
                    </div>
                </dl>

                @if (in_array($jadwal->metode, ['daring', 'campuran'], true) && $jadwal->tautan_pertemuan)
                    <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
                        <div class="font-medium mb-1">Pertemuan Online Tersedia</div>
                        <a href="{{ $jadwal->tautan_pertemuan }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 font-bold text-sky-700 underline hover:text-sky-900">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            Buka tautan pertemuan
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Kartu 3: Tim Pengajar -->
        <div class="lg:col-span-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Tim Pengajar Mata Kuliah</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                    @forelse ($kelas?->pengajarAktif ?? [] as $pengajar)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="font-bold text-slate-800 text-sm mb-1">
                                {{ $pengajar->dosen?->user?->nama ?? 'Nama dosen tidak tersedia' }}
                            </div>
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                {{ \App\Models\PengajarKelas::PERAN[$pengajar->peran] ?? $pengajar->peran }}
                            </span>
                        </div>
                    @empty
                        <div class="col-span-full py-4 text-center text-sm text-slate-500">
                            Tim pengajar belum tersedia untuk kelas ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

