@extends('layouts.siakad')

@section('title', 'Tambah Mahasiswa')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Mahasiswa</h1>
            <p class="subtitle">Pilih akun pengguna, kemudian lengkapi biodatanya.</p>
        </div>

        <a class="button secondary" href="{{ route('admin.mahasiswa.index') }}">
            Kembali
        </a>
    </div>

    @if ($akun === null)
        @if (!empty($filter['user_id']))
            <div class="mhs-warning" role="alert">
                Akun yang dipilih sudah tidak tersedia untuk pendaftaran mahasiswa.
                Silakan pilih akun lain.
            </div>
        @endif

        @error('user_id')
            <p class="mhs-error" role="alert">{{ $message }}</p>
        @enderror

        <section class="card">
            <div class="panel-body">
                <p class="help">
                    Daftar ini menampilkan akun aktif berperan mahasiswa
                    yang belum mempunyai profil mahasiswa.
                </p>

                <form method="GET" action="{{ route('admin.mahasiswa.create') }}" class="mhs-filters">
                    <div class="field">
                        <label for="q">Cari akun pengguna</label>

                        <input type="search" id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                            placeholder="Nama, email, atau username">
                    </div>

                    <div class="actions">
                        <button class="button" type="submit">Cari akun</button>

                        <a class="button secondary" href="{{ route('admin.mahasiswa.create') }}">
                            Reset
                        </a>
                    </div>
                </form>

                @can('kelola-pengguna')
                    <p class="help">
                        Belum ada akun?
                        <a href="{{ route('admin.users.create') }}">
                            Buat pengguna dengan peran mahasiswa.
                        </a>
                    </p>
                @endcan
            </div>
        </section>

        <section class="card mhs-section">
            <div class="card-header">
                <h2>Pilih akun</h2>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Nama</th>
                            <th scope="col">Username</th>
                            <th scope="col">Email</th>
                            <th scope="col">Tindakan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($akunDaftar as $pilihan)
                            <tr>
                                <td>{{ $pilihan->nama }}</td>
                                <td>{{ $pilihan->username }}</td>
                                <td>{{ $pilihan->email }}</td>
                                <td>
                                    <a class="button small"
                                        href="{{ route('admin.mahasiswa.create', ['user_id' => $pilihan->id]) }}"
                                        aria-label="Pilih akun {{ $pilihan->nama }}">
                                        Pilih
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    Tidak ada akun yang memenuhi pilihan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="panel-body">
                @include('mahasiswa._pagination', ['paginator' => $akunDaftar])
            </div>
        </section>
    @else
        <section class="card">
            <div class="card-header">
                <h2>Biodata mahasiswa</h2>

                <a href="{{ route('admin.mahasiswa.create') }}">
                    Ganti pilihan akun
                </a>
            </div>

            <div class="panel-body">
                <form method="POST" action="{{ route('admin.mahasiswa.store') }}">
                    @csrf

                    @include('mahasiswa._form')
                </form>
            </div>
        </section>
    @endif
@endsection
