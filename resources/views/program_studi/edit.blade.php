@extends('layouts.siakad')

@section('title', 'Edit Program Studi')

@section('breadcrumb')
    <a href="{{ route('admin.program-studi.index') }}">Program Studi</a>
    <span> / </span>

    <a href="{{ route('admin.program-studi.show', $programStudi) }}">
        {{ $programStudi->kode }}
    </a>

    <span> / Edit</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Program Studi</h1>
            <p class="subtitle">{{ $programStudi->nama }}</p>
        </div>
    </div>

    <section class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.program-studi.update', $programStudi) }}">
            @csrf
            @method('PATCH')

            @include('program_studi._form')

            <div class="actions">
                <button type="submit" class="button">
                    Simpan Perubahan
                </button>

                <a href="{{ route('admin.program-studi.show', $programStudi) }}" class="button button-secondary">
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection
