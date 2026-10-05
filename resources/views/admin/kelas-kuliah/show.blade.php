@extends('layouts.admin')

@section('title', 'Detail Kelas Kuliah')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">{{ $kelas->kode }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $kelas->nama_mk_snapshot }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if ($kelas->dapatDiubah())
                <a class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800"
                    href="{{ route('admin.kelas-kuliah.edit', $kelas) }}">
                    Edit Kelas
                </a>
            @endif
            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                href="{{ route('admin.kelas-kuliah.index', ['rombel_id' => $rombel->id]) }}">
                Kelas Rombel Ini
            </a>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-6">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Rombel dan Periode</h2>
        </div>
        <div class="p-6">
            @include('admin.kelas-kuliah._identitas')
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-6">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Data Kelas</h2>
            @php
                $s = $kelas->status;
                $badgeClass = match ($s) {
                    'aktif' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                    'selesai' => 'bg-blue-50 text-blue-700 border-blue-100',
                    'arsip' => 'bg-slate-100 text-slate-600 border-slate-200',
                    default => 'bg-amber-50 text-amber-700 border-amber-100',
                };
                $dotClass = match ($s) {
                    'aktif' => 'bg-emerald-500',
                    'selesai' => 'bg-blue-500',
                    'arsip' => 'bg-slate-400',
                    default => 'bg-amber-500',
                };
            @endphp
            <span
                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $badgeClass }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                {{ \App\Models\KelasKuliah::STATUS[$kelas->status] }}
            </span>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm mb-6">
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode Kelas</dt>
                    <dd class="mt-1 font-mono font-bold text-slate-800 text-base">{{ $kelas->kode }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mata Kuliah</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        <a href="{{ route('admin.mata-kuliah.show', $kelas->detailPaket->kurikulumMataKuliah->mataKuliah) }}"
                            class="text-siakad-dark hover:underline">
                            {{ $kelas->detailPaket->kurikulumMataKuliah->mataKuliah->kode }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama pada Saat Penawaran</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $kelas->nama_mk_snapshot }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">SKS Penawaran</dt>
                    <dd class="mt-1 font-semibold text-slate-700">{{ str_replace('.', ',', $kelas->sks_snapshot) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode Dapat Dikoreksi</dt>
                    <dd class="mt-1 text-slate-700">
                        {{ $kelas->kodeDapatDiubah() ? 'Sebelum aktivasi pertama, selama periode terbuka.' : 'Terkunci sejak aktivasi pertama.' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Revisi Data</dt>
                    <dd class="mt-1 font-mono font-semibold text-slate-700">{{ $kelas->revisi }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dibuat</dt>
                    <dd class="mt-1 text-slate-700">
                        {{ $kelas->created_at->copy()->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i') }}
                    </dd>
                </div>
                @foreach ([
            'diaktifkan_at' => 'Diaktifkan',
            'diselesaikan_at' => 'Diselesaikan',
            'diarsipkan_at' => 'Diarsipkan',
        ] as $kolom => $label)
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $label }}</dt>
                        <dd class="mt-1 text-slate-700">
                            {{ $kelas->{$kolom}?->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i') ?? 'Belum' }}
                        </dd>
                    </div>
                @endforeach
            </dl>

            <p class="text-xs text-slate-500 mb-4">
                Nama dan SKS penawaran tetap tersimpan untuk riwayat akademik meskipun master mata kuliah berubah.
            </p>

            @unless ($kelas->dapatDiubah())
                <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800">
                    Data hanya dapat dibaca karena periode telah diarsipkan atau kelas telah masuk arsip tetap.
                </div>
            @endunless

            @if (in_array($rombel->periodeAkademik->status, \App\Models\Rombel::STATUS_PERIODE_TERBUKA, true))
                <div class="pt-5 border-t border-slate-200 flex justify-end">
                    <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                        href="{{ route('admin.kelas-kuliah.create', ['rombel_id' => $rombel->id]) }}">
                        Siapkan mata kuliah lain dalam rombel ini
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
