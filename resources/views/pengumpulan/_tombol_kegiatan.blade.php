@can('ruangSaya', [\App\Models\Pengumpulan::class, $kegiatan])
    <p><a class="button" href="{{ route('pengumpulan.saya', $kegiatan) }}">Jawaban saya</a></p>
@endcan
@can('rekap', [\App\Models\Pengumpulan::class, $kegiatan])
    <p><a class="button secondary" href="{{ route('pengumpulan.rekap', $kegiatan) }}">Rekap jawaban mahasiswa</a></p>
@endcan
