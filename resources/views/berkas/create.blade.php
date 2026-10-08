@extends('layouts.admin')

@section('title', 'Unggah Berkas')

@section('content')
    @include('berkas._pesan')

    {{-- Kepala halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('berkas.index') }}" class="hover:text-siakad-active">Berkas</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Unggah baru</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Unggah berkas</h1>
            <p class="mt-1 text-sm text-slate-500">Berkas yang diunggah masuk ke penyimpanan pribadi akun Anda.</p>
        </div>

        <a href="{{ route('berkas.index') }}"
            class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Formulir --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Detail berkas</h2>
            </div>
            <div class="p-6">
                @include('berkas._form')
            </div>
        </section>

        {{-- Panel panduan --}}
        <aside class="space-y-4">
            <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-200">Ketentuan berkas</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    <li class="flex gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-siakad-accent" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Format yang diterima: <strong>PDF, JPG, PNG</strong></span>
                    </li>
                    <li class="flex gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-siakad-accent" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Ukuran maksimal <strong>20 MB</strong> per berkas</span>
                    </li>
                    <li class="flex gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-siakad-accent" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Berkas bersifat pribadi dan hanya dapat diakses sesuai hak akses akun</span>
                    </li>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Tips</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Beri label yang jelas, misalnya <em>Bukti transfer SPP Oktober</em>.</li>
                    <li>Pastikan tulisan pada foto atau pindaian terbaca.</li>
                    <li>Isi berkas tidak dapat diganti setelah diunggah. Untuk versi baru, unggah sebagai berkas baru.</li>
                </ul>
            </div>
        </aside>
    </div>
@endsection
