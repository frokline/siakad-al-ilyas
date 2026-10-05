@extends('layouts.pembayaran')

@section('title', 'Detail Pengajuan Pembayaran')

@section('content')
    <div class="heading">
        <h1>{{ $pembayaran->nomor_pengajuan }}</h1>

        <a href="{{ route('pembayaran.index') }}">
            Riwayat pembayaran
        </a>
    </div>

    <div class="card">
        <h2>Detail pembayaran</h2>

        <dl>
            <dt>Status</dt>
            <dd>
                {{ \App\Models\Pembayaran::STATUS[$pembayaran->status] ?? $pembayaran->status }}
            </dd>

            <dt>Tagihan</dt>
            <dd>
                <a href="{{ route('tagihan.show', $pembayaran->tagihan_id) }}">
                    {{ $pembayaran->tagihan_snapshot['nomor'] ?? '—' }}
                </a>
            </dd>

            <dt>Mahasiswa</dt>
            <dd>
                {{ $pembayaran->tagihan_snapshot['snapshot']['nama'] ?? '—' }}
                ·
                {{ $pembayaran->tagihan_snapshot['snapshot']['nim'] ?? '—' }}
            </dd>

            <dt>Nominal diajukan</dt>
            <dd>
                {{ \App\Services\UangTagihan::rupiah($pembayaran->nominal_diajukan) }}
            </dd>

            <dt>Tanggal transfer</dt>
            <dd>
                {{ $pembayaran->tanggal_transfer->format('d-m-Y') }}
            </dd>

            <dt>Tujuan saat pengajuan</dt>
            <dd>{{ $pembayaran->tujuan_transfer }}</dd>

            <dt>Referensi bank</dt>
            <dd>{{ $pembayaran->referensi_bank ?? '—' }}</dd>

            <dt>Diajukan oleh</dt>
            <dd>{{ $pembayaran->pengunggah->nama }}</dd>

            <dt>Waktu pengajuan</dt>
            <dd>
                {{ $pembayaran->diajukan_at
                    ->setTimezone('Asia/Makassar')
                    ->format('d-m-Y H:i') }}
            </dd>

            <dt>Bukti pembayaran</dt>
            <dd>
                {{ $pembayaran->bukti_snapshot['nama_asli'] ?? 'Bukti pembayaran' }}
                ·
                <a href="{{ route('pembayaran.tautan', $pembayaran) }}">
                    Unduh bukti privat
                </a>
            </dd>

            @if ($pembayaran->alasan_batal)
                <dt>Alasan pembatalan</dt>
                <dd>{{ $pembayaran->alasan_batal }}</dd>
            @endif
        </dl>

        @if ($pembayaran->status === \App\Models\Pembayaran::MENUNGGU)
            <p>
                Pembayaran masih menunggu pemeriksaan Admin Keuangan.
            </p>
        @elseif ($pembayaran->status === \App\Models\Pembayaran::DITERIMA)
            <p>
                Bukti pembayaran telah diterima oleh Admin Keuangan.
            </p>
        @elseif ($pembayaran->status === \App\Models\Pembayaran::DITOLAK)
            <p>
                Bukti pembayaran ditolak. Mahasiswa dapat mengajukan bukti baru.
            </p>
        @endif
    </div>

    @if ($bolehVerifikasi)
        <div class="card">
            <h2>Verifikasi Admin Keuangan</h2>

            <p>
                Periksa nominal, tanggal transfer, tujuan rekening, serta berkas
                bukti sebelum mengambil keputusan.
            </p>

            <form
                method="post"
                action="{{ route('pembayaran.terima', $pembayaran) }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="versi"
                    value="{{ $pembayaran->versiForm() }}"
                >

                <h3>Terima pembayaran</h3>

                <label for="catatan-terima">
                    Catatan penerimaan
                </label>

                <textarea
                    id="catatan-terima"
                    name="catatan"
                    minlength="10"
                    maxlength="2000"
                    required
                >{{ old('catatan') }}</textarea>

                <label>
                    <input
                        type="checkbox"
                        name="konfirmasi"
                        value="1"
                        required
                    >
                    Saya sudah memeriksa bukti dan menyatakan pembayaran ini valid.
                </label>

                <button type="submit">
                    Terima pembayaran
                </button>
            </form>

            <hr>

            <form
                method="post"
                action="{{ route('pembayaran.tolak', $pembayaran) }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="versi"
                    value="{{ $pembayaran->versiForm() }}"
                >

                <h3>Tolak pembayaran</h3>

                <label for="catatan-tolak">
                    Alasan penolakan
                </label>

                <textarea
                    id="catatan-tolak"
                    name="catatan"
                    minlength="10"
                    maxlength="2000"
                    required
                >{{ old('catatan') }}</textarea>

                <label>
                    <input
                        type="checkbox"
                        name="konfirmasi"
                        value="1"
                        required
                    >
                    Saya memahami bahwa penolakan membuka kesempatan pengajuan baru.
                </label>

                <button type="submit">
                    Tolak pembayaran
                </button>
            </form>
        </div>
    @endif

    @can('cancel', $pembayaran)
        <form
            class="card form-card"
            method="post"
            action="{{ route('pembayaran.batalkan', $pembayaran) }}"
        >
            @csrf

            <input
                type="hidden"
                name="versi"
                value="{{ $pembayaran->versiForm() }}"
            >

            <h2>Batalkan pengajuan</h2>

            <p>
                Histori dan bukti tetap disimpan. Tindakan ini tidak
                mengembalikan uang di rekening bank.
            </p>

            <label for="alasan-pembatalan">
                Alasan pembatalan
            </label>

            <textarea
                id="alasan-pembatalan"
                name="alasan"
                minlength="10"
                maxlength="1000"
                required
            >{{ old('alasan') }}</textarea>

            <label>
                <input
                    type="checkbox"
                    name="konfirmasi"
                    value="1"
                    required
                >
                Saya memahami dampak pembatalan pengajuan.
            </label>

            <button type="submit">
                Batalkan pengajuan
            </button>
        </form>
    @endcan

    @can('audit', $pembayaran)
        <div class="card">
            <h2>Riwayat verifikasi</h2>

            @forelse ($riwayatVerifikasi as $verifikasi)
                <details>
                    <summary>
                        Revisi {{ $verifikasi->revisi_pembayaran }}
                        ·
                        {{ \App\Models\VerifikasiPembayaran::TINDAKAN[$verifikasi->tindakan]
                            ?? $verifikasi->tindakan }}
                        ·
                        {{ $verifikasi->petugas?->nama ?? '—' }}
                    </summary>

                    <p>{{ $verifikasi->catatan }}</p>

                    <p>
                        {{ $verifikasi->waktu
                            ->setTimezone('Asia/Makassar')
                            ->format('d-m-Y H:i') }}
                    </p>
                </details>
            @empty
                <p>Belum ada riwayat verifikasi.</p>
            @endforelse
        </div>

        <div class="card">
            <h2>Audit internal</h2>

            @forelse ($audit as $item)
                <details>
                    <summary>
                        Revisi {{ $item->versi_entitas }}
                        ·
                        {{ $item->aksi }}
                        ·
                        {{ $item->pelaku?->nama ?? '—' }}
                    </summary>

                    <p>{{ $item->alasan }}</p>

                    <pre>{{ json_encode(
                        [
                            'sebelum' => $item->sebelum,
                            'sesudah' => $item->sesudah,
                        ],
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                    ) }}</pre>
                </details>
            @empty
                <p>Belum ada riwayat audit.</p>
            @endforelse
        </div>

        {{ $audit->links('pembayaran._pagination') }}
    @endcan
@endsection