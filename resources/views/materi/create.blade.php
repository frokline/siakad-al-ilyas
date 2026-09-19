@extends('layouts.materi')
@section('title', 'Buat Materi')
@section('content')
    <h1>Buat materi kuliah</h1>
    <form method="post" action="{{ route('materi.store') }}" class="card form-card">
        @csrf
        <input type="hidden" name="kelas_kuliah_id" value="{{ $kelas->id }}">
        <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
        @include('materi._form')
    </form>
@endsection
