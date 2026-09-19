@extends('layouts.siakad')

@section('title', 'Edit Periode Akademik')

@section('breadcrumb')
    <a href="{{ route('admin.periode-akademik.index') }}">Periode Akademik</a>
    <span> / </span>

    <a href="{{ route('admin.periode-akademik.show', $periodeAkademik) }}">
        {{ $periodeAkademik->kode }}
    </a>

    <span> / Edit</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Periode Akademik</h1>
            <p class="subtitle">{{ $periodeAkademik->kode }}</p>
        </div>
    </div>

    <section class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.periode-akademik.update', $periodeAkademik) }}">
            @csrf
            @method('PATCH')

            @include('periode_akademik._form')

            <div class="actions">
                <button type="submit" class="button">Simpan Perubahan</button>

                <a href="{{ route('admin.periode-akademik.show', $periodeAkademik) }}" class="button button-secondary">
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection
