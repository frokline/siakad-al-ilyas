@extends('layouts.pembayaran')
@section('title', 'Riwayat Pembayaran')
@section('content')
    <div class="heading">
        <h1>Riwayat pengajuan pembayaran</h1><a href="{{ route('tagihan.index') }}">Pilih tagihan untuk mengajukan</a>
    </div>
    <p>Satu pengajuan untuk satu tagihan bulanan. Pengajuan menunggu belum berarti pembayaran diterima.</p>
    <form class="card filters" method="get" action="{{ route('pembayaran.index') }}">
        <label>Nomor pengajuan<input name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"></label>
        @if (!empty($filter['tagihan']))
            <input type="hidden" name="tagihan" value="{{ $filter['tagihan'] }}">
        @endif
        <label>Status<select name="status">
                <option value="">Semua</option>
                @foreach (\App\Models\Pembayaran::STATUS as $kode => $label)
                    <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                @endforeach
            </select></label>
        <button type="submit">Cari</button><a href="{{ route('pembayaran.index') }}">Reset</a>
    </form>
    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Pengajuan</th>
                    <th>Tagihan / mahasiswa</th>
                    <th>Nominal</th>
                    <th>Transfer</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $p)
                    <tr>
                        <td>{{ $p->nomor_pengajuan }}</td>
                        <td>{{ $p->tagihan_snapshot['nomor'] }}<small
                                class="sub">{{ $p->tagihan_snapshot['snapshot']['nama'] ?? '—' }} ·
                                {{ $p->tagihan_snapshot['snapshot']['nim'] ?? '—' }}</small></td>
                        <td>{{ \App\Services\UangTagihan::rupiah($p->nominal_diajukan) }}</td>
                        <td>{{ $p->tanggal_transfer->format('d-m-Y') }}</td>
                        <td>{{ \App\Models\Pembayaran::STATUS[$p->status] }}</td>
                        <td><a href="{{ route('pembayaran.show', $p) }}">Detail</a></td>
                    </tr>
                @empty<tr>
                        <td colspan="6">Belum ada pengajuan yang dapat ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $daftar->links('pembayaran._pagination') }}
@endsection
