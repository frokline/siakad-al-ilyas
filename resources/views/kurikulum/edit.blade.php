@extends('layouts.siakad')

@section('title', 'Edit Kurikulum')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Kurikulum</h1>
            <p class="subtitle">{{ $kurikulum->kode }} — {{ $kurikulum->nama }}</p>
        </div>
    </div>

    @if (!$kurikulum->programStudi->aktif)
        <div class="alert" role="status">
            Program studi sedang nonaktif. Aktivasi kurikulum memerlukan
            program studi aktif.
        </div>
    @endif

    <div class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.kurikulum.update', $kurikulum) }}">
            @csrf
            @method('PATCH')

            @include('kurikulum._form')

            <div class="actions">
                <button class="button" type="submit">
                    Simpan Perubahan
                </button>

                <a class="button button-secondary" href="{{ route('admin.kurikulum.show', $kurikulum) }}">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
