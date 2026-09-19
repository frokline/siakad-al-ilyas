@extends('layouts.kegiatan')
@section('title', 'Edit Draf Kegiatan')
@section('content')
    <h1>Edit draf kegiatan</h1>
    <form method="post" action="{{ route('kegiatan.update', $kegiatan) }}" class="card form-card">
        @csrf @method('PATCH')
        <input type="hidden" name="versi" value="{{ old('versi', $kegiatan->versiForm()) }}">
        @include('kegiatan._form')
    </form>
@endsection
