@extends('layouts.siakad')

@section('title', 'Tambah Pengguna')

@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}">Pengguna</a>
    <span> / Tambah</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Pengguna</h1>
            <p class="subtitle">Buat akun dan tentukan perannya.</p>
        </div>
    </div>

    <section class="card form-card user-form">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            @include('users._form')

            <div class="actions">
                <button type="submit" class="button">
                    Simpan Pengguna
                </button>

                <a href="{{ route('admin.users.index') }}" class="button button-secondary">
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection
