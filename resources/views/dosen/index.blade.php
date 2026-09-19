@extends('layouts.siakad')

@section('title', 'Dosen')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Dosen</h1>
            <p class="subtitle">Kelola identitas dan status dosen.</p>
        </div>

        <a class="button" href="{{ route('admin.dosen.create') }}">
            Tambah Dosen
        </a>
    </div>

    <div class="card">
        <form class="filters filters-dosen" method="GET" action="{{ route('admin.dosen.index') }}">
            <div class="field">
                <label for="q">Pencarian</label>

                <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" maxlength="150"
                    placeholder="Kode dosen, NIDN, nama, atau akun">
            </div>

            <div class="field">
                <label for="filter_status">Status dosen</label>

                <select id="filter_status" name="status">
                    <option value="">Semua status</option>

                    @foreach ($statusOptions as $kodeStatus => $labelStatus)
                        <option value="{{ $kodeStatus }}" @selected(($filters['status'] ?? '') === $kodeStatus)>
                            {{ $labelStatus }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="status_akun">Status akun</label>

                <select id="status_akun" name="status_akun">
                    <option value="">Semua status</option>

                    @foreach ($statusAkunOptions as $kodeStatus => $labelStatus)
                        <option value="{{ $kodeStatus }}" @selected(($filters['status_akun'] ?? '') === $kodeStatus)>
                            {{ $labelStatus }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="actions">
                <button class="button" type="submit">Cari</button>

                <a class="button button-secondary" href="{{ route('admin.dosen.index') }}">
                    Reset
                </a>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Kode dosen</th>
                        <th scope="col">Nama dosen</th>
                        <th scope="col">NIDN</th>
                        <th scope="col">Status dosen</th>
                        <th scope="col">Status akun</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftarDosen as $item)
                        <tr>
                            <td>{{ $daftarDosen->firstItem() + $loop->index }}</td>
                            <td>{{ $item->kode_dosen }}</td>

                            <td>
                                {{ $item->user->nama }}

                                @if ($item->gelar !== null)
                                    <div class="help">{{ $item->gelar }}</div>
                                @endif
                            </td>

                            <td>{{ $item->nidn ?? '—' }}</td>

                            <td>
                                <span class="badge {{ $item->isAktif() ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $statusOptions[$item->status] }}
                                </span>
                            </td>

                            <td>
                                <span class="badge {{ $item->user->isAktif() ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $item->user->isAktif() ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a class="button button-small button-secondary"
                                        href="{{ route('admin.dosen.show', $item) }}"
                                        aria-label="Detail dosen {{ $item->kode_dosen }}">
                                        Detail
                                    </a>

                                    <a class="button button-small" href="{{ route('admin.dosen.edit', $item) }}"
                                        aria-label="Edit dosen {{ $item->kode_dosen }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="7">
                                Tidak ada dosen yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$daftarDosen" />
    </div>
@endsection
