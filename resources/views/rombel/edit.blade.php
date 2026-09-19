@extends('layouts.siakad')

@section('title', 'Edit Rombel')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Rombel</h1>

            <p class="subtitle">
                {{ $rombel->kode }}
                — {{ $rombel->periodeAkademik->kode }}
            </p>
        </div>

        <a class="button secondary" href="{{ route('admin.rombel.show', $rombel) }}">
            Kembali
        </a>
    </div>

    <section class="card form-card">
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.rombel.update', $rombel) }}">
                @csrf
                @method('PATCH')

                @include('rombel._form')

                <div class="actions">
                    <button class="button" type="submit">
                        Simpan Perubahan
                    </button>

                    <a class="button secondary" href="{{ route('admin.rombel.show', $rombel) }}">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection
