@extends('layouts.admin')

@section('title', 'Edit Jenis Biaya')

@section('content')
    @include('jenis_biaya._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('keuangan.jenis-biaya.index') }}" class="hover:text-siakad-active">Jenis biaya</a>
                <span>/</span>
                <a href="{{ route('keuangan.jenis-biaya.show', $jenisBiaya) }}"
                    class="hover:text-siakad-active">{{ $jenisBiaya->kode }}</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Edit</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Edit {{ $jenisBiaya->kode }}</h1>
            <div class="mt-2">@include('jenis_biaya._lencana', ['aktif' => $jenisBiaya->aktif])</div>
        </div>
        <a href="{{ route('keuangan.jenis-biaya.show', $jenisBiaya) }}"
            class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
            Kembali ke detail</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Formulir jenis biaya</h2>
            </div>
            <form class="p-6" method="post" action="{{ route('keuangan.jenis-biaya.update', $jenisBiaya) }}">
                @include('jenis_biaya._form', ['baru' => false])
            </form>
        </section>

        <aside class="space-y-4">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider">Setiap perubahan tercatat</h2>
                <p class="mt-2">Perubahan ini memperbarui nama dan keterangan katalog. Kode tetap, dan setiap perubahan
                    tercatat pada riwayat.</p>
            </div>
            @unless ($jenisBiaya->aktif)
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-800 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wider">Jenis biaya nonaktif</h2>
                    <p class="mt-2">Aktifkan kembali jenis biaya sebelum mengubah nama atau keterangannya.</p>
                </div>
            @endunless
        </aside>
    </div>
@endsection
