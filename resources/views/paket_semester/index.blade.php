@extends('layouts.siakad')

@section('title', 'Paket Semester')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Paket Semester</h1>
            <p class="subtitle">Susunan mata kuliah untuk KRS per paket.</p>
        </div>

        <a class="button" href="{{ route('admin.paket-semester.create') }}">
            Tambah Paket
        </a>
    </div>

    <section class="card">
        <div class="panel-body">
            <form class="filters filters-paket-semester" method="GET" action="{{ route('admin.paket-semester.index') }}">
                <div class="field">
                    <label for="q">Pencarian</label>

                    <input id="q" name="q" type="search" maxlength="150" value="{{ $filters['q'] ?? '' }}"
                        placeholder="Nama paket / kode kurikulum">
                </div>

                <div class="field">
                    <label for="kurikulum_id">Kurikulum</label>

                    <select id="kurikulum_id" name="kurikulum_id">
                        <option value="">Semua kurikulum</option>

                        @foreach ($daftarKurikulum as $kurikulum)
                            <option value="{{ $kurikulum->id }}" @selected((string) ($filters['kurikulum_id'] ?? '') === (string) $kurikulum->id)>
                                {{ $kurikulum->programStudi->nama }}
                                — {{ $kurikulum->kode }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="semester_studi">Semester studi</label>

                    <input id="semester_studi" name="semester_studi" type="number" min="1" max="32767"
                        step="1" value="{{ $filters['semester_studi'] ?? '' }}" placeholder="Semua">
                </div>

                <div class="field">
                    <label for="status">Status</label>

                    <select id="status" name="status">
                        <option value="">Semua status</option>

                        @foreach ($statusOptions as $kode => $label)
                            <option value="{{ $kode }}" @selected(($filters['status'] ?? '') === $kode)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="actions">
                    <button class="button" type="submit">Cari</button>

                    <a class="button secondary" href="{{ route('admin.paket-semester.index') }}">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Paket</th>
                        <th scope="col">Program / Kurikulum</th>
                        <th scope="col">Semester</th>
                        <th scope="col">Versi</th>
                        <th scope="col">Mata Kuliah</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftarPaket as $paket)
                        <tr>
                            <td>{{ $daftarPaket->firstItem() + $loop->index }}</td>
                            <td>{{ $paket->nama }}</td>

                            <td>
                                <div>{{ $paket->kurikulum->programStudi->nama }}</div>
                                <div class="help">{{ $paket->kurikulum->kode }}</div>
                            </td>

                            <td>{{ $paket->semester_studi }}</td>
                            <td>{{ $paket->versi }}</td>
                            <td>{{ $paket->details_count }}</td>

                            <td>
                                <span class="badge" data-paket-status="{{ $paket->status }}">
                                    {{ $statusOptions[$paket->status] }}
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a class="button secondary small"
                                        href="{{ route('admin.paket-semester.show', $paket) }}">
                                        Detail
                                    </a>

                                    @if ($paket->isDraf())
                                        <a class="button small" href="{{ route('admin.paket-semester.edit', $paket) }}">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Tidak ada paket yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-body">
            <x-pagination :paginator="$daftarPaket" />
        </div>
    </section>
@endsection
