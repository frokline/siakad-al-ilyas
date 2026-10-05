<dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 text-sm mb-8">
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
                class="text-slate-500 font-normal ml-1 border-l border-slate-300 pl-1">{{ str_replace('.', ',', $kelas->sks_snapshot) }}
                SKS</span></dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rombel / Periode</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            {{ $kelas->rombel->kode }} <span class="text-slate-400 font-normal mx-0.5">&middot;</span>
            {{ $kelas->rombel->periodeAkademik->kode }}
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Kelas / Periode</dt>
        <dd class="mt-1 font-semibold text-slate-800 flex items-center gap-2">
            <span
                class="inline-flex items-center rounded-full bg-slate-100 border border-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ \App\Models\KelasKuliah::STATUS[$kelas->status] }}</span>
            <span
                class="inline-flex items-center rounded-full bg-slate-100 border border-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ \App\Models\PeriodeAkademik::STATUS[$kelas->rombel->periodeAkademik->status] }}</span>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Batas Tanggal Periode</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            {{ $kelas->rombel->periodeAkademik->mulai->format('d-m-Y') }} <span
                class="text-slate-400 font-normal mx-0.5">s.d.</span>
            {{ $kelas->rombel->periodeAkademik->selesai->format('d-m-Y') }}
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Sesi Tercatat</dt>
        <dd class="mt-1 font-semibold text-slate-800">{{ $kelas->pertemuan->count() }} sesi</dd>
    </div>
</dl>

<div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
    <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        Dosen / Penanggung Jawab Kelas
    </h3>
    <div class="flex flex-wrap gap-2">
        @forelse($kelas->pengajarKelas->where('aktif', true)->sortBy('dosen_id') as $anggota)
            <a href="{{ route('admin.pengajar-kelas.show', $anggota) }}"
                class="inline-flex items-center gap-1.5 rounded-full bg-white border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-siakad-dark hover:text-siakad-dark transition-colors shadow-sm">
                {{ $anggota->dosen->user->nama }}
                <span
                    class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 ml-1 uppercase tracking-wider border border-slate-200">
                    {{ $anggota->peran === \App\Models\PengajarKelas::KOORDINATOR ? 'Koor' : 'Pengajar' }}
                </span>
            </a>
        @empty
            <span
                class="inline-flex items-center rounded-full bg-rose-50 border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-700">Belum
                ada dosen aktif ditugaskan.</span>
        @endforelse
    </div>
</div>
