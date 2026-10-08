@extends('layouts.admin')

@section('title', 'Riwayat Presensi Saya')

@section('content')
    <!-- Kepala Halaman -->
    <div class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
        <div>
            <nav aria-label="Breadcrumb" class="mb-1">
                <ol class="flex items-center space-x-2 text-xs text-slate-500">
                    <li><span class="font-medium">Portal Mahasiswa</span></li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">Riwayat Presensi</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Riwayat Presensi Saya</h1>
            <p class="text-sm text-slate-500">
                Daftar dan rekapitulasi kehadiran Anda dalam seluruh pertemuan perkuliahan.
            </p>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="mb-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm text-center">
            <span class="block text-2xl font-extrabold text-slate-800">{{ number_format($rekap['total'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</span>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-sm text-center">
            <span class="block text-2xl font-extrabold text-emerald-700">{{ number_format($rekap['hadir'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Hadir</span>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4 shadow-sm text-center">
            <span class="block text-2xl font-extrabold text-blue-700">{{ number_format($rekap['izin'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Izin</span>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 shadow-sm text-center">
            <span class="block text-2xl font-extrabold text-amber-700">{{ number_format($rekap['sakit'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Sakit</span>
        </div>
        <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 shadow-sm text-center">
            <span class="block text-2xl font-extrabold text-rose-700">{{ number_format($rekap['alpa'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Alpa</span>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm text-center">
            <span class="block text-2xl font-extrabold text-slate-600">{{ number_format($rekap['belum_dicatat'] ?? 0, 0, ',', '.') }}</span>
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Belum Dicatat</span>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('portal.presensi.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
            <!-- Filter Kelas -->
            <div class="sm:col-span-4 w-full">
                <label for="kelas" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Kelas / Mata Kuliah</label>
                <select id="kelas" name="kelas" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua Kelas</option>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id }}" @selected((string)($filter['kelas'] ?? '') === (string)$k->id)>
                            {{ $k->nama_mk_snapshot }} ({{ $k->kode }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div class="sm:col-span-3 w-full">
                <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Status Kehadiran</label>
                <select id="status" name="status" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
                    <option value="">Semua Status</option>
                    @foreach ($pilihanStatus as $key => $label)
                        <option value="{{ $key }}" @selected(($filter['status'] ?? '') === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tanggal Mulai -->
            <div class="sm:col-span-2 w-full">
                <label for="tanggal_mulai" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Mulai</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ $filter['tanggal_mulai'] ?? '' }}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
            </div>

            <!-- Filter Tanggal Selesai -->
            <div class="sm:col-span-2 w-full">
                <label for="tanggal_selesai" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Selesai</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ $filter['tanggal_selesai'] ?? '' }}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white">
            </div>

            <!-- Tombol Aksi -->
            <div class="sm:col-span-1 flex gap-2 w-full">
                <button type="submit" class="w-full rounded-lg bg-slate-800 px-3 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors shadow-sm">Terapkan</button>
            </div>
        </form>
    </div>

    <!-- Tabel Presensi -->
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Daftar Kehadiran</h2>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                {{ number_format($presensi->total(), 0, ',', '.') }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Kelas / Mata Kuliah</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Pertemuan & Topik</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Waktu Rencana</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status Kehadiran</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($presensi as $item)
                        @php
                            $pertemuan = $item->daftar?->pertemuan;
                            $kelasMk = $item->kelasKuliah;

                            $badgeClass = match ($item->status) {
                                'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'izin' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'sakit' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'alpa' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                            };

                            $dotClass = match ($item->status) {
                                'hadir' => 'bg-emerald-500',
                                'izin' => 'bg-blue-500',
                                'sakit' => 'bg-amber-500',
                                'alpa' => 'bg-rose-500',
                                default => 'bg-slate-400',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <strong class="font-bold text-slate-800 font-mono text-xs block">{{ $kelasMk?->kode ?? '—' }}</strong>
                                <span class="text-slate-700 text-sm mt-0.5 block">{{ $kelasMk?->nama_mk_snapshot ?? 'Mata Kuliah' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">
                                    Pertemuan {{ $pertemuan?->nomor ?? '—' }}
                                </div>
                                <span class="text-xs text-slate-500 mt-0.5 block truncate max-w-xs">{{ $pertemuan?->topik ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-700">
                                <div class="font-medium">
                                    {{ $pertemuan?->mulai_rencana ? $pertemuan->mulai_rencana->format('d-m-Y H:i') : '—' }}
                                </div>
                                <span class="text-slate-400 mt-0.5 block">
                                    s.d. {{ $pertemuan?->selesai_rencana ? $pertemuan->selesai_rencana->format('H:i') : '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold uppercase tracking-wider {{ $badgeClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                    {{ $pilihanStatus[$item->status] ?? ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('portal.presensi.show', $item->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-siakad-dark transition-colors shadow-sm">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <p class="text-sm font-bold text-slate-700">Belum ada riwayat presensi yang ditemukan.</p>
                                <p class="mt-1 text-xs">Coba sesuaikan filter pencarian kelas, status, atau rentang tanggal Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('presensi._pagination', ['paginator' => $presensi])
    </div>
@endsection
