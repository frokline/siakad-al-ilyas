@extends('layouts.siakad')

@section('title', 'Riwayat Studi')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Riwayat Studi</h1>
            <p class="subtitle">Program, kurikulum, dan perjalanan studi mahasiswa.</p>
        </div>

        <a class="button" href="{{ route('admin.riwayat-studi.create') }}">
            Tambah Riwayat
        </a>
    </div>

    <section class="card">
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.riwayat-studi.index') }}" class="filters filters-riwayat-studi">
                <div class="field">
                    <label for="q">Cari mahasiswa</label>

                    <input id="q" name="q" type="search" maxlength="150" placeholder="NIM atau nama"
                        value="{{ $filters['q'] ?? '' }}">
                </div>

                <div class="field">
                    <label for="program_studi_id">Program studi</label>

                    <select id="program_studi_id" name="program_studi_id">
                        <option value="">Semua prodi</option>

                        @foreach ($daftarProdi as $prodi)
                            <option value="{{ $prodi->id }}" @selected((string) ($filters['program_studi_id'] ?? '') === (string) $prodi->id)>
                                {{ $prodi->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="angkatan">Angkatan</label>

                    <input id="angkatan" name="angkatan" type="number" min="1900" max="9999" step="1"
                        placeholder="Semua" value="{{ $filters['angkatan'] ?? '' }}">
                </div>

                <div class="field">
                    <label for="status">Status studi</label>

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

                    <a class="button secondary" href="{{ route('admin.riwayat-studi.index') }}">
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
                        <th scope="col">Mahasiswa</th>
                        <th scope="col">Program / Kurikulum</th>
                        <th scope="col">Angkatan</th>
                        <th scope="col">Mulai</th>
                        <th scope="col">Akhir</th>
                        <th scope="col">Status Studi</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftarRiwayat as $riwayat)
                        <tr>
                            <td>{{ $daftarRiwayat->firstItem() + $loop->index }}</td>

                            <td>
                                <strong>{{ $riwayat->mahasiswa->nim }}</strong>
                                <div>{{ $riwayat->mahasiswa->user->nama }}</div>
                            </td>

                            <td>
                                <div>{{ $riwayat->kurikulum->programStudi->nama }}</div>
                                <div class="help">{{ $riwayat->kurikulum->kode }}</div>
                            </td>

                            <td>{{ $riwayat->angkatan }}</td>
                            <td>{{ $riwayat->periodeMulai->kode }}</td>
                            <td>{{ $riwayat->periodeAkhir?->kode ?? '—' }}</td>

                            <td>
                                <span @class([
                                    'badge',
                                    'badge-active' => $riwayat->isAktif(),
                                    'badge-inactive' => !$riwayat->isAktif(),
                                ])>
                                    {{ $statusOptions[$riwayat->status] }}
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a class="button secondary small"
                                        href="{{ route('admin.riwayat-studi.show', $riwayat) }}">
                                        Detail
                                    </a>

                                    @if ($riwayat->isAktif())
                                        <a class="button small" href="{{ route('admin.riwayat-studi.edit', $riwayat) }}">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Tidak ada riwayat studi yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-body">
            <x-pagination :paginator="$daftarRiwayat" />
        </div>
    </section>
@endsection
