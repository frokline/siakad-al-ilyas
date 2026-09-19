@extends('layouts.siakad')

@section('title', 'Detail Mahasiswa')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $mahasiswa->user?->nama ?? 'Detail Mahasiswa' }}</h1>
            <p class="subtitle">NIM {{ $mahasiswa->nim }}</p>
        </div>

        <div class="actions">
            <a class="button" href="{{ route('admin.mahasiswa.edit', $mahasiswa) }}">
                Edit biodata
            </a>

            <a class="button secondary" href="{{ route('admin.mahasiswa.index') }}">
                Kembali
            </a>
        </div>
    </div>

    <section class="card">
        <div class="card-header">
            <h2>Identitas mahasiswa</h2>
        </div>

        <div class="panel-body">
            <dl class="mhs-grid mhs-details">
                <div>
                    <dt>NIM</dt>
                    <dd>{{ $mahasiswa->nim }}</dd>
                </div>

                <div>
                    <dt>Nama lengkap</dt>
                    <dd>{{ $mahasiswa->user?->nama ?? '—' }}</dd>
                </div>

                <div>
                    <dt>Email</dt>
                    <dd>{{ $mahasiswa->user?->email ?? '—' }}</dd>
                </div>

                <div>
                    <dt>Nomor telepon</dt>
                    <dd>{{ $mahasiswa->user?->telepon ?? 'Belum diisi' }}</dd>
                </div>

                <div>
                    <dt>Tempat lahir</dt>
                    <dd>{{ $mahasiswa->tempat_lahir ?? 'Belum diisi' }}</dd>
                </div>

                <div>
                    <dt>Tanggal lahir</dt>
                    <dd>
                        {{ $mahasiswa->tanggal_lahir?->format('d-m-Y') ?? 'Belum diisi' }}
                    </dd>
                </div>

                <div>
                    <dt>Jenis kelamin</dt>
                    <dd>{{ $mahasiswa->labelJenisKelamin() }}</dd>
                </div>

                <div>
                    <dt>Status akun</dt>
                    <dd>
                        {{ $mahasiswa->user?->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                    </dd>
                </div>

                <div class="mhs-full">
                    <dt>Alamat</dt>
                    <dd class="mhs-address">{{ $mahasiswa->alamat ?? 'Belum diisi' }}</dd>
                </div>
            </dl>

            @can('kelola-pengguna')
                <div class="actions mhs-section">
                    <a class="button secondary" href="{{ route('admin.users.show', $mahasiswa->user_id) }}">
                        Lihat akun pengguna
                    </a>
                </div>
            @endcan
        </div>
    </section>

    <section class="card mhs-section">
        <div class="card-header">
            <h2>Riwayat studi</h2>
            <span>{{ $riwayatDaftar->total() }} riwayat</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Angkatan</th>
                        <th scope="col">Program studi</th>
                        <th scope="col">Kurikulum</th>
                        <th scope="col">Status studi</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($riwayatDaftar as $riwayat)
                        <tr>
                            <td>{{ $riwayat->angkatan }}</td>

                            <td>
                                {{ $riwayat->kurikulum?->programStudi?->nama ?? '—' }}
                            </td>

                            <td>{{ $riwayat->kurikulum?->nama ?? '—' }}</td>

                            <td>
                                <span class="badge">
                                    {{ ucfirst($riwayat->status) }}
                                </span>
                            </td>

                            <td>
                                @can('kelola-riwayat-studi')
                                    <a class="button secondary small" href="{{ route('admin.riwayat-studi.show', $riwayat) }}">
                                        Detail riwayat
                                    </a>
                                @else
                                    —
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                Mahasiswa ini belum mempunyai riwayat studi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-body">
            @include('mahasiswa._pagination', ['paginator' => $riwayatDaftar])
        </div>
    </section>
@endsection
