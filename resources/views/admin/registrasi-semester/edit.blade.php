@extends('layouts.siakad')

@section('title', 'Edit Registrasi Semester')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Registrasi Semester</h1>
            <p class="subtitle">Periode {{ $registrasi->periodeAkademik->kode }}</p>
        </div>
        <a class="button secondary" href="{{ route('admin.registrasi-semester.show', $registrasi) }}">Detail</a>
    </div>

    <section class="card">
        <div class="panel-body">
            @include('admin.registrasi-semester._identitas')
        </div>
    </section>

    <section class="card registrasi-section">
        <div class="card-header">
            <h2>Penempatan dan status</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.registrasi-semester.update', $registrasi) }}">
                @include('admin.registrasi-semester._form')
            </form>
        </div>
    </section>
@endsection
