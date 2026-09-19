@extends('layouts.siakad')

@section('title', 'Kurikulum')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Kurikulum</h1>
            <p class="subtitle">Kelola versi kurikulum setiap program studi.</p>
        </div>

        <a class="button" href="{{ route('admin.kurikulum.create') }}">
            Tambah Kurikulum
        </a>
    </div>

    <div class="card">
        <form class="filters filters-kurikulum" method="GET" action="{{ route('admin.kurikulum.index') }}">
            <div class="field">
                <label for="q">Pencarian</label>

                <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" maxlength="150"
                    placeholder="Kode atau nama kurikulum">
            </div>

            <div class="field">
                <label for="filter_prodi">Program studi</label>

                <select id="filter_prodi" name="program_studi_id">
                    <option value="">Semua program studi</option>

                    @foreach ($daftarProdi as $prodi)
                        <option value="{{ $prodi->id }}" @selected((string) ($filters['program_studi_id'] ?? '') === (string) $prodi->id)>
                            {{ $prodi->kode }} — {{ $prodi->nama }}
                            {{ $prodi->aktif ? '' : '(Nonaktif)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="filter_status">Status</label>

                <select id="filter_status" name="status">
                    <option value="">Semua status</option>

                    @foreach ($statusOptions as $kodeStatus => $labelStatus)
                        <option value="{{ $kodeStatus }}" @selected(($filters['status'] ?? '') === $kodeStatus)>
                            {{ $labelStatus }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="actions">
                <button class="button" type="submit">Cari</button>

                <a class="button button-secondary" href="{{ route('admin.kurikulum.index') }}">
                    Reset
                </a>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama kurikulum</th>
                        <th scope="col">Program studi</th>
                        <th scope="col">Tahun berlaku</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftarKurikulum as $item)
                        <tr>
                            <td>
                                {{ $daftarKurikulum->firstItem() + $loop->index }}
                            </td>

                            <td>{{ $item->kode }}</td>
                            <td>{{ $item->nama }}</td>

                            <td>
                                {{ $item->programStudi->nama }}

                                @if (!$item->programStudi->aktif)
                                    <span class="badge badge-inactive">
                                        Prodi nonaktif
                                    </span>
                                @endif
                            </td>

                            <td>{{ $item->tahun_berlaku }}</td>

                            <td>
                                <span class="badge" data-kurikulum-status="{{ $item->status }}">
                                    {{ $statusOptions[$item->status] }}
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a class="button button-small button-secondary"
                                        href="{{ route('admin.kurikulum.show', $item) }}"
                                        aria-label="Detail kurikulum {{ $item->kode }}">
                                        Detail
                                    </a>

                                    <a class="button button-small" href="{{ route('admin.kurikulum.edit', $item) }}"
                                        aria-label="Edit kurikulum {{ $item->kode }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="7">
                                Tidak ada kurikulum yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$daftarKurikulum" />
    </div>
@endsection
