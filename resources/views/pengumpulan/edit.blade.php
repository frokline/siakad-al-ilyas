@extends('layouts.pengumpulan')
@section('title', 'Edit Draf Jawaban')
@section('content')
    <div class="heading">
        <h1>Edit draf versi {{ $pengumpulan->versi }}</h1><a href="{{ route('pengumpulan.show', $pengumpulan) }}">Batal /
            kembali</a>
    </div>
    @include('pengumpulan._jadwal', ['kegiatan' => $pengumpulan->kegiatan])
    <form class="card form-card" method="post" action="{{ route('pengumpulan.update', $pengumpulan) }}">
        @csrf @method('PATCH')
        <input type="hidden" name="versi_form" value="{{ old('versi_form', $pengumpulan->versiForm()) }}">
        <p class="notice">Simpan draf dahulu, periksa hasilnya, lalu kirim dari halaman detail. Tidak ada penyimpanan
            otomatis.</p>
        <label for="jawaban_teks">Teks jawaban</label>
        <textarea id="jawaban_teks" name="jawaban_teks" rows="14"
            maxlength="{{ max(1, min(10000, (int) config('pengumpulan.maks_karakter_jawaban', 10000))) }}">{{ old('jawaban_teks', $pengumpulan->jawaban_teks) }}</textarea>
        <label for="lampiran_ids">ID berkas jawaban</label>
        <input id="lampiran_ids" name="lampiran_ids" maxlength="150" autocomplete="off" placeholder="Contoh: 12, 15"
            value="{{ old('lampiran_ids', $pengumpulan->lampiran->pluck('berkas_id')->implode(', ')) }}"
            aria-describedby="petunjuk-berkas">
        <p id="petunjuk-berkas" class="muted">Unggah melalui <a href="{{ route('berkas.index') }}" target="_blank"
                rel="noopener noreferrer">Berkas saya</a> sampai status Tersedia.
            Salin ID dari URL detail berkas. Pisahkan dengan koma. Masukkan seluruh ID yang ingin dipertahankan; ID yang
            dihilangkan dilepas dari draf ini.</p>
        <p>Minimal teks atau satu berkas saat mengirim. HTML tidak dijalankan. File harus milik akun Anda dan sesuai
            ketentuan kegiatan.</p>
        <button type="submit">Simpan draf dan periksa</button>
    </form>
@endsection
