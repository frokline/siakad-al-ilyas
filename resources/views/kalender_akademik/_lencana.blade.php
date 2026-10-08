@php
    $peta =
        $tipe === 'status'
            ? [
                'draf' => 'bg-slate-100 text-slate-700 ring-slate-300',
                'terbit' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'batal' => 'bg-rose-50 text-rose-700 ring-rose-200',
            ]
            : [
                'krs' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'kuliah' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'ujian' => 'bg-rose-50 text-rose-700 ring-rose-200',
                'libur' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'lainnya' => 'bg-slate-100 text-slate-700 ring-slate-300',
            ];
    $daftarLabel = $tipe === 'status' ? \App\Models\KalenderAkademik::STATUS : \App\Models\KalenderAkademik::JENIS;
@endphp
<span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $peta[$nilai] ?? 'bg-slate-100 text-slate-700 ring-slate-300' }}">{{ $daftarLabel[$nilai] ?? $nilai }}</span>
