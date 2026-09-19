@extends('layouts.permohonan_surat')
@section('title', 'Ajukan Surat')
@section('content')
    <div class="heading">
        <h1>Ajukan surat aktif kuliah</h1><a href="{{ route('surat.index') }}">Kembali</a>
    </div>
    @if (!$jenis || $registrasi->isEmpty())
        <div class="notice">Layanan atau registrasi aktif belum tersedia. Hubungi bagian akademik.</div>
    @else
        <section class="card">
            <h2>{{ $jenis->nama }}</h2>
            <h3>Persyaratan</h3>
            <div class="prose">{{ $jenis->syarat ?? 'Belum ada persyaratan tambahan yang dicantumkan.' }}</div>
        </section>
        <form method="get" action="{{ route('surat.create') }}" class="card filters"><label for="q_berkas">Cari lampiran
                menurut label</label>
            <input id="q_berkas" name="q_berkas" maxlength="100" value="{{ $filter['q_berkas'] ?? '' }}"><button
                type="submit">Cari</button>
            <a target="_blank" rel="noopener noreferrer" href="{{ route('berkas.create') }}">Unggah berkas baru</a>
        </form>
        <p class="muted">Lakukan pencarian/unggahan sebelum mengisi formulir. Perubahan halaman tidak menyimpan isian.</p>
        <form method="post" action="{{ route('surat.store') }}" class="card form-card">@csrf
            <input type="hidden" name="form_token" value="{{ old('form_token', $token) }}">
            <input type="hidden" name="jenis_surat_id" value="{{ $jenis->id }}"><input type="hidden" name="versi_jenis"
                value="{{ old('versi_jenis', $jenis->versiForm()) }}">
            <label for="registrasi">Registrasi semester *</label><select id="registrasi" name="registrasi_semester_id"
                required>
                <option value="">Pilih registrasi</option>
                @foreach ($registrasi as $r)
                    <option value="{{ $r->id }}" @selected((string) old('registrasi_semester_id') === (string) $r->id)>{{ $r->nim }} · Periode
                        #{{ $r->periode_akademik_id }} · Semester studi {{ $r->semester_studi }}</option>
                @endforeach
            </select>
            <label for="keperluan">Tujuan penggunaan surat *</label>
            <textarea id="keperluan" name="keperluan" minlength="10" maxlength="2000" rows="4" required>{{ old('keperluan') }}</textarea>
            <h2>Lampiran pendukung (opsional)</h2><label><input type="radio" name="lampiran_berkas_id" value=""
                    @checked(!old('lampiran_berkas_id'))> Tanpa lampiran</label>
            @include('permohonan_surat._berkas', ['field' => 'lampiran_berkas_id'])
            <label class="check"><input type="checkbox" name="konfirmasi" value="1" required> Saya telah memeriksa
                tujuan dan lampiran. Pengajuan tidak dapat diedit setelah dikirim.</label>
            <button type="submit">Kirim permohonan</button>
        </form>
    @endif
@endsection
