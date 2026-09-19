@extends('layouts.siakad')

@section('title', 'Detail Dosen')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $dosen->user->nama }}</h1>
            <p class="subtitle">Kode dosen {{ $dosen->kode_dosen }}</p>
        </div>

        <div class="actions">
            <a class="button" href="{{ route('admin.dosen.edit', $dosen) }}">
                Edit Dosen
            </a>

            <a class="button button-secondary" href="{{ route('admin.dosen.index') }}">
                Kembali
            </a>
        </div>
    </div>

    @if (!$memilikiPeranDosen)
        <div class="alert" role="status">
            Akun terhubung tidak memiliki peran Dosen.

            <a href="{{ route('admin.users.edit', $dosen->user) }}">
                Kelola peran akun
            </a>
        </div>
    @endif

    <div class="card panel-body">
        <dl class="detail-grid">
            <div>
                <dt>Kode dosen</dt>
                <dd>{{ $dosen->kode_dosen }}</dd>
            </div>

            <div>
                <dt>Nama dosen</dt>
                <dd>{{ $dosen->user->nama }}</dd>
            </div>

            <div>
                <dt>NIDN</dt>
                <dd>{{ $dosen->nidn ?? 'Belum diisi' }}</dd>
            </div>

            <div>
                <dt>Gelar</dt>
                <dd>{{ $dosen->gelar ?? 'Belum diisi' }}</dd>
            </div>

            <div>
                <dt>Akun terhubung</dt>
                <dd>
                    <a href="{{ route('admin.users.show', $dosen->user) }}">
                        {{ $dosen->user->username }}
                    </a>
                </dd>
            </div>

            <div>
                <dt>Email</dt>
                <dd>{{ $dosen->user->email }}</dd>
            </div>

            <div>
                <dt>Telepon</dt>
                <dd>{{ $dosen->user->telepon ?? 'Belum diisi' }}</dd>
            </div>

            <div>
                <dt>Status dosen</dt>
                <dd>
                    <span class="badge {{ $dosen->isAktif() ? 'badge-active' : 'badge-inactive' }}">
                        {{ $statusOptions[$dosen->status] }}
                    </span>
                </dd>
            </div>

            <div>
                <dt>Status akun</dt>
                <dd>
                    <span class="badge {{ $dosen->user->isAktif() ? 'badge-active' : 'badge-inactive' }}">
                        {{ $dosen->user->isAktif() ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>
@endsection
