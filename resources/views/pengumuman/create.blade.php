@extends('layouts.admin')

@section('title', 'Buat Pengumuman')

@section('content')
    @include('pengumuman._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('pengumuman.index', ['mode' => 'kelola']) }}"
                    class="hover:text-siakad-active">Pengumuman</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Buat baru</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Buat pengumuman</h1>
            <p class="mt-1 text-sm text-slate-500">Pengumuman baru tersimpan sebagai draf dan baru terlihat pembaca
                setelah diterbitkan.</p>
        </div>
        <a href="{{ route('pengumuman.index', ['mode' => 'kelola']) }}"
            class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
            Kembali</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Formulir pengumuman</h2>
            </div>
            <form class="p-6" method="post" action="{{ route('pengumuman.store') }}">
                @include('pengumuman._form')
            </form>
        </section>

        <aside class="space-y-4">
            @include('pengumuman._search')
            <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-200">Alur pengumuman</h2>
                <ol class="mt-4 space-y-3 text-sm">
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-siakad-accent text-xs font-bold text-siakad-dark">1</span>Simpan
                        sebagai <strong>draf</strong></li>
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-siakad-accent text-xs font-bold text-siakad-dark">2</span>Periksa
                        isi dan sasaran</li>
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-siakad-accent text-xs font-bold text-siakad-dark">3</span><strong>Terbitkan</strong>.
                        Isi dan sasaran terkunci</li>
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-siakad-accent text-xs font-bold text-siakad-dark">4</span><strong>Arsipkan</strong>
                        bila sudah tidak relevan</li>
                </ol>
            </div>
        </aside>
    </div>
@endsection
