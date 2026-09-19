<div class="card">
    <h2>Pembayaran tagihan</h2>
    @if ($aktif)
        <p>{{ \App\Models\Pembayaran::STATUS[$aktif->status] }} — <a
                href="{{ route('pembayaran.show', $aktif) }}">{{ $aktif->nomor_pengajuan }}</a></p>
    @else<p>Belum ada pengajuan menunggu atau pembayaran diterima untuk tagihan ini.</p>
    @endif
    @if ($bolehAjukan)
        <p><a class="button" href="{{ route('pembayaran.create', $tagihan) }}">Ajukan bukti transfer</a></p>
    @endif
    <ul>
        @foreach ($riwayat as $p)
            <li><a href="{{ route('pembayaran.show', $p) }}">{{ $p->nomor_pengajuan }}</a> —
                {{ \App\Models\Pembayaran::STATUS[$p->status] }}</li>
        @endforeach
    </ul>
    <a href="{{ route('pembayaran.index', ['tagihan' => $tagihan->id]) }}">Seluruh riwayat pengajuan tagihan ini</a>
</div>
