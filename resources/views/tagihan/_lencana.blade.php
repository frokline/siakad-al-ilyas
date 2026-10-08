@php
    // Warna ditentukan dari konstanta model, bukan dari teks, agar aman jika nilai status berubah.
    $kelas = match ($status) {
        \App\Models\Tagihan::TERBIT => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        \App\Models\Tagihan::DIBATALKAN => 'bg-rose-50 text-rose-700 ring-rose-200',
        default => 'bg-slate-100 text-slate-700 ring-slate-300',
    };
@endphp
<span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $kelas }}">{{ \App\Models\Tagihan::STATUS[$status] ?? $status }}</span>
