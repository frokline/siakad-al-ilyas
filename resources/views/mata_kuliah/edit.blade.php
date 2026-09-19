@extends('layouts.siakad')

@section('title', 'Edit Mata Kuliah')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Mata Kuliah</h1>

            <p class="subtitle">
                {{ $mataKuliah->kode }} — {{ $mataKuliah->nama }}
            </p>
        </div>
    </div>

    @if (!$mataKuliah->programStudi->aktif)
        <div class="alert" role="status">
            Program studi sedang nonaktif.
            Pengaktifan kembali mata kuliah memerlukan program studi aktif.
        </div>
    @endif

    <div class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.mata-kuliah.update', $mataKuliah) }}">
            @csrf
            @method('PATCH')

            @include('mata_kuliah._form')

            <div class="actions">
                <button class="button" type="submit">
                    Simpan Perubahan
                </button>

                <a class="button button-secondary" href="{{ route('admin.mata-kuliah.show', $mataKuliah) }}">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
