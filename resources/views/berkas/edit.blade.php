@extends('layouts.admin')

@section('title', 'Edit Keterangan Berkas')

@section('content')
    @include('berkas._pesan')

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Edit keterangan berkas</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $file->nama_asli }} &middot; Revisi {{ $file->revisi }}</p>
        </div>

        <a href="{{ route('berkas.show', $file) }}"
            class="self-start rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">&larr;
            Detail berkas</a>
    </div>

    <section class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('berkas._form')
    </section>
@endsection
