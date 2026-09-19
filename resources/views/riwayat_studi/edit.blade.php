@extends('layouts.siakad')

@section('title', 'Edit Riwayat Studi')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Riwayat Studi</h1>

            <p class="subtitle">
                {{ $riwayatStudi->mahasiswa->nim }}
                — {{ $riwayatStudi->mahasiswa->user->nama }}
            </p>
        </div>

        <a class="button secondary" href="{{ route('admin.riwayat-studi.show', $riwayatStudi) }}">
            Kembali
        </a>
    </div>

    <section class="card form-card">
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.riwayat-studi.update', $riwayatStudi) }}">
                @csrf
                @method('PATCH')

                @include('riwayat_studi._form')

                <div class="actions">
                    <button class="button" type="submit">
                        Simpan Perubahan
                    </button>

                    <a class="button secondary" href="{{ route('admin.riwayat-studi.show', $riwayatStudi) }}">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection
