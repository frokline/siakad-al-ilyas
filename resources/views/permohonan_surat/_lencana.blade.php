@php
    // Parameter: $status (kode status), $gelap (opsional, untuk latar hijau tua).
    $warnaStatus = match ((string) $status) {
        'diajukan' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'diproses' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'terbit' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'ditolak' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/20',
    };
    if ($gelap ?? false) {
        $warnaStatus = 'bg-white/15 text-white ring-white/30';
    }
@endphp
<span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $warnaStatus }}">{{ \App\Models\PermohonanSurat::STATUS[$status] ?? $status }}</span>
