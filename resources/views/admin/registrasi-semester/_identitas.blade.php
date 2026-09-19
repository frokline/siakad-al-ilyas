dl class="detail-grid">
<div>
    <dt>Mahasiswa</dt>
    <dd>{{ $riwayat->mahasiswa->user->nama }}</dd>
</div>
<div>
    <dt>NIM</dt>
    <dd>{{ $riwayat->mahasiswa->nim }}</dd>
</div>
<div>
    <dt>Program studi</dt>
    <dd>{{ $riwayat->kurikulum->programStudi->nama }}</dd>
</div>
<div>
    <dt>Kurikulum</dt>
    <dd>{{ $riwayat->kurikulum->kode }} — {{ $riwayat->kurikulum->nama }}</dd>
</div>
<div>
    <dt>Angkatan</dt>
    <dd>{{ $riwayat->angkatan }}</dd>
</div>
<div>
    <dt>Status riwayat studi</dt>
    <dd>{{ \App\Models\RiwayatStudi::STATUS[$riwayat->status] ?? $riwayat->status }}</dd>
</div>
</dl>
