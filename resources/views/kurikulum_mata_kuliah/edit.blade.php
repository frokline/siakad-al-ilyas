@extends('layouts.siakad')

@section('title', 'Edit Mata Kuliah Kurikulum')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Mata Kuliah Kurikulum</h1>

            <p class="subtitle">
                {{ $kurikulum->kode }} — {{ $kurikulum->nama }}
            </p>
        </div>
    </div>

    <div class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.kurikulum.mata-kuliah.update', [$kurikulum, $detail]) }}">
            @csrf
            @method('PATCH')

            @include('kurikulum_mata_kuliah._form')

            <div class="actions">
                <button class="button" type="submit">
                    Simpan Perubahan
                </button>

                <a class="button button-secondary"
                    href="{{ route('admin.kurikulum.mata-kuliah.show', [$kurikulum, $detail]) }}">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
