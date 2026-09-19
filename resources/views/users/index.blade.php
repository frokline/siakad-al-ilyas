@extends('layouts.siakad')

@section('title', 'Pengguna')

@section('breadcrumb')
    <span>Pengguna</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Pengguna</h1>
            <p class="subtitle">Kelola akun dan akses pengguna SIAKAD.</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="button">
            Tambah Pengguna
        </a>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="card filters">
        <div class="field">
            <label for="q">Cari pengguna</label>
            <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                placeholder="Nama, username, atau email" maxlength="190">
        </div>

        <div class="field">
            <label for="filter-status">Status</label>

            <select id="filter-status" name="status">
                <option value="">Semua status</option>

                <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>
                    Aktif
                </option>

                <option value="nonaktif" @selected(($filters['status'] ?? '') === 'nonaktif')>
                    Nonaktif
                </option>
            </select>
        </div>

        <div class="actions">
            <button type="submit" class="button">Cari</button>

            <a href="{{ route('admin.users.index') }}" class="button button-secondary">
                Reset
            </a>
        </div>
    </form>

    <section class="card">
        <div class="card-header">
            <strong>{{ number_format($users->total(), 0, ',', '.') }} pengguna</strong>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Username</th>
                        <th scope="col">Email</th>
                        <th scope="col">Peran</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $account)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td>{{ $account->nama }}</td>
                            <td>{{ $account->username }}</td>
                            <td>{{ $account->email }}</td>
                            <td>
                                {{ $account->roles->pluck('nama')->implode(', ') ?: 'Belum ditetapkan' }}
                            </td>
                            <td>
                                <span class="badge {{ $account->isAktif() ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $account->isAktif() ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.users.show', $account) }}"
                                        class="button button-secondary button-small"
                                        aria-label="Detail {{ $account->nama }}">
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.users.edit', $account) }}" class="button button-small"
                                        aria-label="Edit {{ $account->nama }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">
                                Tidak ada pengguna yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($users->hasPages())
        <nav class="pagination" aria-label="Halaman daftar pengguna">
            @if ($users->previousPageUrl())
                <a href="{{ $users->previousPageUrl() }}" class="button button-secondary" rel="prev">
                    Sebelumnya
                </a>
            @endif

            <span>
                Halaman {{ $users->currentPage() }}
                dari {{ $users->lastPage() }}
            </span>

            @if ($users->nextPageUrl())
                <a href="{{ $users->nextPageUrl() }}" class="button button-secondary" rel="next">
                    Berikutnya
                </a>
            @endif
        </nav>
    @endif
@endsection
