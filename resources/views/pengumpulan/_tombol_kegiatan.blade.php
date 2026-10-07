@canany(['ruangSaya', 'rekap'], [\App\Models\Pengumpulan::class, $kegiatan])
    <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800">Pengumpulan jawaban</h2>

        @can('ruangSaya', [\App\Models\Pengumpulan::class, $kegiatan])
            <a href="{{ route('pengumpulan.saya', $kegiatan) }}"
                class="block rounded-lg bg-siakad-dark px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">Jawaban
                saya</a>
        @endcan

        @can('rekap', [\App\Models\Pengumpulan::class, $kegiatan])
            <a href="{{ route('pengumpulan.rekap', $kegiatan) }}"
                class="block rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Rekap
                jawaban mahasiswa</a>
        @endcan
    </section>
@endcanany
