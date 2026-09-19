@extends('layouts.pembayaran')
@section('title', 'Ajukan Pembayaran')
@section('content')
    <div class="heading">
        <h1>Ajukan bukti transfer</h1><a href="{{ route('tagihan.show', $tagihan) }}">Kembali ke tagihan</a>
    </div>
    <div class="card">
        <h2>{{ $tagihan->nomor }}</h2>
        <p>{{ $tagihan->snapshot['nama'] ?? $tagihan->mahasiswa->user->nama }} ·
            {{ sprintf('%02d/%04d', $tagihan->bulan_tagihan, $tagihan->tahun_tagihan) }}</p>
        <p>Nominal penuh: <strong>{{ $tagihan->nominalRupiah() }}</strong></p>
        <p>Rekening tujuan: <strong>{{ $tujuan ?? 'Belum dikonfigurasi. Hubungi admin keuangan.' }}</strong></p>
    </div>
    @if ($aktif)
        <div class="card">
            <p>Tagihan ini sudah mempunyai pengajuan aktif.</p><a href="{{ route('pembayaran.show', $aktif) }}">Buka
                pengajuan {{ $aktif->nomor_pengajuan }}</a>
        </div>
    @elseif($tujuan)
        <p>Unggah bukti PDF/JPG/PNG pada <a href="{{ route('berkas.create') }}" target="_blank"
                rel="noopener noreferrer">Berkas Saya</a>. Setelah unggahan berstatus Tersedia, muat ulang halaman ini lalu
            pilih buktinya. Admin yang mengajukan atas nama mahasiswa memakai unggahan akun admin sendiri.</p>
        <form class="card filters" method="get" action="{{ route('pembayaran.create', $tagihan) }}"><label>Cari label
                berkas<input name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"></label><button
                type="submit">Cari / muat ulang berkas</button></form>
        <form class="card form-card" method="post" action="{{ route('pembayaran.store', $tagihan) }}">
            @csrf<input type="hidden" name="form_token" value="{{ old('form_token', $token) }}"><input type="hidden"
                name="versi_tagihan" value="{{ old('versi_tagihan', $tagihan->versiForm()) }}"><input type="hidden"
                name="versi_tujuan" value="{{ old('versi_tujuan', $versiTujuan) }}">
            <fieldset>
                <legend>Pilih satu bukti milik akun Anda</legend>
                @forelse($berkas as $b)
                    <label><input type="radio" name="bukti_berkas_id" value="{{ $b->id }}"
                            @checked((string) old('bukti_berkas_id') === (string) $b->id) required> #{{ $b->id }} — {{ $b->label }} ·
                        {{ $b->nama_asli }} · {{ $b->ukuranLabel() }} <a href="{{ route('berkas.show', $b) }}"
                            target="_blank" rel="noopener noreferrer">Periksa</a></label>
                @empty<p>Belum ada berkas tersedia. Unggah dahulu melalui Berkas Saya.</p>
                @endforelse
            </fieldset>
            <label>Nominal yang ditransfer<input name="nominal_diajukan" inputmode="decimal" maxlength="15"
                    value="{{ old('nominal_diajukan', $tagihan->nominal) }}" required></label><small>Isi tanpa pemisah
                ribuan. Harus sama dengan nominal penuh tagihan.</small>
            <label>Tanggal transfer<input type="date" name="tanggal_transfer" min="2000-01-01"
                    max="{{ now('Asia/Makassar')->format('Y-m-d') }}" value="{{ old('tanggal_transfer') }}"
                    required></label>
            <label>Referensi bank (opsional)<input name="referensi_bank" maxlength="100"
                    value="{{ old('referensi_bank') }}"></label>
            <label><input type="checkbox" name="konfirmasi" value="1" required> Saya telah memeriksa tujuan rekening,
                nominal, tanggal, dan bukti untuk tagihan ini.</label>
            <button type="submit" @disabled($berkas->isEmpty())>Ajukan untuk diperiksa</button>
        </form>{{ $berkas->links('pembayaran._pagination') }}
        <p>Jika pindah halaman berkas atau mencari ulang, isian yang belum dikirim perlu diisi kembali. Bukti identik tidak
            boleh dipakai untuk tagihan lain.</p>
    @endif
@endsection
