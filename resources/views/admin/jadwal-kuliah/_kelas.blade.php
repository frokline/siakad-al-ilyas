<dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm mb-8">
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kelas Kuliah</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            <a href="{{ route('admin.kelas-kuliah.show', $kelas) }}"
                class="text-siakad-dark hover:underline font-mono font-bold">{{ $kelas->kode }}</a>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mata Kuliah</dt>
        <dd class="mt-1 font-semibold text-slate-800">{{ $kelas->nama_mk_snapshot }} <span
                class="text-slate-500 font-normal ml-1">— {{ str_replace('.', ',', $kelas->sks_snapshot) }} SKS</span>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rombel</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            <a href="{{ route('admin.rombel.show', $kelas->rombel) }}"
                class="text-siakad-dark hover:underline">{{ $kelas->rombel->kode }}</a>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Kelas</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            <span
                class="inline-flex items-center rounded border px-2.5 py-0.5 text-xs font-bold bg-emerald-50 text-emerald-700 border-emerald-200">
                {{ \App\Models\KelasKuliah::STATUS[$kelas->status] }}
            </span>
        </dd>
    </div>

    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Periode Akademik</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            {{ $kelas->rombel->periodeAkademik->kode }} <span class="text-slate-500 font-normal ml-1">—
                {{ \App\Models\PeriodeAkademik::STATUS[$kelas->rombel->periodeAkademik->status] }}</span>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Batas Tanggal Periode</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            {{ $kelas->rombel->periodeAkademik->mulai->format('d-m-Y') }} <span class="text-slate-400 mx-1">s.d.</span>
            {{ $kelas->rombel->periodeAkademik->selesai->format('d-m-Y') }}
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Program Studi</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            {{ $kelas->rombel->paketSemester->kurikulum->programStudi->nama }}</dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Paket</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            {{ $kelas->rombel->paketSemester->nama }} <span class="text-slate-500 font-normal ml-1">— Semester
                {{ $kelas->rombel->paketSemester->semester_studi }}</span>
        </dd>
    </div>
</dl>

<div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
    <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        Dosen yang Mengikuti Pola Kelas
    </h3>
    <div class="flex flex-wrap gap-2 mb-3">
        @forelse($kelas->pengajarKelas->where('aktif', true)->sortBy('dosen_id') as $anggota)
            <a href="{{ route('admin.pengajar-kelas.show', $anggota) }}"
                class="inline-flex items-center gap-1.5 rounded-full bg-white border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-siakad-dark hover:text-siakad-dark transition-colors shadow-sm">
                {{ $anggota->dosen->user->nama }}
                @if ($anggota->isKoordinatorAktif())
                    <span
                        class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-700 ml-1 uppercase tracking-wider">Koor</span>
                @endif
            </a>
        @empty
            <span
                class="inline-flex items-center rounded-full bg-rose-50 border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-700">Belum
                ada penugasan dosen aktif.</span>
        @endforelse
    </div>
    <p class="text-xs text-slate-500 italic">Penambahan dosen baru nantinya juga akan otomatis diperiksa terhadap semua
        pola jadwal ini.</p>
</div>
