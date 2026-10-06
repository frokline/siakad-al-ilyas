@extends('layouts.portal')

@section('title', 'Bagikan Pembelajaran')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-siakad-active">Pembelajaran kelas</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Bagikan pembelajaran</h1>
            <p class="mt-1 text-sm text-slate-500">Materi, tugas, latihan, UTS, dan UAS dikelola dari satu halaman.</p>
        </div>
        <a href="{{ route('kegiatan.kelas') }}"
            class="inline-flex items-center gap-2 self-start rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">
            &larr; Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
        <form method="post" action="{{ route('kegiatan.store') }}" enctype="multipart/form-data"
            class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            @csrf

            <input type="hidden" name="kelas_kuliah_id" value="{{ $kelas->id }}">
            <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">

            @include('kegiatan._form')
        </form>

        <aside class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 text-sm font-bold text-slate-800">Panduan singkat</h2>
                <ul class="list-inside list-disc space-y-2 text-xs text-slate-600">
                    <li><strong>Materi</strong> hanya dibaca dan diunduh, tanpa batas waktu.</li>
                    <li><strong>Tugas, latihan, UTS, UAS</strong> memerlukan waktu mulai, batas pengumpulan, dan aturan
                        berkas.</li>
                    <li>Waktu diisi menurut zona <strong>{{ $zona }}</strong>.</li>
                    <li>Mahasiswa menerima notifikasi setiap pembelajaran dibagikan atau diubah.</li>
                </ul>
            </div>
        </aside>
    </div>
@endsection
