@extends('layouts.siakad')

@section('title', 'Edit Kelas Kuliah')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Kelas Kuliah</h1>
            <p class="subtitle">{{ $kelas->kode }} — {{ $kelas->nama_mk_snapshot }}</p>
        </div>
        <a class="button secondary" href="{{ route('admin.kelas-kuliah.show', $kelas) }}">Detail kelas</a>
    </div>

    <section class="card">
        <div class="panel-body">
            @include('admin.kelas-kuliah._identitas')
        </div>
    </section>

    <section class="card kelas-section">
        <div class="card-header">
            <h2>Kode dan status kelas</h2>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.kelas-kuliah.update', $kelas) }}">
                @include('admin.kelas-kuliah._form')
            </form>
        </div>
    </section>
@endsection
