@extends('layouts.siakad')

@section('title', 'Tambah Mata Kuliah')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Mata Kuliah</h1>
            <p class="subtitle">Tambahkan mata kuliah pada program studi.</p>
        </div>
    </div>

    @if ($daftarProdi->isEmpty())
        <div class="alert alert-error" role="alert">
            Belum ada program studi aktif.

            <a href="{{ route('admin.program-studi.index') }}">
                Kelola program studi
            </a>
        </div>
    @endif

    <div class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.mata-kuliah.store') }}">
            @csrf

            @include('mata_kuliah._form')

            <div class="actions">
                <button class="button" type="submit" @disabled($daftarProdi->isEmpty())>
                    Simpan Mata Kuliah
                </button>

                <a class="button button-secondary" href="{{ route('admin.mata-kuliah.index') }}">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
