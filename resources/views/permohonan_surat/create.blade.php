@extends('layouts.admin')

@section('title', 'Ajukan Surat')

@section('content')
    @php
        $kartu = 'rounded-2xl border border-slate-200 bg-white shadow-sm';
        $kepalaKartu = 'border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4';
        $judulKartu = 'text-sm font-bold uppercase tracking-wider text-siakad-dark';
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $label = 'mb-1.5 block text-sm font-semibold text-slate-700';
    @endphp

    @include('permohonan_surat._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs text-slate-500">Layanan &rsaquo; Permohonan surat &rsaquo; Ajukan</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Ajukan surat aktif kuliah</h1>
            <p class="mt-1 text-sm text-slate-500">Isi tujuan penggunaan surat, lalu kirim untuk diproses petugas akademik.
            </p>
        </div>
        <a href="{{ route('surat.index') }}"
            class="inline-flex items-center justify-center self-start rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">&larr;
            Kembali</a>
    </div>

    @if (!$jenis || $registrasi->isEmpty())
        <div class="rounded-2xl border border-dashed border-amber-300 bg-amber-50 p-8 text-center">
            <p class="text-sm font-bold text-amber-800">Layanan atau registrasi aktif belum tersedia.</p>
            <p class="mt-1 text-xs text-amber-700">Hubungi bagian akademik.</p>
        </div>
    @else
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            {{-- Kolom utama --}}
            <div class="space-y-6 lg:col-span-2">

                <section class="{{ $kartu }}">
                    <div class="{{ $kepalaKartu }}">
                        <h2 class="{{ $judulKartu }}">Persyaratan &mdash; {{ $jenis->nama }}</h2>
                    </div>
                    <div class="whitespace-pre-line break-words p-6 text-sm leading-relaxed text-slate-700">
                        {{ $jenis->syarat ?? 'Belum ada persyaratan tambahan yang dicantumkan.' }}</div>
                </section>

                {{-- Pencarian lampiran: formulir GET terpisah (tidak boleh bersarang dalam formulir utama) --}}
                <form method="get" action="{{ route('surat.create') }}" class="{{ $kartu }} p-6">
                    <h2 class="{{ $judulKartu }} mb-1">Langkah 1 &middot; Siapkan lampiran (opsional)</h2>
                    <p class="mb-4 text-xs text-slate-500">Lakukan pencarian atau unggahan <strong>sebelum</strong> mengisi
                        formulir. Perubahan halaman tidak menyimpan isian.</p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <label for="q_berkas" class="{{ $label }}">Cari lampiran menurut label</label>
                            <input id="q_berkas" name="q_berkas" maxlength="100" value="{{ $filter['q_berkas'] ?? '' }}"
                                class="{{ $input }}">
                        </div>
                        <button type="submit"
                            class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-700">Cari</button>
                        <a target="_blank" rel="noopener noreferrer" href="{{ route('berkas.create') }}"
                            class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50">Unggah
                            berkas baru</a>
                    </div>
                </form>

                <form method="post" action="{{ route('surat.store') }}" class="{{ $kartu }} p-6"
                    x-data="{ jumlah: {{ mb_strlen((string) old('keperluan')) }} }">
                    @csrf
                    <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
                    <input type="hidden" name="jenis_surat_id" value="{{ $jenis->id }}">
                    <input type="hidden" name="versi_jenis" value="{{ old('versi_jenis', $jenis->versiForm()) }}">

                    <h2 class="{{ $judulKartu }} mb-5">Langkah 2 &middot; Isi formulir</h2>

                    <div class="space-y-5">
                        <div>
                            <label for="registrasi" class="{{ $label }}">Registrasi semester <span
                                    class="text-rose-500">*</span></label>
                            <select id="registrasi" name="registrasi_semester_id" required class="{{ $input }}">
                                <option value="">Pilih registrasi</option>
                                @foreach ($registrasi as $r)
                                    <option value="{{ $r->id }}" @selected((string) old('registrasi_semester_id') === (string) $r->id)>
                                        {{ $r->nim }} &middot; Periode #{{ $r->periode_akademik_id }} &middot;
                                        Semester studi {{ $r->semester_studi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('registrasi_semester_id')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="keperluan" class="{{ $label }}">Tujuan penggunaan surat <span
                                    class="text-rose-500">*</span></label>
                            <textarea id="keperluan" name="keperluan" minlength="10" maxlength="2000" rows="4" required
                                @input="jumlah = $event.target.value.length" class="{{ $input }}">{{ old('keperluan') }}</textarea>
                            <p class="mt-1 flex justify-between text-xs text-slate-500">
                                <span>Minimal 10 karakter.</span>
                                <span><span x-text="jumlah">0</span> / 2.000</span>
                            </p>
                            @error('keperluan')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <fieldset>
                            <legend class="{{ $label }}">Lampiran pendukung (opsional)</legend>
                            <label
                                class="mb-3 flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <input type="radio" name="lampiran_berkas_id" value="" @checked(!old('lampiran_berkas_id'))
                                    class="h-4 w-4 border-slate-300 text-siakad-dark focus:ring-siakad-dark">
                                Tanpa lampiran
                            </label>
                            @include('permohonan_surat._berkas', ['field' => 'lampiran_berkas_id'])
                            @error('lampiran_berkas_id')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            <input type="checkbox" name="konfirmasi" value="1" required
                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-siakad-dark focus:ring-siakad-dark">
                            <span>Saya telah memeriksa tujuan dan lampiran. Pengajuan tidak dapat diedit setelah
                                dikirim.</span>
                        </label>
                    </div>

                    <div class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-6">
                        <button type="submit"
                            class="rounded-xl bg-siakad-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">Kirim
                            permohonan</button>
                        <a href="{{ route('surat.index') }}"
                            class="text-sm font-semibold text-slate-600 hover:text-siakad-dark hover:underline">Batal</a>
                    </div>
                </form>
            </div>

            {{-- Panel samping --}}
            <aside class="space-y-6">
                <section class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-200">Alur pengajuan</h2>
                    <ol class="mt-4 space-y-4 text-sm">
                        <li class="flex gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15 text-xs font-bold ring-1 ring-white/30">1</span>
                            <span><strong>Diajukan</strong><br><span class="text-xs text-emerald-100">Anda mengirim
                                    permohonan. Selama masih berstatus ini, Anda dapat membatalkannya.</span></span>
                        </li>
                        <li class="flex gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15 text-xs font-bold ring-1 ring-white/30">2</span>
                            <span><strong>Diproses</strong><br><span class="text-xs text-emerald-100">Petugas akademik
                                    memeriksa permohonan Anda.</span></span>
                        </li>
                        <li class="flex gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15 text-xs font-bold ring-1 ring-white/30">3</span>
                            <span><strong>Terbit</strong><br><span class="text-xs text-emerald-100">Surat final dapat
                                    diunduh dari halaman detail permohonan.</span></span>
                        </li>
                    </ol>
                </section>

                <section class="{{ $kartu }} p-6 text-xs text-slate-600">
                    <h2 class="mb-2 text-sm font-bold text-slate-800">Perlu diperhatikan</h2>
                    <ul class="list-inside list-disc space-y-1.5">
                        <li>Lampiran diambil dari <strong>Berkas saya</strong> (PDF, JPG, atau PNG).</li>
                        <li>Catatan petugas akan tampil di riwayat proses.</li>
                        <li>Pantau status pada menu <strong>Permohonan surat</strong>.</li>
                    </ul>
                </section>
            </aside>
        </div>
    @endif
@endsection
