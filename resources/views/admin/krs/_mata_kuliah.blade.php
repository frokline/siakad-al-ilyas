@php($untukCetak = $untukCetak ?? false)
<div class="table-wrap">
    <table class="krs-table">
        <caption>Mata kuliah dalam paket semester</caption>
        <thead>
            <tr>
                <th scope="col">No.</th>
                <th scope="col">Kode kelas</th>
                <th scope="col">Mata kuliah</th>
                <th scope="col" class="krs-angka">SKS</th>
                @unless ($untukCetak)
                    <th scope="col">Status kelas</th>
                    <th scope="col">Keikutsertaan</th>
                @endunless
            </tr>
        </thead>
        <tbody>
            @forelse ($krs->details->sortBy('id') as $detail)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @if ($untukCetak)
                            {{ $detail->kelasKuliah->kode }}
                        @else
                            <a href="{{ route('admin.kelas-kuliah.show', $detail->kelasKuliah) }}">
                                {{ $detail->kelasKuliah->kode }}
                            </a>
                        @endif
                    </td>
                    <td>{{ $detail->kelasKuliah->nama_mk_snapshot }}</td>
                    <td class="krs-angka">{{ str_replace('.', ',', $detail->kelasKuliah->sks_snapshot) }}</td>
                    @unless ($untukCetak)
                        <td>{{ \App\Models\KelasKuliah::STATUS[$detail->kelasKuliah->status] }}</td>
                        <td>
                            <span class="badge" data-krs-detail="{{ $detail->status }}">
                                {{ \App\Models\DetailKrs::STATUS[$detail->status] }}
                            </span>
                        </td>
                    @endunless
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $untukCetak ? 4 : 6 }}">Detail mata kuliah belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th scope="row" colspan="3">Total {{ $krs->details->count() }} mata kuliah</th>
                <td class="krs-angka"><strong>{{ str_replace('.', ',', $krs->totalSks()) }}</strong></td>
                @unless ($untukCetak)
                    <td colspan="2">Seluruh paket semester</td>
                @endunless
            </tr>
        </tfoot>
    </table>
</div>
