@extends('layouts.tagihan')
@section('title', 'Edit Draf Tagihan')
@section('content')
    <div class="heading">
        <h1>Edit draf</h1><a href="{{ route('tagihan.show', $tagihan) }}">Kembali</a>
    </div>
    <p>{{ $tagihan->mahasiswa->nim }} — {{ $tagihan->mahasiswa->user->nama }} · {{ $tagihan->jenisBiaya->nama }}</p>
    <form class="card form-card" method="post" action="{{ route('tagihan.update', $tagihan) }}">@include('tagihan._form', ['baru' => false])
    </form>
@endsection
