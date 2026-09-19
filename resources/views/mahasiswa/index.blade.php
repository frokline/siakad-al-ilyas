@extends('layouts.siakad')

@section('title', 'Mahasiswa')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Mahasiswa</h1>
            <p class="subtitle">Kelola identitas dan biodata mahasiswa.</p>
        </div>

        <a class="button" href="{{ route('admin.mahasiswa.create') }}">
            Tambah mahasiswa
        </a>
    </div>

    <section class="card">
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.mahasiswa.index') }}" class="mhs-filters">
                <div class="field">
                    <label for="q">Cari mahasiswa</label>

                    <input type="search" id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="NIM, nama, atau email">
                </div>

                <div class="field">
                    <label for="status_akun">Status akun</label>

                    <select id="status_akun" name="status_akun">
                        <option value="">Semua status</option>
                        <option value="aktif" @selected(($filter['status_akun'] ?? '') === 'aktif')>
                            Aktif
                        </option>
                        <option value="nonaktif" @selected(($filter['status_akun'] ?? '') === 'nonaktif')>
                            Nonaktif
                        </option>
                    </select>
                </div>

                <div class="actions">
                    <button class="button" type="submit">Cari</button>

                    <a class="button secondary" href="{{ route('admin.mahasiswa.index') }}">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </section>

    <section class="card mhs-section">
        <div class="card-header">
            <h2>Daftar mahasiswa</h2>
            <span>{{ number_format($daftar->total(), 0, ',', '.') }} data</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">NIM</th>
                        <th scope="col">Mahasiswa</th>
                        <th scope="col">Program studi aktif</th>
                        <th scope="col">Status akun</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftar as $mahasiswa)
                        <tr>
                            <td>{{ $mahasiswa->nim }}</td>

                            <td>
                                <strong>{{ $mahasiswa->user?->nama ?? '—' }}</strong>
                                <div class="help">
                                    {{ $mahasiswa->user?->email ?? '—' }}
                                </div>
                            </td>

                            <td>
                                {{ $mahasiswa->riwayatAktif?->kurikulum?->programStudi?->nama ?? 'Belum ada riwayat aktif' }}
                            </td>

                            <td>
                                <span class="badge">
                                    {{ $mahasiswa->user?->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a class="button secondary small"
                                        href="{{ route('admin.mahasiswa.show', $mahasiswa) }}"
                                        aria-label="Detail mahasiswa {{ $mahasiswa->nim }}">
                                        Detail
                                    </a>

                                    <a class="button small" href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}"
                                        aria-label="Edit mahasiswa {{ $mahasiswa->nim }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Tidak ada mahasiswa yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-body">
            @include('mahasiswa._pagination', ['paginator' => $daftar])
        </div>
    </section>
@endsection
