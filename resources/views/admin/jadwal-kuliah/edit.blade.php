@extends('layouts.siakad')
@section('title', 'Edit Jadwal Kuliah')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Jadwal #{{ $jadwal->id }}</h1>
            <p class="subtitle">Revisi {{ $jadwal->revisi }} · {{ $kelas->kode }}</p>
        </div>
        <a class="button secondary" href="{{ route('admin.jadwal-kuliah.show', $jadwal) }}">Detail jadwal</a>
    </div>
    <section class="card jadwal-section">
        <div class="card-header">
            <h2>Kelas dan pengajar</h2>
        </div>
        <div class="panel-body">@include('admin.jadwal-kuliah._kelas')</div>
    </section>
    <section class="card jadwal-section">
        <div class="card-header">
            <h2>Perubahan pola</h2>
        </div>
        <div class="panel-body">
            @unless ($boleh)
                <div class="alert alert-error" role="alert">Isi pola sudah dikunci karena kelas/periode ditutup. Tindakan
                    nonaktifkan saja tersedia pada halaman detail selama pola masih aktif.</div>
            @endunless
            <form method="POST" action="{{ route('admin.jadwal-kuliah.update', $jadwal) }}">
                @include('admin.jadwal-kuliah._form')
            </form>
        </div>
    </section>
    <section class="card jadwal-section">
        <div class="card-header">
            <h2>Seluruh pola kelas</h2>
        </div>
        @include('admin.jadwal-kuliah._pola')
    </section>
@endsection
