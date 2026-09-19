@extends('layouts.berkas')
@section('title', 'Unggah berkas')
@section('content')
    <div class="heading">
        <h1>Unggah berkas</h1><a href="{{ route('berkas.index') }}">Kembali</a>
    </div>
    <section class="card">
        <p>Berkas yang diunggah akan masuk ke penyimpanan pribadi akun Anda.</p>@include('berkas._form')
    </section>
@endsection
