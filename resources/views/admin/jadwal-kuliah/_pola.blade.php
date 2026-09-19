<div class="table-wrap">
    <table class="jadwal-table">
        <caption class="jadwal-sr-only">Seluruh pola jadwal kelas {{ $kelas->kode }}</caption>
        <thead>
            <tr>
                <th scope="col">Hari / jam</th>
                <th scope="col">Berlaku</th>
                <th scope="col">Metode / lokasi</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kelas->jadwalKuliah->sortBy(fn ($row) => sprintf('%d-%s-%020d', $row->hari, $row->jam_mulai, $row->id)) as $pola)
                <tr @if (isset($jadwal) && $jadwal->exists && $jadwal->id === $pola->id) class="jadwal-selected" @endif>
                    <td>
                        <strong>{{ \App\Models\JadwalKuliah::HARI[$pola->hari] }}</strong>
                        <span
                            class="jadwal-sub">{{ substr($pola->jam_mulai, 0, 5) }}–{{ substr($pola->jam_selesai, 0, 5) }}</span>
                    </td>
                    <td>{{ $pola->berlaku_mulai->format('d-m-Y') }}<span class="jadwal-sub">s.d.
                            {{ $pola->berlaku_selesai->format('d-m-Y') }}</span></td>
                    <td>{{ \App\Models\JadwalKuliah::METODE[$pola->metode] }}<span
                            class="jadwal-sub">{{ $pola->lokasi ?? '—' }}</span></td>
                    <td><span
                            class="badge {{ $pola->aktif ? 'jadwal-badge-active' : 'jadwal-badge-muted' }}">{{ $pola->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td><a class="button secondary small" href="{{ route('admin.jadwal-kuliah.show', $pola) }}">Detail
                            #{{ $pola->id }}</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="jadwal-empty">Belum ada pola jadwal untuk kelas ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
