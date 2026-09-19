@extends('layouts.siakad')
@section('title', 'Edit Catatan KRS')

@section('content')
    @php
        $bolehSimpan =
            $registrasi->status === \App\Models\RegistrasiSemester::AKTIF &&
            $registrasi->riwayatStudi->status === \App\Models\RiwayatStudi::AKTIF &&
            $registrasi->periodeAkademik->isKrsOpen();
    @endphp
    <div class="page-heading">
        <div>
            <h1>Edit Catatan KRS #{{ $krs->id }}</h1>
            <p class="subtitle">Draf versi {{ $krs->versi }}. Paket dan rombel tetap mengikuti registrasi.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.krs.show', $krs) }}">Detail KRS</a>
    </div>

    <section class="card">
        <div class="panel-body">@include('admin.krs._identitas')</div>
        @include('admin.krs._mata_kuliah')
    </section>

    <section class="card krs-section">
        <div class="card-header">
            <h2>Catatan draf</h2>
        </div>
        <div class="panel-body">
            @unless ($bolehSimpan)
                <div class="alert alert-error" role="alert">Pengubahan catatan memerlukan registrasi dan riwayat aktif serta
                    jadwal KRS yang terbuka.</div>
            @endunless
            <form method="POST" action="{{ route('admin.krs.update', $krs) }}">
                @include('admin.krs._form')
            </form>
        </div>
    </section>
@endsection
