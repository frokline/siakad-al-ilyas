@extends('layouts.admin')

@section('title', 'Edit Draf Pengumuman')

@section('content')
    @include('pengumuman._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('pengumuman.index', ['mode' => 'kelola']) }}"
                    class="hover:text-siakad-active">Pengumuman</a>
                <span>/</span>
                <a href="{{ route('pengumuman.show', $item) }}"
                    class="max-w-[14rem] truncate hover:text-siakad-active">{{ $item->judul }}</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Edit</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Edit draf pengumuman</h1>
            <div class="mt-2">@include('pengumuman._lencana', ['status' => $item->status])</div>
        </div>
        <a href="{{ route('pengumuman.show', $item) }}"
            class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
            Kembali</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Formulir pengumuman</h2>
            </div>
            <form class="p-6" method="post" action="{{ route('pengumuman.update', $item) }}">
                @method('PATCH')
                @include('pengumuman._form')
            </form>
        </section>

        <aside class="space-y-4">
            @include('pengumuman._search')
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider">Hanya draf yang dapat diedit</h2>
                <p class="mt-2">Setelah terbit, isi dan sasaran terkunci. Untuk perubahan, arsipkan lalu buat
                    pengumuman baru.</p>
            </div>
        </aside>
    </div>
@endsection
