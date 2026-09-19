@extends('layouts.permohonan_surat')
@section('title', $p->nomor_pengajuan)
@section('content')
    <div class="heading">
        <div><span class="badge">{{ \App\Models\PermohonanSurat::STATUS[$p->status] }}</span>
            <h1>{{ $p->nomor_pengajuan }}</h1>
        </div><a href="{{ route('surat.index') }}">Kembali ke daftar</a>
    </div>
    <section class="card">
        <h2>{{ $p->jenis_snapshot['nama'] }}</h2>
        <dl class="metadata">
            <div>
                <dt>Mahasiswa</dt>
                <dd>{{ $p->akademik_snapshot['nim'] }} — {{ $p->akademik_snapshot['nama'] }}</dd>
            </div>
            <div>
                <dt>Periode / semester studi</dt>
                <dd>#{{ $p->akademik_snapshot['periode_akademik_id'] }} / {{ $p->akademik_snapshot['semester_studi'] }}</dd>
            </div>
            <div>
                <dt>Diajukan</dt>
                <dd>{{ $p->diajukan_at->setTimezone('Asia/Makassar')->format('d-m-Y H:i') }} WITA</dd>
            </div>
        </dl>
        <h3>Keperluan</h3>
        <div class="prose">{{ $p->keperluan }}</div>
        <h3>Persyaratan saat diajukan</h3>
        <div class="prose">{{ $p->jenis_snapshot['syarat'] ?? 'Tidak ada persyaratan tambahan.' }}</div>
        @can('download', [$p, 'lampiran'])
            <p><a href="{{ route('surat.tautan', [$p, 'lampiran']) }}">Unduh lampiran pendukung</a></p>
        @endcan
        @if ($p->nomor_surat)
            <p><strong>Nomor surat:</strong> {{ $p->nomor_surat }}</p>
            <p>Diterbitkan {{ $p->terbit_at->setTimezone('Asia/Makassar')->format('d-m-Y H:i') }} WITA.</p>
        @endif
        @can('download', [$p, 'hasil'])
            <a class="button" href="{{ route('surat.tautan', [$p, 'hasil']) }}">Unduh surat final</a>
        @endcan
        @if ($p->status === 'dibatalkan' && $p->terbit_at)
            <p class="notice error">Surat ini telah dibatalkan. Jangan gunakan salinan yang pernah diunduh. Hubungi bagian
                akademik untuk penggantian.</p>
        @endif
    </section>
    @if ($pilihan)
        @if ($berkas)
            <form method="get" action="{{ route('surat.show', $p) }}" class="card filters"><label for="q_berkas">Cari PDF
                    final menurut label</label><input id="q_berkas" name="q_berkas" maxlength="100"
                    value="{{ $filter['q_berkas'] ?? '' }}"><button type="submit">Cari</button>
                <a href="{{ route('berkas.create') }}" target="_blank" rel="noopener noreferrer">Unggah PDF final</a>
            </form>
            <p class="muted">Siapkan PDF yang sudah diperiksa dan ditandatangani sesuai prosedur kampus. Pilih berkas
                sebelum mengisi keputusan.</p>
        @endif
        <form method="post" action="{{ route('surat.tindakan', $p) }}" class="card form-card">@csrf
            <input type="hidden" name="versi" value="{{ old('versi', $p->versiForm()) }}">
            <label for="tujuan">Tindakan *</label><select id="tujuan" name="tujuan" required>
                <option value="">Pilih tindakan</option>
                @foreach ($pilihan as $tujuan)
                    <option value="{{ $tujuan }}" @selected(old('tujuan') === $tujuan)>
                        {{ $tujuan === 'terbit' ? 'Terbitkan surat' : \App\Models\PermohonanSurat::STATUS[$tujuan] }}
                    </option>
                @endforeach
            </select>
            @if ($berkas)
                <h2>Khusus penerbitan</h2><label for="nomor_surat">Nomor surat resmi</label><input id="nomor_surat"
                    name="nomor_surat" maxlength="100" value="{{ old('nomor_surat') }}" autocomplete="off">
                <p class="muted">Gunakan nomor resmi dari pencatatan kampus. Huruf, angka, titik, garis miring, tanda
                    minus, dan garis bawah; tanpa spasi. Nomor tidak dibuat otomatis.</p>
                @include('permohonan_surat._berkas', ['field' => 'hasil_berkas_id'])
            @endif
            <label for="catatan">Catatan / alasan *</label>
            <textarea id="catatan" name="catatan" minlength="{{ $p->status === 'terbit' ? 20 : 10 }}" maxlength="1000"
                rows="4" required>{{ old('catatan') }}</textarea>
            <p class="muted">Catatan terlihat oleh mahasiswa. Jangan menuliskan informasi internal yang tidak boleh
                dibagikan.</p>
            <label class="check"><input type="checkbox" name="konfirmasi" value="1" required> Saya sudah memeriksa
                tindakan dan dokumen yang dipilih.</label>
            <button type="submit">Simpan tindakan</button>
        </form>
    @endif
    <section class="card">
        <h2>Riwayat proses</h2>
        @foreach ($p->riwayat as $r)
            <article class="status-box">
                <h3>{{ \App\Models\PermohonanSurat::STATUS[$r->status_baru] }}</h3>
                <p>{{ $r->waktu->setTimezone('Asia/Makassar')->format('d-m-Y H:i') }} WITA ·
                    {{ $r->pelaku?->nama ?? 'Petugas' }}</p>
                <div class="prose">{{ $r->catatan }}</div>
            </article>
        @endforeach
    </section>
@endsection
