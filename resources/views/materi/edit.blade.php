@extends('layouts.materi')
@section('title', 'Edit Draf Materi')
@section('content')
    <h1>Edit draf materi</h1>
    <form method="post" action="{{ route('materi.update', $materi) }}" class="card form-card">
        @csrf @method('PATCH')
        <input type="hidden" name="versi" value="{{ old('versi', $materi->versiForm()) }}">
        @include('materi._form')
    </form>
@endsection
