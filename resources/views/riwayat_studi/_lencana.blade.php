{{-- Lencana status riwayat studi. Pakai: @include('riwayat_studi._lencana', ['status' => $riwayat->status]) --}}
@php
    $kelasLencana = match ($status) {
        \App\Models\RiwayatStudi::AKTIF => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        \App\Models\RiwayatStudi::SELESAI => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        \App\Models\RiwayatStudi::PINDAH => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        \App\Models\RiwayatStudi::KELUAR => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/20',
    };
@endphp
<span
    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $kelasLencana }}">
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ \App\Models\RiwayatStudi::STATUS[$status] ?? ucfirst((string) $status) }}
</span>
