@extends('layouts.admin')
@section('title', 'Detail Pertemuan')

@section('content')
    @php
        $zona = config('siakad.timezone', 'Asia/Makassar');
        $tautan = $sesi->tautan_pertemuan;
        $pengajar = is_array($sesi->pengajar_snapshot) ? $sesi->pengajar_snapshot : [];
        $sumber = is_array($sesi->jadwal_snapshot) ? $sesi->jadwal_snapshot : [];
    @endphp

    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Pertemuan {{ $sesi->nomor }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                <span class="font-mono font-bold">{{ $kelas->kode }}</span> &middot; {{ $kelas->nama_mk_snapshot }} &middot;
                Revisi {{ $sesi->revisi }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if ($sesi->dapatDiubah())
                <a class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800"
                    href="{{ route('admin.pertemuan.edit', $sesi) }}">Edit Rencana</a>
            @endif
            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                href="{{ route('admin.pertemuan.create', ['kelas_id' => $kelas->id]) }}">Buat sesi lain</a>
            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                href="{{ route('admin.pertemuan.index', ['kelas_id' => $kelas->id]) }}">Daftar kelas ini</a>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-6 flex flex-col sm:flex-row items-center gap-6">
            <div
                class="flex flex-col items-center justify-center bg-slate-800 text-white rounded-2xl p-5 min-w-[150px] shadow-sm">
                <span
                    class="text-[10px] font-bold uppercase tracking-widest text-emerald-400 mb-1 border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 rounded-full">Sesi
                    {{ $sesi->nomor }}</span>
                <span
                    class="text-2xl font-black font-mono tracking-tight mt-1">{{ $sesi->mulai_rencana->setTimezone($zona)->format('H:i') }}</span>
                <span
                    class="text-2xl font-black font-mono tracking-tight leading-none text-slate-400 opacity-50 my-0.5">|</span>
                <span
                    class="text-2xl font-black font-mono tracking-tight">{{ $sesi->selesai_rencana->setTimezone($zona)->format('H:i') }}</span>
                <span
                    class="text-xs text-slate-300 font-medium mt-2">{{ $sesi->mulai_rencana->setTimezone($zona)->format('d M Y') }}</span>
            </div>

            <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                <div class="lg:col-span-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Topik
                            Pembelajaran</span>
                        <h2 class="text-lg font-bold text-slate-800 mt-0.5">{{ $sesi->topik }}</h2>
                    </div>
                    @php
                        $statusBadgeClass = match ($sesi->status) {
                            'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'berlangsung' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'terjadwal' => 'bg-amber-50 text-amber-700 border-amber-200',
                            default => 'bg-rose-50 text-rose-700 border-rose-200',
                        };
                    @endphp
                    <span
                        class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-widest {{ $statusBadgeClass }}">
                        {{ \App\Models\Pertemuan::STATUS[$sesi->status] ?? $sesi->status }}
                    </span>
                </div>

                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Penanggung Jawab</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $pengajar['nama'] ?? '—' }} <div
                            class="text-xs text-slate-500 font-mono mt-0.5">{{ $pengajar['kode_dosen'] ?? '—' }}</div>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis & Peran</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        {{ \App\Models\Pertemuan::JENIS[$sesi->jenis] ?? $sesi->jenis }} <span
                            class="text-slate-400 mx-1">&middot;</span> {{ $pengajar['peran'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pola Sumber</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        {{ $sumber['id'] ?? 'Tambahan' }}
                        @if (isset($sumber['hari']))
                            <span class="text-slate-400 mx-1">&middot;</span>
                            {{ \App\Models\JadwalKuliah::HARI[(int) $sumber['hari']] ?? '' }}
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Metode & Lokasi</dt>
                    <dd class="mt-1 font-semibold text-slate-800">
                        {{ \App\Models\JadwalKuliah::METODE[$sesi->metode] ?? $sesi->metode }} <div
                            class="text-xs text-slate-500 mt-0.5">{{ $sesi->lokasi ?? 'Daring' }}</div>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Waktu Mulai Aktual</dt>
                    <dd class="mt-1 font-mono text-xs font-semibold text-slate-700">
                        {{ $sesi->mulai_aktual?->setTimezone($zona)->format('d-m-Y H:i:s') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Waktu Selesai Aktual</dt>
                    <dd class="mt-1 font-mono text-xs font-semibold text-slate-700">
                        {{ $sesi->selesai_aktual?->setTimezone($zona)->format('d-m-Y H:i:s') ?? '—' }}</dd>
                </div>
            </div>
        </div>

        <div class="p-6 border-b border-slate-200 space-y-6">
            @if ($sesi->rencana)
                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-2 flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Rencana Pembelajaran
                    </h3>
                    <div
                        class="bg-slate-50 border border-slate-100 p-4 rounded-lg text-sm text-slate-700 whitespace-pre-line">
                        {{ $sesi->rencana }}</div>
                </div>
            @endif
            @if ($sesi->realisasi)
                <div>
                    <h3 class="text-sm font-bold text-blue-800 mb-2 flex items-center gap-2">
                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Realisasi Pembelajaran
                    </h3>
                    <div
                        class="bg-blue-50 border border-blue-100 p-4 rounded-lg text-sm text-blue-900 whitespace-pre-line font-medium">
                        {{ $sesi->realisasi }}</div>
                </div>
            @endif
        </div>

        <div class="p-6 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-800 mb-3">Tautan Pertemuan Daring</h3>
            @if ($tautan !== null && \App\Rules\TautanPertemuanAman::sesuai($tautan))
                <a href="{{ $tautan }}" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer"
                    class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Buka Tautan Disematkan
                </a>
            @elseif($tautan !== null)
                <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
                    Tautan tersimpan tidak memenuhi format izin standar keamanan. Silakan perbarui.
                </div>
            @else
                <p class="text-xs text-slate-500 italic">Belum ada tautan pertemuan disematkan untuk sesi ini.</p>
            @endif
        </div>
        <div class="bg-slate-50/50 p-4 text-xs text-slate-500 text-center">
            Setiap sesi menyimpan <em>snapshot</em> sumber jadwal dan info pengajar saat dibuat. Perubahan pola jadwal atau
            tim pada kelas utamanya di kemudian hari tidak akan merubah rekam jejak ini secara retrospektif.
        </div>
    </div>

    <div class="rounded-xl bg-slate-50 shadow-sm border border-slate-300 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-300 bg-slate-100/80 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Tindakan Eksekusi (Action)</h2>
        </div>
        <div class="p-6">
            @include('admin.pertemuan._aksi')
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Identitas Kelas</h2>
        </div>
        <div class="p-6">@include('admin.pertemuan._kelas')</div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Seluruh Sesi Kelas Ini</h2>
        </div>
        @include('admin.pertemuan._daftar-sesi')
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Riwayat Perubahan & Audit Log</h2>
        </div>
        <div class="p-6">@include('admin.pertemuan._audit')</div>
    </div>
@endsection
