@extends('layouts.admin')

@section('title', 'Ajukan Pembayaran')

@section('content')
    @php
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $label = 'mb-1.5 block text-sm font-semibold text-slate-700';
    @endphp

    @include('pembayaran._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('tagihan.index') }}" class="hover:text-siakad-active">Tagihan</a>
                <span>/</span>
                <a href="{{ route('tagihan.show', $tagihan) }}" class="hover:text-siakad-active">{{ $tagihan->nomor }}</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Ajukan pembayaran</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Ajukan bukti transfer</h1>
            <p class="mt-1 text-sm text-slate-500">Bukti yang diunggah belum otomatis melunasi tagihan. Pengajuan akan
                diperiksa Admin Keuangan.</p>
        </div>
        <a href="{{ route('tagihan.show', $tagihan) }}"
            class="inline-flex items-center gap-2 self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
            Kembali ke tagihan</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if ($aktif)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wider">Sudah ada pengajuan aktif</h2>
                    <p class="mt-2">Tagihan ini sudah mempunyai pengajuan yang menunggu atau sudah diterima.</p>
                    <a href="{{ route('pembayaran.show', $aktif) }}"
                        class="mt-4 inline-flex rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700">Buka
                        pengajuan {{ $aktif->nomor_pengajuan }}</a>
                </div>
            @elseif (!$tujuan)
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-800 shadow-sm"
                    role="alert">
                    <h2 class="text-sm font-bold uppercase tracking-wider">Rekening tujuan belum tersedia</h2>
                    <p class="mt-2">Rekening tujuan belum dikonfigurasi. Hubungi admin keuangan.</p>
                </div>
            @else
                {{-- Langkah 1: unggah dan cari berkas --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Siapkan bukti
                            transfer</h2>
                    </div>
                    <div class="space-y-4 p-6">
                        <p class="text-sm text-slate-600">Unggah bukti PDF/JPG/PNG pada
                            <a href="{{ route('berkas.create') }}" target="_blank" rel="noopener noreferrer"
                                class="font-semibold text-siakad-active underline">Berkas Saya</a>. Setelah unggahan
                            berstatus <strong>Tersedia</strong>, muat ulang halaman ini lalu pilih buktinya. Admin yang
                            mengajukan atas nama mahasiswa memakai unggahan akun admin sendiri.
                        </p>
                        <form method="get" action="{{ route('pembayaran.create', $tagihan) }}"
                            class="flex flex-col gap-2 sm:flex-row sm:items-end">
                            <div class="flex-1">
                                <label for="q"
                                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Cari
                                    label berkas</label>
                                <input id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                                    placeholder="Ketik label berkas"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20">
                            </div>
                            <button type="submit"
                                class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Cari
                                / muat ulang berkas</button>
                        </form>
                    </div>
                </section>

                {{-- Langkah 2: formulir pengajuan --}}
                <form method="post" action="{{ route('pembayaran.store', $tagihan) }}"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @csrf
                    <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
                    <input type="hidden" name="versi_tagihan" value="{{ old('versi_tagihan', $tagihan->versiForm()) }}">
                    <input type="hidden" name="versi_tujuan" value="{{ old('versi_tujuan', $versiTujuan) }}">

                    <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Formulir pengajuan
                        </h2>
                    </div>

                    <div class="space-y-6 p-6">
                        @foreach (['form_token', 'versi_tagihan', 'versi_tujuan'] as $kunci)
                            @error($kunci)
                                <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700">{{ $message }}</p>
                            @enderror
                        @endforeach

                        <fieldset>
                            <legend class="mb-3 text-xs font-bold uppercase tracking-wider text-siakad-active">1. Pilih
                                satu bukti milik akun Anda <span class="text-rose-500">*</span></legend>
                            <div class="space-y-2">
                                @forelse($berkas as $b)
                                    <label
                                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-siakad-active hover:bg-emerald-50/40 has-[:checked]:border-siakad-active has-[:checked]:bg-emerald-50">
                                        <input type="radio" name="bukti_berkas_id" value="{{ $b->id }}"
                                            @checked((string) old('bukti_berkas_id') === (string) $b->id) required
                                            class="mt-1 h-4 w-4 border-slate-300 text-siakad-active focus:ring-siakad-active">
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-sm font-semibold text-slate-800">#{{ $b->id }}
                                                &mdash; {{ $b->label }}</span>
                                            <span class="block break-all text-xs text-slate-500">{{ $b->nama_asli }}
                                                &middot; {{ $b->ukuranLabel() }}</span>
                                        </span>
                                        <a href="{{ route('berkas.show', $b) }}" target="_blank" rel="noopener noreferrer"
                                            class="shrink-0 text-xs font-semibold text-siakad-active underline">Periksa</a>
                                    </label>
                                @empty
                                    <div
                                        class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">
                                        Belum ada berkas tersedia. Unggah dahulu melalui Berkas Saya.</div>
                                @endforelse
                            </div>
                            @error('bukti_berkas_id')
                                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <fieldset class="space-y-5 border-t border-slate-100 pt-6">
                            <legend class="mb-1 text-xs font-bold uppercase tracking-wider text-siakad-active">2. Data
                                transfer</legend>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="nominal_diajukan" class="{{ $label }}">Nominal yang ditransfer
                                        <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-500">Rp</span>
                                        <input id="nominal_diajukan" name="nominal_diajukan" inputmode="decimal"
                                            maxlength="15" required class="{{ $input }} pl-11"
                                            value="{{ old('nominal_diajukan', $tagihan->nominal) }}">
                                    </div>
                                    @error('nominal_diajukan')
                                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1.5 text-xs text-slate-500">Isi tanpa pemisah ribuan. Harus sama dengan
                                        nominal penuh tagihan.</p>
                                </div>
                                <div>
                                    <label for="tanggal_transfer" class="{{ $label }}">Tanggal transfer <span
                                            class="text-rose-500">*</span></label>
                                    <input id="tanggal_transfer" type="date" name="tanggal_transfer" min="2000-01-01"
                                        max="{{ now('Asia/Makassar')->format('Y-m-d') }}" required
                                        class="{{ $input }}" value="{{ old('tanggal_transfer') }}">
                                    @error('tanggal_transfer')
                                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="referensi_bank" class="{{ $label }}">Referensi bank <span
                                        class="font-normal text-slate-400">(opsional)</span></label>
                                <input id="referensi_bank" name="referensi_bank" maxlength="100"
                                    class="{{ $input }}" value="{{ old('referensi_bank') }}"
                                    placeholder="Nomor referensi pada struk transfer">
                                @error('referensi_bank')
                                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </fieldset>

                        <div class="border-t border-slate-100 pt-6">
                            <label class="flex cursor-pointer items-start gap-2.5 text-sm text-slate-700">
                                <input type="checkbox" name="konfirmasi" value="1" required
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-siakad-active focus:ring-siakad-active">
                                Saya telah memeriksa tujuan rekening, nominal, tanggal, dan bukti untuk tagihan ini.
                            </label>
                            @error('konfirmasi')
                                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-5 flex flex-wrap items-center gap-3">
                                <button type="submit" @disabled($berkas->isEmpty())
                                    class="rounded-xl bg-siakad-dark px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50">Ajukan
                                    untuk diperiksa</button>
                                <a href="{{ route('tagihan.show', $tagihan) }}"
                                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Batal</a>
                            </div>
                        </div>
                    </div>
                </form>

                @if ($berkas->hasPages())
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {{ $berkas->links('pembayaran._pagination') }}
                    </div>
                @endif
                <p class="text-xs text-slate-500">Jika pindah halaman berkas atau mencari ulang, isian yang belum
                    dikirim perlu diisi kembali. Bukti identik tidak boleh dipakai untuk tagihan lain.</p>
            @endif
        </div>

        {{-- Panel samping --}}
        <aside class="space-y-4">
            <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-200">Nominal penuh</h2>
                <p class="mt-3 break-words text-3xl font-bold">{{ $tagihan->nominalRupiah() }}</p>
                <dl class="mt-5 space-y-3 border-t border-white/10 pt-4 text-sm">
                    <div>
                        <dt class="text-xs text-emerald-200">Tagihan</dt>
                        <dd class="font-semibold">{{ $tagihan->nomor }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-emerald-200">Mahasiswa</dt>
                        <dd class="font-semibold">{{ $tagihan->snapshot['nama'] ?? $tagihan->mahasiswa->user->nama }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-emerald-200">Bulan</dt>
                        <dd class="font-semibold">
                            {{ sprintf('%02d/%04d', $tagihan->bulan_tagihan, $tagihan->tahun_tagihan) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900 shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider">Rekening tujuan</h2>
                <p class="mt-2 break-words text-base font-bold">
                    {{ $tujuan ?? 'Belum dikonfigurasi. Hubungi admin keuangan.' }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Alur pembayaran</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-700">
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-siakad-dark">1</span>Transfer
                        ke rekening tujuan</li>
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-siakad-dark">2</span>Unggah
                        bukti di Berkas Saya</li>
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-siakad-dark">3</span>Ajukan
                        bukti pada halaman ini</li>
                    <li class="flex gap-3"><span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-siakad-dark">4</span>Tunggu
                        pemeriksaan Admin Keuangan</li>
                </ol>
            </div>
        </aside>
    </div>
@endsection
