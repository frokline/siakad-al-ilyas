<section class="card">
    <h2>{{ $kegiatan->judul }}</h2>
    <p>{{ $kegiatan->kelasKuliah->kode }} · {{ $kegiatan->kelasKuliah->nama_mk_snapshot }} ·
        {{ $kegiatan->labelJadwal() }}</p>
    <dl class="metadata">
        <div>
            <dt>Mulai</dt>
            <dd>{{ $kegiatan->buka_at->setTimezone($zona)->format('d-m-Y H:i') }}</dd>
        </div>
        <div>
            <dt>Tenggat saat ini</dt>
            <dd>{{ $kegiatan->tenggat_at->setTimezone($zona)->format('d-m-Y H:i') }} ({{ $zona }})</dd>
        </div>
        <div>
            <dt>Format berkas</dt>
            <dd>{{ strtoupper(implode(', ', $kegiatan->ekstensi_diizinkan)) }}</dd>
        </div>
        <div>
            <dt>Batas berkas</dt>
            <dd>{{ $kegiatan->maks_berkas }} berkas · {{ (int) ($kegiatan->maks_ukuran_byte / 1048576) }} MB per berkas
            </dd>
        </div>
    </dl>
    @can('view', $kegiatan)
        <a href="{{ route('kegiatan.show', $kegiatan) }}">Baca instruksi kegiatan</a>
    @endcan
</section>
