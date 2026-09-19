@extends('layouts.kegiatan')
@section('title', 'Buat Kegiatan')
@section('content')
    <h1>Buat kegiatan kuliah</h1>
    <form method="post" action="{{ route('kegiatan.store') }}" class="card form-card">
        @csrf
        <input type="hidden" name="kelas_kuliah_id" value="{{ $kelas->id }}">
        <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
        @include('kegiatan._form')
    </form>
@endsection
