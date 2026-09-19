@extends('layouts.siakad')

@section('title', 'Tambah Kurikulum')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Kurikulum</h1>
            <p class="subtitle">Buat versi kurikulum untuk program studi.</p>
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
        <form method="POST" action="{{ route('admin.kurikulum.store') }}">
            @csrf

            @include('kurikulum._form')

            <div class="actions">
                <button class="button" type="submit" @disabled($daftarProdi->isEmpty())>
                    Simpan Draf
                </button>

                <a class="button button-secondary" href="{{ route('admin.kurikulum.index') }}">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
