@extends('layouts.siakad')
@section('title', 'Edit Rencana Pertemuan')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit Pertemuan {{ $sesi->nomor }}</h1>
            <p class="subtitle">{{ $kelas->kode }} · Revisi {{ $sesi->revisi }}</p>
        </div><a class="button secondary" href="{{ route('admin.pertemuan.show', $sesi) }}">Detail sesi</a>
    </div>
    <section class="card pertemuan-section">
        <div class="card-header">
            <h2>Kelas</h2>
        </div>
        <div class="panel-body">@include('admin.pertemuan._kelas')</div>
    </section>
    <section class="card pertemuan-section">
        <div class="card-header">
            <h2>Rencana sesi</h2>
        </div>
        <div class="panel-body">
            @unless ($boleh)
                <div class="alert alert-error" role="alert">Rencana hanya dapat diubah pada sesi terjadwal dengan
                    kelas/periode terbuka.</div>
            @endunless
            <form method="POST" action="{{ route('admin.pertemuan.update', $sesi) }}">@include('admin.pertemuan._form')</form>
        </div>
    </section>
    <section class="card pertemuan-section">
        <div class="card-header">
            <h2>Seluruh sesi kelas</h2>
        </div>@include('admin.pertemuan._daftar-sesi')
    </section>
@endsection
