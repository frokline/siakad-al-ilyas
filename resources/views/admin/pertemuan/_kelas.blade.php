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
        <dt>Rombel / periode</dt>
        <dd>{{ $kelas->rombel->kode }} · {{ $kelas->rombel->periodeAkademik->kode }}</dd>
    </div>
    <div>
        <dt>Status</dt>
        <dd>{{ \App\Models\KelasKuliah::STATUS[$kelas->status] }} ·
            {{ \App\Models\PeriodeAkademik::STATUS[$kelas->rombel->periodeAkademik->status] }}</dd>
    </div>
    <div>
        <dt>Batas periode</dt>
        <dd>{{ $kelas->rombel->periodeAkademik->mulai->format('d-m-Y') }} s.d.
            {{ $kelas->rombel->periodeAkademik->selesai->format('d-m-Y') }}</dd>
    </div>
    <div>
        <dt>Jumlah sesi</dt>
        <dd>{{ $kelas->pertemuan->count() }} sesi tercatat</dd>
    </div>
</dl>
<div class="pertemuan-team">
    <strong>Penanggung jawab yang dapat dipilih</strong>
    <ul class="pertemuan-chip-list">
        @forelse($kelas->pengajarKelas->where('aktif', true)->sortBy('dosen_id') as $anggota)
            <li>
                <a href="{{ route('admin.pengajar-kelas.show', $anggota) }}">{{ $anggota->dosen->user->nama }}</a>
                <span>{{ $anggota->peran === \App\Models\PengajarKelas::KOORDINATOR ? 'Koordinator' : 'Pengajar' }}</span>
            </li>
        @empty
            <li>Belum ada dosen aktif. Tambahkan penugasan terlebih dahulu.</li>
        @endforelse
    </ul>
</div>
