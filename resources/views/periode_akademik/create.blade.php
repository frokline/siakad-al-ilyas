@extends('layouts.siakad')

@section('title', 'Tambah Periode Akademik')

@section('breadcrumb')
    <a href="{{ route('admin.periode-akademik.index') }}">Periode Akademik</a>
    <span> / Tambah</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Periode Akademik</h1>
            <p class="subtitle">Atur semester dan jadwal pengisian KRS.</p>
        </div>
    </div>

    <section class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.periode-akademik.store') }}">
            @csrf

            @include('periode_akademik._form')

            <div class="actions">
                <button type="submit" class="button">Simpan Periode</button>

                <a href="{{ route('admin.periode-akademik.index') }}" class="button button-secondary">
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection
