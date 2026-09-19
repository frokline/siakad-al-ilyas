@extends('layouts.siakad')

@section('title', 'Tambah Program Studi')

@section('breadcrumb')
    <a href="{{ route('admin.program-studi.index') }}">Program Studi</a>
    <span> / Tambah</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Program Studi</h1>
            <p class="subtitle">Lengkapi data program studi lembaga.</p>
        </div>
    </div>

    <section class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.program-studi.store') }}">
            @csrf

            @include('program_studi._form')

            <div class="actions">
                <button type="submit" class="button">
                    Simpan Program Studi
                </button>

                <a href="{{ route('admin.program-studi.index') }}" class="button button-secondary">
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection
