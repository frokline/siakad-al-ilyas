@extends('layouts.admin')
@section('title', 'Riwayat Presensi')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Riwayat Pencatatan Presensi</h1>
            <p class="text-sm text-slate-500 mt-1">
                <span class="font-bold">{{ $baris->peserta_snapshot['nama'] ?? 'Nama tidak tersedia' }}</span> &middot;
                <span class="font-mono">{{ $baris->peserta_snapshot['nim'] ?? 'NIM tidak tersedia' }}</span> &middot;
                Pertemuan {{ $sesi->nomor }}
            </p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('presensi.show', $sesi) }}">
            &larr; Kembali ke Pertemuan
        </a>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Audit Log Pencatatan & Koreksi</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Revisi / Waktu</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Pelaku / Tindakan</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Sebelum</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Sesudah</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Alasan Koreksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($audit as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <strong class="font-bold text-slate-800 block">V{{ $log->versi_entitas }}</strong>
                                <span
                                    class="text-xs text-slate-500 font-mono mt-0.5 block">{{ $log->waktu->setTimezone($zona)->format('d-m-Y H:i:s') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $log->pelaku?->nama ?? 'Sistem' }}</div>
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 uppercase tracking-wider mt-1 border border-slate-200">{{ ucfirst($log->aksi) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <strong
                                    class="text-xs font-bold uppercase tracking-wider block {{ isset($log->sebelum['status']) ? 'text-slate-700' : 'text-slate-400' }}">{{ \App\Models\Presensi::STATUS[$log->sebelum['status'] ?? ''] ?? '—' }}</strong>
                                <span
                                    class="text-xs text-slate-500 mt-0.5 block whitespace-pre-line">{{ $log->sebelum['catatan'] ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <strong
                                    class="text-xs font-bold uppercase tracking-wider block text-emerald-700">{{ \App\Models\Presensi::STATUS[$log->sesudah['status'] ?? ''] ?? '—' }}</strong>
                                <span
                                    class="text-xs text-slate-600 font-medium mt-0.5 block whitespace-pre-line">{{ $log->sesudah['catatan'] ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600 whitespace-pre-line">
                                {{ $log->alasan ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">Belum ada riwayat audit
                                pencatatan untuk peserta ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('presensi._pagination', ['paginator' => $audit])
    </div>
@endsection
