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
        <dd>{{ $kelas->rombel->periodeAkademik->kode }} —
            {{ \App\Models\PeriodeAkademik::STATUS[$kelas->rombel->periodeAkademik->status] }}</dd>
    </div>
    <div>
        <dt>Batas tanggal periode</dt>
        <dd>{{ $kelas->rombel->periodeAkademik->mulai->format('d-m-Y') }} s.d.
            {{ $kelas->rombel->periodeAkademik->selesai->format('d-m-Y') }}</dd>
    </div>
    <div>
        <dt>Status kelas</dt>
        <dd>{{ \App\Models\KelasKuliah::STATUS[$kelas->status] }}</dd>
    </div>
    <div>
        <dt>Program studi</dt>
        <dd>{{ $kelas->rombel->paketSemester->kurikulum->programStudi->nama }}</dd>
    </div>
    <div>
        <dt>Paket</dt>
        <dd>{{ $kelas->rombel->paketSemester->nama }} — semester {{ $kelas->rombel->paketSemester->semester_studi }}
        </dd>
    </div>
</dl>
<div class="jadwal-tim">
    <strong>Dosen yang mengikuti pola kelas</strong>
    <ul class="jadwal-chip-list">
        @forelse($kelas->pengajarKelas->where('aktif', true)->sortBy('dosen_id') as $anggota)
            <li>
                <a href="{{ route('admin.pengajar-kelas.show', $anggota) }}">
                    {{ $anggota->dosen->user->nama }}
                    @if ($anggota->isKoordinatorAktif())
                        <span>· Koordinator</span>
                    @endif
                </a>
            </li>
        @empty
            <li>Belum ada penugasan dosen aktif.</li>
        @endforelse
    </ul>
    <p class="help">Penambahan dosen nantinya juga diperiksa terhadap pola jadwal ini.</p>
</div>
