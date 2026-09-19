@extends('layouts.siakad')

@section('title', 'Mata Kuliah')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Mata Kuliah</h1>
            <p class="subtitle">Kelola katalog mata kuliah setiap program studi.</p>
        </div>

        <a class="button" href="{{ route('admin.mata-kuliah.create') }}">
            Tambah Mata Kuliah
        </a>
    </div>

    <div class="card">
        <form class="filters filters-mata-kuliah" method="GET" action="{{ route('admin.mata-kuliah.index') }}">
            <div class="field">
                <label for="q">Pencarian</label>

                <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" maxlength="150"
                    placeholder="Kode atau nama mata kuliah">
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
                <label for="filter_aktif">Status</label>

                <select id="filter_aktif" name="aktif">
                    <option value="">Semua status</option>

                    @foreach ($statusOptions as $kodeStatus => $labelStatus)
                        <option value="{{ $kodeStatus }}" @selected((string) ($filters['aktif'] ?? '') === (string) $kodeStatus)>
                            {{ $labelStatus }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="actions">
                <button class="button" type="submit">Cari</button>

                <a class="button button-secondary" href="{{ route('admin.mata-kuliah.index') }}">
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
                        <th scope="col">Nama mata kuliah</th>
                        <th scope="col">Program studi</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftarMataKuliah as $item)
                        <tr>
                            <td>
                                {{ $daftarMataKuliah->firstItem() + $loop->index }}
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

                            <td>
                                <span class="badge {{ $item->aktif ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a class="button button-small button-secondary"
                                        href="{{ route('admin.mata-kuliah.show', $item) }}"
                                        aria-label="Detail mata kuliah {{ $item->kode }}">
                                        Detail
                                    </a>

                                    <a class="button button-small" href="{{ route('admin.mata-kuliah.edit', $item) }}"
                                        aria-label="Edit mata kuliah {{ $item->kode }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="6">
                                Tidak ada mata kuliah yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$daftarMataKuliah" />
    </div>
@endsection
