@extends('layouts.siakad')

@section('title', 'Edit Mahasiswa')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Mahasiswa</h1>
            <p class="subtitle">
                {{ $mahasiswa->nim }} — {{ $akun->nama }}
            </p>
        </div>

        <a class="button secondary" href="{{ route('admin.mahasiswa.show', $mahasiswa) }}">
            Kembali
        </a>
    </div>

    <section class="card">
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.mahasiswa.update', $mahasiswa) }}">
                @csrf
                @method('PATCH')

                @include('mahasiswa._form')
            </form>
        </div>
    </section>
@endsection
