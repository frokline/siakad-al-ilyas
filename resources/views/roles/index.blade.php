@extends('layouts.siakad')

@section('title', 'Peran Pengguna')

@section('breadcrumb')
    <span aria-current="page">Peran pengguna</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Peran pengguna</h1>
            <p class="subtitle">
                Kelola nama peran yang digunakan dalam SIAKAD.
            </p>
        </div>

        <span class="badge">
            {{ $roles->count() }} peran
        </span>
    </div>

    <section class="card" aria-labelledby="daftar-peran">
        <div class="card-header">
            <h2 id="daftar-peran">Daftar peran</h2>
        </div>

        <div class="table-wrap">
            <table>
                <caption>
                    Kode peran digunakan sebagai identitas tetap dalam sistem.
                </caption>

                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama peran</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <th scope="row">{{ $role->nama }}</th>

                            <td>
                                <code>{{ $role->kode }}</code>
                            </td>

                            <td>
                                <div class="actions">
                                    <a
                                        href="{{ route('admin.roles.show', $role) }}"
                                        class="button button-secondary button-small"
                                        aria-label="Lihat detail peran {{ $role->nama }}"
                                    >
                                        Detail
                                    </a>

                                    @can('kelola-peran')
                                        <a
                                            href="{{ route('admin.roles.edit', $role) }}"
                                            class="button button-small"
                                            aria-label="Edit nama peran {{ $role->nama }}"
                                        >
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty">
                                Data peran belum tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection