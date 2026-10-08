@extends('layouts.admin')

@section('title', 'Edit Draf Tagihan')

@section('content')
    @include('tagihan._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('tagihan.index') }}" class="hover:text-siakad-active">Tagihan</a>
                <span>/</span>
                <a href="{{ route('tagihan.show', $tagihan) }}" class="hover:text-siakad-active">{{ $tagihan->nomor }}</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Edit draf</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Edit draf tagihan</h1>
            <div class="mt-2">@include('tagihan._lencana', ['status' => $tagihan->status])</div>
        </div>
        <a href="{{ route('tagihan.show', $tagihan) }}"
            class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
            Kembali</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Formulir tagihan</h2>
            </div>
            <form class="p-6" method="post" action="{{ route('tagihan.update', $tagihan) }}">
                @include('tagihan._form', ['baru' => false])
            </form>
        </section>

        <aside class="space-y-4">
            <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-200">Mahasiswa</h2>
                <p class="mt-3 text-lg font-bold">{{ $tagihan->mahasiswa->user->nama }}</p>
                <p class="text-sm text-emerald-100">{{ $tagihan->mahasiswa->nim }}</p>
                <div class="mt-4 border-t border-white/10 pt-4">
                    <p class="text-xs text-emerald-200">Jenis biaya</p>
                    <p class="text-sm font-semibold">{{ $tagihan->jenisBiaya->nama }}</p>
                </div>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider">Hanya draf yang dapat diedit</h2>
                <p class="mt-2">Setelah terbit, tagihan tidak dapat diedit. Periksa nominal dan jatuh tempo sebelum
                    menerbitkan.</p>
            </div>
        </aside>
    </div>
@endsection
