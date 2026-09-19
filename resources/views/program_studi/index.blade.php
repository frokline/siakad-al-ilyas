@extends('layouts.siakad')

@section('title', 'Program Studi')

@section('breadcrumb')
    <span>Program Studi</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Program Studi</h1>
            <p class="subtitle">Kelola data program studi dan status penggunaannya.</p>
        </div>

        <a href="{{ route('admin.program-studi.create') }}" class="button">
            Tambah Program Studi
        </a>
    </div>

    <form method="GET" action="{{ route('admin.program-studi.index') }}" class="card filters">
        <div class="field">
            <label for="q">Cari program studi</label>

            <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                placeholder="Kode, nama, atau jenjang" maxlength="150">
        </div>

        <div class="field">
            <label for="filter-aktif">Status</label>

            <select id="filter-aktif" name="aktif">
                <option value="">Semua status</option>

                <option value="1" @selected((string) ($filters['aktif'] ?? '') === '1')>
                    Aktif
                </option>

                <option value="0" @selected((string) ($filters['aktif'] ?? '') === '0')>
                    Nonaktif
                </option>
            </select>
        </div>

        <div class="actions">
            <button type="submit" class="button">Cari</button>

            <a href="{{ route('admin.program-studi.index') }}" class="button button-secondary">
                Reset
            </a>
        </div>
    </form>

    <section class="card">
        <div class="card-header">
            <strong>
                {{ number_format($daftarProgramStudi->total(), 0, ',', '.') }}
                program studi
            </strong>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama program studi</th>
                        <th scope="col">Jenjang / program</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftarProgramStudi as $prodi)
                        <tr>
                            <td>
                                {{ $daftarProgramStudi->firstItem() + $loop->index }}
                            </td>
                            <td>{{ $prodi->kode }}</td>
                            <td>{{ $prodi->nama }}</td>
                            <td>{{ $prodi->jenjang }}</td>
                            <td>
                                <span class="badge {{ $prodi->aktif ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $prodi->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.program-studi.show', $prodi) }}"
                                        class="button button-secondary button-small"
                                        aria-label="Detail {{ $prodi->nama }}">
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.program-studi.edit', $prodi) }}" class="button button-small"
                                        aria-label="Edit {{ $prodi->nama }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                Tidak ada program studi yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-pagination :paginator="$daftarProgramStudi" />
@endsection
