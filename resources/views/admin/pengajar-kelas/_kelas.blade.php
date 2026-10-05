<dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kelas Kuliah</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            <a href="{{ route('admin.kelas-kuliah.show', $kelas) }}"
                class="text-siakad-dark hover:underline font-mono font-bold">{{ $kelas->kode }}</a>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mata Kuliah</dt>
        <dd class="mt-1 font-semibold text-slate-800">{{ $kelas->nama_mk_snapshot }} — <span
                class="text-slate-600 font-normal">{{ str_replace('.', ',', $kelas->sks_snapshot) }} SKS</span></dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rombel</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            <a href="{{ route('admin.rombel.show', $kelas->rombel) }}"
                class="text-siakad-dark hover:underline">{{ $kelas->rombel->kode }}</a>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Periode Akademik</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            {{ $kelas->rombel->periodeAkademik->kode }}
            <span
                class="text-xs font-normal text-slate-500">({{ \App\Models\PeriodeAkademik::STATUS[$kelas->rombel->periodeAkademik->status] }})</span>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4 sm:col-span-2">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Program Studi</dt>
        <dd class="mt-1 font-semibold text-slate-800">{{ $kelas->rombel->paketSemester->kurikulum->programStudi->nama }}
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4 sm:col-span-2">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Paket Semester</dt>
        <dd class="mt-1 font-semibold text-slate-800">{{ $kelas->rombel->paketSemester->nama }} — <span
                class="font-mono font-bold text-xs text-slate-600">Versi
                {{ $kelas->rombel->paketSemester->versi }}</span></dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Kelas</dt>
        <dd class="mt-1">
            <span
                class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 border-emerald-100">
                {{ \App\Models\KelasKuliah::STATUS[$kelas->status] }}
            </span>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Semester Studi</dt>
        <dd class="mt-1 font-semibold text-slate-800">{{ $kelas->rombel->paketSemester->semester_studi }}</dd>
    </div>
</dl>
