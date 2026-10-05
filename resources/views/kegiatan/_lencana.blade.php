@php
    $warnaJenis = [
        'materi' => 'bg-sky-50 text-sky-700 border-sky-200',
        'tugas' => 'bg-amber-50 text-amber-700 border-amber-200',
        'latihan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'uts' => 'bg-rose-50 text-rose-700 border-rose-200',
        'uas' => 'bg-violet-50 text-violet-700 border-violet-200',
    ];
    $jadwal = $item->labelJadwal();
    $warnaJadwal = match ($jadwal) {
        'Dapat dikumpulkan' => 'text-emerald-700',
        'Tersedia' => 'text-sky-700',
        'Tenggat telah lewat' => 'text-rose-700',
        default => 'text-slate-500',
    };
@endphp
<div class="flex flex-wrap items-center gap-2">
    <span
        class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider {{ $warnaJenis[$item->jenis] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
        {{ \App\Models\Kegiatan::JENIS[$item->jenis] ?? $item->jenis }}
    </span>
    <span class="text-xs font-semibold {{ $warnaJadwal }}">{{ $jadwal }}</span>
</div>
