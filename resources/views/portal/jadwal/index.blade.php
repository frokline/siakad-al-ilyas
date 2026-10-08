@extends('layouts.admin')

@section('title', 'Jadwal Kuliah Saya')

@section('content')
    <!-- Kepala Halaman -->
    <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <nav aria-label="Breadcrumb" class="mb-1">
                <ol class="flex items-center space-x-2 text-xs text-slate-500">
                    <li><span class="font-medium">Portal</span></li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">Jadwal Kuliah Saya</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Jadwal Kuliah Saya</h1>
            <p class="text-sm text-slate-500">
                Daftar jadwal dari kelas yang tercantum pada KRS yang telah disahkan.
            </p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="get" action="{{ route('portal.jadwal.index') }}"
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-12 items-end">
            
            <!-- Filter Periode Akademik -->
            <div class="md:col-span-4">
                <label for="periode_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                    Periode Akademik
                </label>
                <select id="periode_id" name="periode_id"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 transition-colors focus:border-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/20">
                    <option value="">Semua Periode</option>
                    @foreach ($daftarPeriode as $periode)
                        <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>
                            {{ $periode->kode }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Hari -->
            <div class="md:col-span-3">
                <label for="hari" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                    Hari
                </label>
                <select id="hari" name="hari"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 transition-colors focus:border-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/20">
                    <option value="">Semua Hari</option>
                    @foreach (\App\Models\JadwalKuliah::HARI as $nilai => $label)
                        <option value="{{ $nilai }}" @selected((string) ($filter['hari'] ?? '') === (string) $nilai)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Metode -->
            <div class="md:col-span-3">
                <label for="metode" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-600">
                    Metode Perkuliahan
                </label>
                <select id="metode" name="metode"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-800 transition-colors focus:border-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/20">
                    <option value="">Semua Metode</option>
                    @foreach (\App\Models\JadwalKuliah::METODE as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(($filter['metode'] ?? '') === $nilai)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Aksi Filter -->
            <div class="flex gap-2 md:col-span-2">
                <button type="submit"
                    class="flex-1 rounded-xl bg-siakad-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark/50">
                    Terapkan
                </button>
                <a href="{{ route('portal.jadwal.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200"
                    title="Reset Filter">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Jadwal Kuliah -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Daftar Jadwal Kuliah Aktif</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Waktu & Hari
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Kelas & Rombel
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Mata Kuliah
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Dosen Pengajar
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            Metode & Lokasi
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($daftarJadwal as $jadwal)
                        @php
                            $kelas = $jadwal->kelasKuliah;
                            $dosenList = $kelas?->pengajarAktif
                                ? $kelas->pengajarAktif->map(fn($p) => $p->dosen?->user?->nama)->filter()->join(', ')
                                : null;
                            
                            $metodeBadgeClass = match ($jadwal->metode) {
                                'luring' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                'daring' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                                'campuran' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                default => 'bg-slate-50 text-slate-700 ring-slate-600/20',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Waktu & Hari -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-semibold text-slate-900 text-sm">
                                    {{ \App\Models\JadwalKuliah::HARI[$jadwal->hari] ?? '—' }}
                                </div>
                                <div class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700 mt-1">
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ substr((string) $jadwal->jam_mulai, 0, 5) }} – {{ substr((string) $jadwal->jam_selesai, 0, 5) }}
                                </div>
                            </td>

                            <!-- Kelas & Rombel -->
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-semibold text-slate-800 text-sm">
                                    {{ $kelas?->kode ?? '—' }}
                                </div>
                                @if ($kelas?->rombel)
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        Rombel: {{ $kelas->rombel->kode }}
                                    </div>
                                @endif
                            </td>

                            <!-- Mata Kuliah -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 text-sm">
                                    {{ $kelas?->nama_mk_snapshot ?? '—' }}
                                </div>
                                @if ($kelas?->sks_snapshot)
                                    <div class="text-xs text-slate-500 font-medium mt-0.5">
                                        {{ $kelas->sks_snapshot }} SKS
                                    </div>
                                @endif
                            </td>

                            <!-- Dosen Pengajar -->
                            <td class="px-6 py-4 text-sm text-slate-700">
                                {{ $dosenList ?: 'Belum ditentukan' }}
                            </td>

                            <!-- Metode & Lokasi -->
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div>
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $metodeBadgeClass }}">
                                        {{ \App\Models\JadwalKuliah::METODE[$jadwal->metode] ?? ucfirst($jadwal->metode) }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mt-1.5 flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $jadwal->lokasi ?: '—' }}
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('portal.jadwal.show', $jadwal) }}"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 hover:text-siakad-dark">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">
                                <svg class="mx-auto h-10 w-10 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Belum ada jadwal aktif yang dapat ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

