@extends('layouts.siakad')

@section('title', 'Tambah Dosen')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Dosen</h1>
            <p class="subtitle">Hubungkan akun dengan identitas dosen.</p>
        </div>
    </div>

    @if ($daftarPengguna->isEmpty())
        <div class="alert" role="status">
            Belum ada akun aktif berperan Dosen yang dapat digunakan.

            <a href="{{ route('admin.users.index') }}">
                Kelola akun melalui menu Pengguna
            </a>
        </div>
    @endif

    <div class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.dosen.store') }}">
            @csrf

            @include('dosen._form')

            <div class="actions">
                <button class="button" type="submit" @disabled($daftarPengguna->isEmpty())>
                    Simpan Dosen
                </button>

                <a class="button button-secondary" href="{{ route('admin.dosen.index') }}">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
