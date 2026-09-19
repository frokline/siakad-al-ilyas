@extends('layouts.tagihan')
@section('title', 'Detail Tagihan')
@section('content')
    <div class="heading">
        <h1>{{ $tagihan->nomor }}</h1><a href="{{ route('tagihan.index') }}">Daftar tagihan</a>
    </div>
    <div class="card">
        <dl>
            <dt>Mahasiswa</dt>
            <dd>{{ $tagihan->snapshot['nama'] ?? $tagihan->mahasiswa->user->nama }} —
                {{ $tagihan->snapshot['nim'] ?? $tagihan->mahasiswa->nim }}</dd>
            <dt>Jenis biaya</dt>
            <dd>{{ $tagihan->snapshot['jenis_kode'] ?? $tagihan->jenisBiaya->kode }} —
                {{ $tagihan->snapshot['jenis_nama'] ?? $tagihan->jenisBiaya->nama }}</dd>
            <dt>Bulan kewajiban</dt>
            <dd>{{ sprintf('%02d/%04d', $tagihan->bulan_tagihan, $tagihan->tahun_tagihan) }}</dd>
            <dt>Nominal</dt>
            <dd>{{ $tagihan->nominalRupiah() }}</dd>
            <dt>Jatuh tempo</dt>
            <dd>{{ $tagihan->jatuh_tempo->format('d-m-Y') }}</dd>
            <dt>Status</dt>
            <dd>{{ \App\Models\Tagihan::STATUS[$tagihan->status] }} · Revisi {{ $tagihan->revisi }}</dd>
            <dt>Registrasi</dt>
            <dd>#{{ $tagihan->registrasi_semester_id }}</dd>
            <dt>Terbit</dt>
            <dd>{{ $tagihan->diterbitkan_at?->setTimezone('Asia/Makassar')->format('d-m-Y H:i') ?? '—' }}</dd>
            <dt>Catatan</dt>
            <dd>{{ $tagihan->catatan ?? '—' }}</dd>
        </dl>
        <x-status-pembayaran :tagihan="$tagihan" />
    </div>
    @can('update', $tagihan)
        <p><a class="button" href="{{ route('tagihan.edit', $tagihan) }}">Edit draf</a></p>
    @endcan
    @foreach (['terbitkan' => ['terbitkan', 'Terbitkan'], 'batalkan' => ['batalkan', 'Batalkan'], 'buka-draf' => ['bukaDraf', 'Buka kembali sebagai draf']] as $rute => $aksi)
        @can($aksi[0], $tagihan)
            <form class="card form-card" method="post" action="{{ route('tagihan.' . $rute, $tagihan) }}">
                @csrf<input type="hidden" name="versi" value="{{ $tagihan->versiForm() }}">
                <h2>{{ $aksi[1] }}</h2>
                <label>Alasan (internal)
                    <textarea name="alasan" minlength="10" maxlength="1000" required></textarea>
                </label>
                <label><input type="checkbox" name="konfirmasi" value="1" required> Saya sudah memeriksa mahasiswa, bulan,
                    nominal, dan dampak tindakan ini.</label>
                <button type="submit">{{ $aksi[1] }}</button>
            </form>
        @endcan
    @endforeach
    @can('audit', $tagihan)
        <div class="card">
            <h2>Riwayat perubahan internal</h2>
            <p>Pembatalan/koreksi dihentikan jika sudah ada riwayat pembayaran. Buka draf memakai nomor dan record yang sama;
                snapshot terdahulu tetap ada di audit.</p>
            @foreach ($audit as $a)
                <details>
                    <summary>Revisi {{ $a->versi_entitas }} · {{ $a->aksi }} · {{ $a->pelaku?->nama ?? '—' }}</summary>
                    <p>{{ $a->alasan }}</p>
                    <h3>Sebelum</h3>
                    <pre>{{ json_encode($a->sebelum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    <h3>Sesudah</h3>
                    <pre>{{ json_encode($a->sesudah, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            @endforeach
        </div>{{ $audit->links('tagihan._pagination') }}
    @endcan
@endsection
