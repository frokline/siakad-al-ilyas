@extends('layouts.pembayaran')
@section('title', 'Detail Pengajuan Pembayaran')
@section('content')
    <div class="heading">
        <h1>{{ $pembayaran->nomor_pengajuan }}</h1><a href="{{ route('pembayaran.index') }}">Riwayat pembayaran</a>
    </div>
    <div class="card">
        <dl>
            <dt>Status</dt>
            <dd>{{ \App\Models\Pembayaran::STATUS[$pembayaran->status] }}</dd>
            <dt>Tagihan</dt>
            <dd><a
                    href="{{ route('tagihan.show', $pembayaran->tagihan_id) }}">{{ $pembayaran->tagihan_snapshot['nomor'] }}</a>
            </dd>
            <dt>Mahasiswa</dt>
            <dd>{{ $pembayaran->tagihan_snapshot['snapshot']['nama'] ?? '—' }} ·
                {{ $pembayaran->tagihan_snapshot['snapshot']['nim'] ?? '—' }}</dd>
            <dt>Nominal diajukan</dt>
            <dd>{{ \App\Services\UangTagihan::rupiah($pembayaran->nominal_diajukan) }}</dd>
            <dt>Tanggal transfer</dt>
            <dd>{{ $pembayaran->tanggal_transfer->format('d-m-Y') }}</dd>
            <dt>Tujuan saat pengajuan</dt>
            <dd>{{ $pembayaran->tujuan_transfer }}</dd>
            <dt>Referensi bank</dt>
            <dd>{{ $pembayaran->referensi_bank ?? '—' }}</dd>
            <dt>Diajukan oleh</dt>
            <dd>{{ $pembayaran->pengunggah->nama }}</dd>
            <dt>Waktu pengajuan</dt>
            <dd>{{ $pembayaran->diajukan_at->setTimezone('Asia/Makassar')->format('d-m-Y H:i') }}</dd>
            <dt>Bukti</dt>
            <dd>{{ $pembayaran->bukti_snapshot['nama_asli'] }} · <a
                    href="{{ route('pembayaran.tautan', $pembayaran) }}">Unduh bukti privat</a></dd>
            @if ($pembayaran->alasan_batal)
                <dt>Alasan pembatalan</dt>
                <dd>{{ $pembayaran->alasan_batal }}</dd>
            @endif
        </dl>
        <p>Penerimaan/penolakan oleh admin tersedia setelah modul verifikasi dipasang. Status menunggu tidak otomatis
            melunasi tagihan.</p>
    </div>
    @can('cancel', $pembayaran)
        <form class="card form-card" method="post" action="{{ route('pembayaran.batalkan', $pembayaran) }}">@csrf
            <input type="hidden" name="versi" value="{{ $pembayaran->versiForm() }}">
            <h2>Batalkan pengajuan</h2>
            <p>Histori dan bukti tetap disimpan. Tindakan ini tidak mengembalikan uang di rekening bank.</p>
            <label>Alasan
                <textarea name="alasan" minlength="10" maxlength="1000" required>{{ old('alasan') }}</textarea>
            </label>
            <label><input type="checkbox" name="konfirmasi" value="1" required> Saya memahami dampak pembatalan
                pengajuan.</label><button type="submit">Batalkan pengajuan</button>
        </form>
    @endcan
    @can('audit', $pembayaran)
        <div class="card">
            <h2>Audit internal</h2>
            @foreach ($audit as $a)
                <details>
                    <summary>Revisi {{ $a->versi_entitas }} · {{ $a->aksi }} · {{ $a->pelaku?->nama ?? '—' }}</summary>
                    <p>{{ $a->alasan }}</p>
                    <pre>{{ json_encode(['sebelum' => $a->sebelum, 'sesudah' => $a->sesudah], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            @endforeach
        </div>{{ $audit->links('pembayaran._pagination') }}
    @endcan
@endsection
