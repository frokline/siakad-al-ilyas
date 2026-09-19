<div class="table-wrap">
    <table class="pertemuan-table">
        <caption class="pertemuan-sr-only">Sesi kuliah kelas {{ $kelas->kode }}</caption>
        <thead>
            <tr>
                <th scope="col">No.</th>
                <th scope="col">Rencana</th>
                <th scope="col">Jenis / topik</th>
                <th scope="col">Penanggung jawab</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kelas->pertemuan->sortBy('nomor') as $row)
                <tr @if (isset($sesi) && $sesi->exists && $sesi->id === $row->id) class="pertemuan-selected" @endif>
                    <td><strong>{{ $row->nomor }}</strong></td>
                    <td>
                        <strong>{{ $row->mulai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y') }}</strong>
                        <span
                            class="pertemuan-sub">{{ $row->mulai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('H:i') }}–{{ $row->selesai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('H:i') }}</span>
                    </td>
                    <td>{{ \App\Models\Pertemuan::JENIS[$row->jenis] ?? $row->jenis }}<span
                            class="pertemuan-sub">{{ $row->topik }}</span></td>
                    <td>{{ $row->pengajar_snapshot['nama'] ?? '—' }}<span
                            class="pertemuan-sub">{{ $row->pengajar_snapshot['kode_dosen'] ?? '—' }}</span></td>
                    <td><span
                            class="badge pertemuan-status-{{ $row->status }}">{{ \App\Models\Pertemuan::STATUS[$row->status] ?? $row->status }}</span>
                    </td>
                    <td><a class="button secondary small" href="{{ route('admin.pertemuan.show', $row) }}">Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="pertemuan-empty">Belum ada pertemuan untuk kelas ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
