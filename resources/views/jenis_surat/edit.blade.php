@extends('layouts.surat')
@section('title', 'Edit Jenis Surat')
@section('content')
    <div class="heading">
        <h1>Edit {{ $jenisSurat->kode }}</h1><a href="{{ route('admin.jenis-surat.show', $jenisSurat) }}">Kembali ke
            detail</a>
    </div>
    <p class="notice">Perubahan ini memperbarui nama/syarat katalog. Kode tetap dan setiap perubahan tercatat.</p>
    <form class="card form-card" method="post" action="{{ route('admin.jenis-surat.update', $jenisSurat) }}">
        @include('jenis_surat._form', ['baru' => false])
    </form>
@endsection
