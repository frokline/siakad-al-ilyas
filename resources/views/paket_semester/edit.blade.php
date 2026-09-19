@extends('layouts.siakad')

@section('title', 'Edit Paket Semester')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Paket Semester</h1>

            <p class="subtitle">
                {{ $paketSemester->nama }}
                — Versi {{ $paketSemester->versi }}
            </p>
        </div>

        <a class="button secondary" href="{{ route('admin.paket-semester.show', $paketSemester) }}">
            Kembali
        </a>
    </div>

    @if (!$indukAktif)
        <div class="alert alert-error" role="alert">
            Kurikulum atau program studi sudah tidak aktif.
            Aktifkan kembali sebelum mengubah paket, atau arsipkan paket
            melalui halaman detail.
        </div>
    @endif

    <section class="card">
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.paket-semester.update', $paketSemester) }}">
                @csrf
                @method('PATCH')

                @include('paket_semester._form')

                <div class="actions">
                    <button class="button" type="submit" @disabled(!$indukAktif)>
                        Simpan Paket
                    </button>

                    <a class="button secondary" href="{{ route('admin.paket-semester.show', $paketSemester) }}">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection
