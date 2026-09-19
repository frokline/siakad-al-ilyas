@extends('layouts.keuangan')
@section('title', 'Edit Jenis Biaya')
@section('content')
    <div class="heading">
        <h1>Edit {{ $jenisBiaya->kode }}</h1><a href="{{ route('keuangan.jenis-biaya.show', $jenisBiaya) }}">Kembali ke
            detail</a>
    </div>
    <p class="notice">Perubahan ini memperbarui nama/keterangan katalog. Kode tetap dan setiap perubahan tercatat.</p>
    <form class="card form-card" method="post" action="{{ route('keuangan.jenis-biaya.update', $jenisBiaya) }}">
        @include('jenis_biaya._form', ['baru' => false])
    </form>
@endsection
