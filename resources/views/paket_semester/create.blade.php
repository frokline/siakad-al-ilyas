@extends('layouts.siakad')

@section('title', 'Tambah Paket Semester')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Paket Semester</h1>
            <p class="subtitle">Buat identitas paket sebelum memilih mata kuliah.</p>
        </div>

        <a class="button secondary" href="{{ route('admin.paket-semester.index') }}">
            Kembali
        </a>
    </div>

    @if ($daftarKurikulum->isEmpty())
        <div class="alert alert-error" role="alert">
            Belum ada kurikulum aktif pada program studi aktif.

            <a href="{{ route('admin.kurikulum.index') }}">
                Periksa kurikulum
            </a>
        </div>
    @endif

    <section class="card form-card">
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.paket-semester.store') }}">
                @csrf

                @include('paket_semester._form')

                <div class="actions">
                    <button class="button" type="submit" @disabled($daftarKurikulum->isEmpty())>
                        Buat Paket
                    </button>

                    <a class="button secondary" href="{{ route('admin.paket-semester.index') }}">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection
