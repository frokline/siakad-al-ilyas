<dl class="detail-grid">
    <div>
        <dt>Kelas kuliah</dt>
        <dd><a href="{{ route('admin.kelas-kuliah.show', $kelas) }}">{{ $kelas->kode }}</a></dd>
    </div>
    <div>
        <dt>Mata kuliah</dt>
        <dd>{{ $kelas->nama_mk_snapshot }} — {{ str_replace('.', ',', $kelas->sks_snapshot) }} SKS</dd>
    </div>
    <div>
        <dt>Rombel</dt>
        <dd><a href="{{ route('admin.rombel.show', $kelas->rombel) }}">{{ $kelas->rombel->kode }}</a></dd>
    </div>
    <div>
        <dt>Periode</dt>
        <dd>
            {{ $kelas->rombel->periodeAkademik->kode }}
            — {{ \App\Models\PeriodeAkademik::STATUS[$kelas->rombel->periodeAkademik->status] }}
        </dd>
    </div>
    <div>
        <dt>Program studi</dt>
        <dd>{{ $kelas->rombel->paketSemester->kurikulum->programStudi->nama }}</dd>
    </div>
    <div>
        <dt>Paket semester</dt>
        <dd>{{ $kelas->rombel->paketSemester->nama }} — versi {{ $kelas->rombel->paketSemester->versi }}</dd>
    </div>
    <div>
        <dt>Status kelas</dt>
        <dd>{{ \App\Models\KelasKuliah::STATUS[$kelas->status] }}</dd>
    </div>
    <div>
        <dt>Semester studi</dt>
        <dd>{{ $kelas->rombel->paketSemester->semester_studi }}</dd>
    </div>
</dl>
