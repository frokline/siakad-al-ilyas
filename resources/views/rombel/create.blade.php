@extends('layouts.siakad')

@section('title', 'Tambah Rombel')

@section('content')
    @php
        $siap = $daftarPeriode->isNotEmpty() && $daftarPaket->isNotEmpty();
    @endphp

    <div class="page-heading">
        <div>
            <h1>Tambah Rombel</h1>
            <p class="subtitle">Kelompok mahasiswa untuk satu periode akademik.</p>
        </div>

        <a class="button secondary" href="{{ route('admin.rombel.index') }}">
            Kembali
        </a>
    </div>

    @if ($daftarPeriode->isEmpty())
        <div class="alert alert-error" role="alert">
            Belum ada periode berstatus persiapan atau aktif.

            <a href="{{ route('admin.periode-akademik.index') }}">
                Kelola periode akademik
            </a>
        </div>
    @endif

    @if ($daftarPaket->isEmpty())
        <div class="alert alert-error" role="alert">
            Belum ada paket yang memenuhi syarat.
            Periksa status penerbitan, kurikulum, program studi,
            dan mata kuliah paket.

            <a href="{{ route('admin.paket-semester.index') }}">
                Kelola paket semester
            </a>
        </div>
    @endif

    <section class="card form-card">
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.rombel.store') }}">
                @csrf

                @include('rombel._form')

                <div class="actions">
                    <button class="button" type="submit" @disabled(!$siap)>
                        Simpan Rombel
                    </button>

                    <a class="button secondary" href="{{ route('admin.rombel.index') }}">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection
