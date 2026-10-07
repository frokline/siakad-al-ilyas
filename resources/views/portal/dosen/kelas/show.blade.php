@extends('layouts.portal')

@section('title', 'Detail Kelas Dosen')

@section('content')
    <!-- HEADER HALAMAN -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
        <div>
            <p class="text-sm font-bold text-siakad-active mb-1 uppercase tracking-wider">Portal Dosen &raquo; Detail Kelas
            </p>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                <span class="font-mono bg-slate-200 text-slate-700 px-2 py-1 rounded-md text-xl">{{ $kelas->kode }}</span>
                {{ $kelas->nama_mk_snapshot }}
            </h1>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('portal.dosen.kelas.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                &larr; Kembali
            </a>

            @if ($bolehMengelola)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false"
                        class="inline-flex items-center justify-center rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 transition-colors shadow-sm gap-2">
                        Kelola Pembelajaran
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-cloak
                        class="absolute right-0 mt-2 w-48 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-20">
                        <div class="py-1">
                            <a href="{{ route('kegiatan.index', ['kelas' => $kelas->id]) }}"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Kelola Materi</a>
                            <a href="{{ route('kegiatan.index', ['kelas' => $kelas->id]) }}"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Kelola Tugas & Ujian</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- GRID INFORMASI & RINGKASAN -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <!-- CARD 1: INFORMASI KELAS -->
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 p-5 flex flex-col h-full">
            <h2 class="text-sm font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Informasi Kelas</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <dt class="text-slate-500">Mata Kuliah</dt>
                <dd class="font-semibold text-slate-800 text-right">{{ $kelas->nama_mk_snapshot }}</dd>

                <dt class="text-slate-500">Bobot SKS</dt>
                <dd class="font-semibold text-slate-800 text-right">{{ $kelas->sks_snapshot }} SKS</dd>

                <dt class="text-slate-500">Rombel / Periode</dt>
                <dd class="font-semibold text-slate-800 text-right">
                    {{ $kelas->rombel?->kode ?? '—' }} <br>
                    <span
                        class="text-xs font-normal text-slate-500">{{ $kelas->rombel?->periodeAkademik?->kode ?? '—' }}</span>
                </dd>

                <dt class="text-slate-500 mt-2">Status Kelas</dt>
                <dd class="text-right mt-2">
                    <span
                        class="inline-flex items-center rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[10px] font-bold uppercase text-emerald-700 tracking-wider">
                        {{ $labelStatus }}
                    </span>
                </dd>
            </dl>
        </div>

        <!-- CARD 2: RINGKASAN AKTIVITAS -->
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 p-5 flex flex-col h-full">
            <h2
                class="text-sm font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 flex justify-between items-center">
                Ringkasan Modul
                <a href="{{ route('portal.dosen.peserta.index', $kelas->id) }}"
                    class="text-[10px] uppercase font-bold text-siakad-active hover:underline">Lihat Peserta &rarr;</a>
            </h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-center">
                    <span
                        class="block text-2xl font-black text-siakad-dark mb-1">{{ number_format($kelas->jumlah_peserta, 0, ',', '.') }}</span>
                    <span class="block text-xs font-semibold text-slate-500 uppercase">Peserta</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-center">
                    <span
                        class="block text-2xl font-black text-siakad-dark mb-1">{{ number_format($kelas->jumlah_jadwal_aktif, 0, ',', '.') }}</span>
                    <span class="block text-xs font-semibold text-slate-500 uppercase">Jadwal</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-center">
                    <span
                        class="block text-2xl font-black text-siakad-dark mb-1">{{ number_format($kelas->jumlah_pertemuan, 0, ',', '.') }}</span>
                    <span class="block text-xs font-semibold text-slate-500 uppercase">Pertemuan</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-center">
                    <span
                        class="block text-2xl font-black text-siakad-dark mb-1">{{ number_format($kelas->jumlah_kegiatan, 0, ',', '.') }}</span>
                    <span class="block text-xs font-semibold text-slate-500 uppercase">Kegiatan</span>
                </div>
            </div>
        </div>

        <!-- CARD 3: STATISTIK PRESENSI -->
        <div
            class="rounded-xl bg-white shadow-sm border border-slate-200 p-5 flex flex-col h-full relative overflow-hidden">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-50/50"></div>
            <h2
                class="text-sm font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2 flex justify-between items-center relative z-10">
                Statistik Kehadiran
                <a href="{{ route('portal.dosen.peserta.index', $kelas->id) }}"
                    class="text-[10px] uppercase font-bold text-siakad-active hover:underline">Rekap &rarr;</a>
            </h2>

            <div class="flex items-center justify-between mb-4 relative z-10">
                <div>
                    <span
                        class="block text-3xl font-black text-emerald-600">{{ number_format($ringkasanPresensi['persentase_hadir'] ?? 0, 1, ',', '.') }}%</span>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Hadir</span>
                </div>
                <div class="text-right text-xs">
                    <p class="text-slate-500">Total Baris: <strong
                            class="text-slate-700">{{ number_format($ringkasanPresensi['jumlah_baris'] ?? 0, 0, ',', '.') }}</strong>
                    </p>
                    <p class="text-slate-500">Daftar Terbuka: <strong
                            class="text-slate-700">{{ number_format($ringkasanPresensi['daftar']['terbuka'] ?? 0, 0, ',', '.') }}</strong>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-2 mt-auto relative z-10">
                <div class="text-center p-2 rounded bg-emerald-50 border border-emerald-100" title="Hadir">
                    <span
                        class="block font-bold text-emerald-700">{{ number_format($ringkasanPresensi['status']['hadir'] ?? 0, 0, ',', '.') }}</span>
                    <span class="block text-[9px] uppercase font-bold text-emerald-600 mt-1">H</span>
                </div>
                <div class="text-center p-2 rounded bg-blue-50 border border-blue-100" title="Izin">
                    <span
                        class="block font-bold text-blue-700">{{ number_format($ringkasanPresensi['status']['izin'] ?? 0, 0, ',', '.') }}</span>
                    <span class="block text-[9px] uppercase font-bold text-blue-600 mt-1">I</span>
                </div>
                <div class="text-center p-2 rounded bg-amber-50 border border-amber-100" title="Sakit">
                    <span
                        class="block font-bold text-amber-700">{{ number_format($ringkasanPresensi['status']['sakit'] ?? 0, 0, ',', '.') }}</span>
                    <span class="block text-[9px] uppercase font-bold text-amber-600 mt-1">S</span>
                </div>
                <div class="text-center p-2 rounded bg-rose-50 border border-rose-100" title="Alpa">
                    <span
                        class="block font-bold text-rose-700">{{ number_format($ringkasanPresensi['status']['alpa'] ?? 0, 0, ',', '.') }}</span>
                    <span class="block text-[9px] uppercase font-bold text-rose-600 mt-1">A</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL TIM PENGAJAR & JADWAL (2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <!-- TIM PENGAJAR -->
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-50/50 px-5 py-3">
                <h2 class="text-sm font-bold text-slate-800">Tim Pengajar Aktif</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Nama & Kode</th>
                            <th class="px-5 py-3 font-semibold text-right">Peran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($kelas->pengajarKelas as $penugasan)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3">
                                    <strong
                                        class="text-slate-800 block">{{ $penugasan->dosen?->user?->nama ?? '—' }}</strong>
                                    <span
                                        class="text-xs text-slate-500 font-mono">{{ $penugasan->dosen?->kode_dosen ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span
                                        class="inline-block rounded bg-slate-100 border border-slate-200 px-2 py-1 text-[10px] font-bold uppercase text-slate-600">
                                        {{ \App\Models\PengajarKelas::PERAN[$penugasan->peran] ?? $penugasan->peran }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-5 py-8 text-center text-xs text-slate-500">Tidak ada pengajar
                                    aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- JADWAL KULIAH -->
        <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-50/50 px-5 py-3">
                <h2 class="text-sm font-bold text-slate-800">Jadwal Kuliah Aktif</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 border-collapse">
                    <thead class="bg-slate-50 text-[10px] uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Hari & Waktu</th>
                            <th class="px-5 py-3 font-semibold">Metode / Lokasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($kelas->jadwalKuliah as $jadwal)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3">
                                    <strong
                                        class="text-slate-800 block uppercase text-xs">{{ \App\Models\JadwalKuliah::HARI[$jadwal->hari] ?? $jadwal->hari }}</strong>
                                    <span
                                        class="text-xs text-slate-500 font-mono">{{ substr((string) $jadwal->jam_mulai, 0, 5) }}
                                        – {{ substr((string) $jadwal->jam_selesai, 0, 5) }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span
                                            class="inline-block rounded bg-indigo-50 border border-indigo-100 px-1.5 py-0.5 text-[9px] font-bold uppercase text-indigo-600">
                                            {{ \App\Models\JadwalKuliah::METODE[$jadwal->metode] ?? ucfirst($jadwal->metode) }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-slate-600">
                                        {{ $jadwal->lokasi ?: ($jadwal->tautan_pertemuan ? 'Tautan Tersedia' : '—') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-5 py-8 text-center text-xs text-slate-500">Belum ada jadwal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TABEL PERTEMUAN -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Pertemuan Kelas</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse min-w-[700px]">
                <thead class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase text-slate-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold w-16 text-center">Sesi</th>
                        <th class="px-4 py-4 font-semibold">Topik & Waktu Rencana</th>
                        <th class="px-4 py-4 font-semibold text-center w-32">Status</th>
                        <th class="px-6 py-4 font-semibold text-right w-32">Presensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($kelas->pertemuan as $pertemuan)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-700">
                                    {{ $pertemuan->nomor }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <strong class="text-slate-800 block">{{ $pertemuan->topik ?: 'Tanpa Topik' }}</strong>
                                <span class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $pertemuan->mulai_rencana ? \Illuminate\Support\Carbon::parse($pertemuan->mulai_rencana)->format('d M Y, H:i') : 'Belum dijadwalkan' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @php
                                    $statusLabel =
                                        \App\Models\Pertemuan::STATUS[$pertemuan->status] ?? $pertemuan->status;
                                    $statusClass =
                                        strtolower($statusLabel) === 'selesai'
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                            : 'bg-slate-100 text-slate-600 border-slate-200';
                                @endphp
                                <span
                                    class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if ($bolehMengelola)
                                    <a href="{{ route('presensi.show', $pertemuan->id) }}"
                                        class="inline-flex items-center gap-1 text-sm font-semibold text-siakad-dark hover:text-emerald-500">
                                        Buka &rarr;
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm font-bold text-slate-500">Belum ada
                                data pertemuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
