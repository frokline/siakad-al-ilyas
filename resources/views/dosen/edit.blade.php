@extends('layouts.siakad')

@section('title', 'Edit Dosen')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Dosen</h1>

            <p class="subtitle">
                {{ $dosen->kode_dosen }} — {{ $dosen->user->nama }}
            </p>
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

    <div class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.dosen.update', $dosen) }}">
            @csrf
            @method('PATCH')

            @include('dosen._form')

            <div class="actions">
                <button class="button" type="submit">
                    Simpan Perubahan
                </button>

                <a class="button button-secondary" href="{{ route('admin.dosen.show', $dosen) }}">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
