@extends('layouts.surat')
@section('title', 'Tambah Jenis Surat')
@section('content')
    <div class="heading">
        <h1>Tambah jenis surat</h1><a href="{{ route('admin.jenis-surat.index') }}">Kembali ke daftar</a>
    </div>
    <p class="notice">Jenis surat baru berstatus Aktif. Isi nama layanan dan persyaratan yang telah ditetapkan bagian
        akademik.</p>
    <form class="card form-card" method="post" action="{{ route('admin.jenis-surat.store') }}">
        @include('jenis_surat._form', ['baru' => true])
    </form>
@endsection
