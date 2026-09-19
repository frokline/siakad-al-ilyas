@extends('layouts.keuangan')
@section('title', 'Tambah Jenis Biaya')
@section('content')
    <div class="heading">
        <h1>Tambah jenis biaya</h1><a href="{{ route('keuangan.jenis-biaya.index') }}">Kembali ke daftar</a>
    </div>
    <p class="notice">Jenis biaya baru berstatus Aktif. Nominal, bulan, dan jatuh tempo ditentukan ketika membuat tagihan.
    </p>
    <form class="card form-card" method="post" action="{{ route('keuangan.jenis-biaya.store') }}">
        @include('jenis_biaya._form', ['baru' => true])
    </form>
@endsection
