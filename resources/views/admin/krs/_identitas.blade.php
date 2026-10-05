<dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mahasiswa</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            <a href="{{ route('admin.mahasiswa.show', $registrasi->riwayatStudi->mahasiswa) }}"
                class="text-siakad-dark hover:underline font-mono">{{ $registrasi->riwayatStudi->mahasiswa->nim }}</a>
            <div class="text-slate-600 font-normal mt-0.5">{{ $registrasi->riwayatStudi->mahasiswa->user->nama }}</div>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Program Studi</dt>
        <dd class="mt-1 font-semibold text-slate-800">{{ $registrasi->riwayatStudi->kurikulum->programStudi->nama }}</dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Periode Akademik</dt>
        <dd class="mt-1 font-semibold text-slate-800">{{ $registrasi->periodeAkademik->kode }}</dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Semester Studi</dt>
        <dd class="mt-1 font-semibold text-slate-800">Semester {{ $registrasi->semester_studi }}</dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rombel</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            <a href="{{ route('admin.rombel.show', $registrasi->rombel) }}"
                class="text-siakad-dark hover:underline">{{ $registrasi->rombel->kode }}</a>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Paket Semester</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            <a href="{{ route('admin.paket-semester.show', $registrasi->rombel->paketSemester) }}"
                class="text-siakad-dark hover:underline">{{ $registrasi->rombel->paketSemester->nama }}</a>
            <span class="text-xs text-slate-500 font-normal ml-1 border-l border-slate-300 pl-1">Versi
                {{ $registrasi->rombel->paketSemester->versi }}</span>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Registrasi Semester</dt>
        <dd class="mt-1 font-semibold text-slate-800 flex items-center gap-2">
            <a href="{{ route('admin.registrasi-semester.show', $registrasi) }}"
                class="text-siakad-dark hover:underline font-mono">#{{ $registrasi->id }}</a>
            <span
                class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 border border-slate-200">
                {{ \App\Models\RegistrasiSemester::STATUS[$registrasi->status] }}
            </span>
        </dd>
    </div>
    <div class="border-b border-slate-100 pb-4">
        <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jadwal Pengisian KRS</dt>
        <dd class="mt-1 font-semibold text-slate-800">
            @if ($registrasi->periodeAkademik->krs_mulai && $registrasi->periodeAkademik->krs_selesai)
                <div class="text-sm">
                    {{ $registrasi->periodeAkademik->krs_mulai->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i') }}
                    <span class="text-slate-400 font-normal mx-0.5">s.d.</span>
                    {{ $registrasi->periodeAkademik->krs_selesai->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i') }}
                </div>
                <div class="text-[10px] text-slate-500 mt-0.5 font-mono">
                    {{ config('siakad.timezone', 'Asia/Makassar') }}</div>
            @else
                <span class="text-rose-600 text-xs font-medium">Belum ditetapkan</span>
            @endif
        </dd>
    </div>
</dl>
