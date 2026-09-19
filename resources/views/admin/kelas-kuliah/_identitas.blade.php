<dl class="detail-grid">
    <div>
        <dt>Rombel</dt>
        <dd>
            <a href="{{ route('admin.rombel.show', $rombel) }}">{{ $rombel->kode }}</a>
        </dd>
    </div>
    <div>
        <dt>Periode akademik</dt>
        <dd>
            {{ $rombel->periodeAkademik->kode }}
            ({{ \App\Models\PeriodeAkademik::STATUS[$rombel->periodeAkademik->status] ?? $rombel->periodeAkademik->status }})
        </dd>
    </div>
    <div>
        <dt>Program studi</dt>
        <dd>{{ $rombel->paketSemester->kurikulum->programStudi->nama }}</dd>
    </div>
    <div>
        <dt>Kurikulum</dt>
        <dd>{{ $rombel->paketSemester->kurikulum->kode }} — {{ $rombel->paketSemester->kurikulum->nama }}</dd>
    </div>
    <div>
        <dt>Paket semester</dt>
        <dd>
            <a href="{{ route('admin.paket-semester.show', $rombel->paketSemester) }}">
                {{ $rombel->paketSemester->nama }}
            </a>
            — versi {{ $rombel->paketSemester->versi }}
        </dd>
    </div>
    <div>
        <dt>Semester studi</dt>
        <dd>{{ $rombel->paketSemester->semester_studi }}</dd>
    </div>
</dl>
