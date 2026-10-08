@php
    $peta = [
        'draf' => 'bg-slate-100 text-slate-700 ring-slate-300',
        'terbit' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'arsip' => 'bg-amber-50 text-amber-800 ring-amber-200',
    ];
@endphp
<span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $peta[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-300' }}">{{ \App\Models\Pengumuman::STATUS[$status] ?? $status }}</span>
