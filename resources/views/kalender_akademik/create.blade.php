@extends('layouts.kalender_akademik')
@section('title', 'Tambah Agenda')
@section('content')
    <h1>Tambah agenda akademik</h1>
    <p class="notice">Agenda berjenis KRS hanya informasi kalender. Batas operasional KRS dikelola pada Periode Akademik.</p>
    <form class="card form-card" method="post" action="{{ route('kalender.store') }}">@include('kalender_akademik._form', ['baru' => true])</form>
@endsection
