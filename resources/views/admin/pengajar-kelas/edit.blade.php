@extends('layouts.siakad')
@section('title', 'Edit Penugasan Dosen')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Penugasan #{{ $penugasan->id }}</h1>
            <p class="subtitle">{{ $penugasan->dosen->user->nama }} — revisi {{ $penugasan->revisi }}</p>
        </div>
        <a class="button secondary" href="{{ route('admin.pengajar-kelas.show', $penugasan) }}">Detail penugasan</a>
    </div>

    <section class="card">
        <div class="card-header">
            <h2>Kelas kuliah</h2>
        </div>
        <div class="panel-body">@include('admin.pengajar-kelas._kelas')</div>
    </section>

    <section class="card pengajar-section">
        <div class="card-header">
            <h2>Tim pengajar saat ini</h2>
        </div>
        @include('admin.pengajar-kelas._tim')
    </section>

    <section class="card pengajar-section">
        <div class="card-header">
            <h2>Peran dan status penugasan</h2>
        </div>
        <div class="panel-body">
            @unless ($bolehSimpan)
                <div class="alert alert-error" role="alert">Kelas sudah selesai/diarsipkan atau periode telah diarsipkan.
                    Riwayat penugasan hanya dapat dibaca.</div>
            @endunless
            <form method="POST" action="{{ route('admin.pengajar-kelas.update', $penugasan) }}">
                @include('admin.pengajar-kelas._form')
            </form>
        </div>
    </section>
@endsection
