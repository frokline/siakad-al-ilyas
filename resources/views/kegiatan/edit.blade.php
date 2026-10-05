@extends('layouts.admin')

@section('title', 'Edit Pembelajaran')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Edit pembelajaran</h1>
        <p class="mt-1 text-sm text-slate-500">Perubahan langsung diterapkan dan diberitahukan kepada mahasiswa.</p>
    </div>

    <form method="post" action="{{ route('kegiatan.update', $kegiatan) }}" enctype="multipart/form-data"
        class="max-w-3xl space-y-6">
        @csrf
        @method('PATCH')
        <input type="hidden" name="versi" value="{{ old('versi', $kegiatan->versiForm()) }}">

        @include('kegiatan._form')
    </form>
@endsection
