@extends('layouts.berkas')
@section('title', 'Edit keterangan berkas')
@section('content')
    <div class="heading">
        <h1>Edit keterangan berkas</h1><a href="{{ route('berkas.show', $file) }}">Detail berkas</a>
    </div>
    <section class="card">
        <p>{{ $file->nama_asli }} · Revisi {{ $file->revisi }}</p>@include('berkas._form')
    </section>
@endsection
