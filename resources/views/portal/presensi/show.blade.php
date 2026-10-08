@extends('layouts.admin')

@section('title', 'Detail Presensi Pertemuan ' . ($presensi->daftar?->pertemuan?->nomor ?? '—'))

@section('content')
    @php
        $pertemuan = $presensi->daftar?->pertemuan;
        $kelasKuliah = $presensi->kelasKuliah;

        $badgeClass = match ($presensi->status) {
            'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'izin' => 'bg-blue-50 text-blue-700 border-blue-200',
            'sakit' => 'bg-amber-50 text-amber-700 border-amber-200',
            'alpa' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    @endphp

    <!-- Kepala Halaman -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav aria-label="Breadcrumb" class="mb-1">
                <ol class="flex items-center space-x-2 text-xs text-slate-500">
                    <li><a href="{{ route('portal.presensi.index') }}" class="hover:text-siakad-dark font-medium transition-colors">Riwayat Presensi</a></li>
                    <li><span class="mx-1">&middot;</span></li>
                    <li class="font-semibold text-siakad-dark" aria-current="page">Detail Kehadiran</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Pertemuan {{ $pertemuan?->nomor ?? '—' }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                <span class="font-mono font-semibold">{{ $kelasKuliah?->kode ?? '—' }}</span> &middot; {{ $kelasKuliah?->nama_mk_snapshot ?? 'Mata Kuliah' }}
            </p>
        </div>
        <div>
            <a href="{{ route('portal.presensi.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                &larr; Kembali ke Riwayat
            </a>
        </div>
    </div>

    <!-- Container Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Kartu Status Kehadiran Mahasiswa -->
        <div class="lg:col-span-1 rounded-xl bg-white border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Status Kehadiran Anda</h2>
                <div class="text-center py-6 border-b border-slate-100">
                    <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-base font-extrabold uppercase tracking-wider {{ $badgeClass }}">
                        {{ $labelStatus }}
                    </span>
                </div>
                <div class="mt-6 space-y-4 text-sm">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Waktu Dicatat</span>
                        <span class="font-mono font-medium text-slate-700">
                            {{ $presensi->waktu_dicatat ? $presensi->waktu_dicatat->format('d-m-Y H:i:s') : 'Belum dicatat' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Metode</span>
                        <span class="font-medium text-slate-700 uppercase">
                            {{ $presensi->metode ?? 'Otomatis / Manual Dosen' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Catatan Keterangan</span>
                        <p class="text-slate-600 bg-slate-50 rounded-lg p-3 border border-slate-100 text-xs italic">
                            {{ $presensi->catatan ?: 'Tidak ada catatan khusus.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Informasi Pertemuan Perkuliahan -->
        <div class="lg:col-span-2 rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Informasi Pertemuan Perkuliahan</h2>
            
            <div class="mb-6">
                <h3 class="text-lg font-bold text-slate-800 mb-1">{{ $pertemuan?->topik ?? 'Materi / Topik tidak ditentukan' }}</h3>
                <p class="text-xs text-slate-500">Status Pertemuan: 
                    <span class="font-semibold text-slate-700 uppercase">{{ $pertemuan?->status ?? 'Terjadwal' }}</span>
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm pt-4 border-t border-slate-100">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Rencana Waktu Mulai</span>
                    <span class="font-mono font-medium text-slate-700">
                        {{ $pertemuan?->mulai_rencana ? $pertemuan->mulai_rencana->format('d-m-Y H:i') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Rencana Waktu Selesai</span>
                    <span class="font-mono font-medium text-slate-700">
                        {{ $pertemuan?->selesai_rencana ? $pertemuan->selesai_rencana->format('d-m-Y H:i') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Pelaksanaan Mulai (Aktual)</span>
                    <span class="font-mono font-medium text-slate-700">
                        {{ $pertemuan?->mulai_aktual ? $pertemuan->mulai_aktual->format('d-m-Y H:i:s') : 'Belum dimulai' }}
                    </span>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Pelaksanaan Selesai (Aktual)</span>
                    <span class="font-mono font-medium text-slate-700">
                        {{ $pertemuan?->selesai_aktual ? $pertemuan->selesai_aktual->format('d-m-Y H:i:s') : 'Belum selesai' }}
                    </span>
                </div>
            </div>

            @if ($kelasKuliah)
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div>
                        <span>SKS: <strong>{{ $kelasKuliah->sks_snapshot ?? '—' }}</strong></span>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
