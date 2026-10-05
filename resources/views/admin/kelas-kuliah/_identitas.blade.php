<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
    <div class="border-b border-slate-100 pb-4">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Rombel</span>
        <span class="mt-1 font-semibold text-slate-800 block">
            <a href="{{ route('admin.rombel.show', $rombel) }}"
                class="text-siakad-dark hover:underline">{{ $rombel->kode }}</a>
        </span>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Periode Akademik</span>
        <span class="mt-1 font-semibold text-slate-800 block">
            {{ $rombel->periodeAkademik->kode }}
            <span
                class="text-xs font-normal text-slate-500">({{ \App\Models\PeriodeAkademik::STATUS[$rombel->periodeAkademik->status] ?? $rombel->periodeAkademik->status }})</span>
        </span>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Program Studi</span>
        <span
            class="mt-1 font-semibold text-slate-800 block">{{ $rombel->paketSemester->kurikulum->programStudi->nama }}</span>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kurikulum</span>
        <span class="mt-1 font-semibold text-slate-800 block">{{ $rombel->paketSemester->kurikulum->kode }} —
            {{ $rombel->paketSemester->kurikulum->nama }}</span>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Paket Semester</span>
        <span class="mt-1 font-semibold text-slate-800 block">
            <a href="{{ route('admin.paket-semester.show', $rombel->paketSemester) }}"
                class="text-siakad-dark hover:underline">
                {{ $rombel->paketSemester->nama }}
            </a>
            — versi {{ $rombel->paketSemester->versi }}
        </span>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Semester Studi</span>
        <span class="mt-1 font-semibold text-slate-800 block">{{ $rombel->paketSemester->semester_studi }}</span>
    </div>
</div>
