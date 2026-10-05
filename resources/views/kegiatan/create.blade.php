@extends('layouts.admin')

@section('title', 'Bagikan Pembelajaran')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Bagikan pembelajaran</h1>
        <p class="mt-1 text-sm text-slate-500">Materi, tugas, latihan, UTS, dan UAS dikelola dari satu halaman.</p>
    </div>

    <form method="post" action="{{ route('kegiatan.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
        @csrf
        <input type="hidden" name="kelas_kuliah_id" value="{{ $kelas->id }}">
        <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">

        @include('kegiatan._form')
    </form>
@endsection
