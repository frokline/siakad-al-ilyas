@extends('layouts.siakad')

@section('title', 'Edit Pengguna')

@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}">Pengguna</a>
    <span> / </span>
    <a href="{{ route('admin.users.show', $user) }}">{{ $user->nama }}</a>
    <span> / Edit</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Pengguna</h1>
            <p class="subtitle">{{ $user->nama }}</p>
        </div>
    </div>

    <section class="card form-card user-form">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PATCH')

            @include('users._form')

            <div class="actions">
                <button type="submit" class="button">
                    Simpan Perubahan
                </button>

                <a href="{{ route('admin.users.show', $user) }}" class="button button-secondary">
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection
