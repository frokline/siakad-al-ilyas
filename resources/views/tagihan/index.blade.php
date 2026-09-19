@extends('layouts.tagihan')
@section('title', 'Daftar Tagihan')
@section('content')
    <div class="heading">
        <h1>Tagihan bulanan</h1>
        @can('create', \App\Models\Tagihan::class)
            <a class="button" href="{{ route('tagihan.create') }}">Buat draf</a>
        @endcan
    </div>
    <p>Status terbit menunjukkan tagihan sudah diterbitkan. Buka detail untuk mengajukan bukti dan melihat status pengajuan
        pembayaran.</p>
    <form class="card filters" method="get" action="{{ route('tagihan.index') }}">
        <label>Nomor tagihan<input name="q" value="{{ $filter['q'] ?? '' }}" maxlength="100"></label>
        <label>Status<select name="status">
                <option value="">Semua</option>
                @foreach (\App\Models\Tagihan::STATUS as $kode => $label)
                    <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                @endforeach
            </select></label>
        <label>Tahun<input type="number" name="tahun" min="2000" max="2199"
                value="{{ $filter['tahun'] ?? '' }}"></label>
        <label>Bulan<input type="number" name="bulan" min="1" max="12"
                value="{{ $filter['bulan'] ?? '' }}"></label>
        <button type="submit">Tampilkan</button><a href="{{ route('tagihan.index') }}">Reset</a>
    </form>
    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Mahasiswa</th>
                    <th>Biaya / bulan</th>
                    <th>Nominal</th>
                    <th>Jatuh tempo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $t)
                    <tr>
                        <td>{{ $t->nomor }}</td>
                        <td>{{ $t->snapshot['nama'] ?? $t->mahasiswa->user->nama }}<small
                                class="sub">{{ $t->snapshot['nim'] ?? $t->mahasiswa->nim }}</small></td>
                        <td>{{ $t->snapshot['jenis_nama'] ?? $t->jenisBiaya->nama }}<small
                                class="sub">{{ sprintf('%02d/%04d', $t->bulan_tagihan, $t->tahun_tagihan) }}</small>
                        </td>
                        <td>{{ $t->nominalRupiah() }}</td>
                        <td>{{ $t->jatuh_tempo->format('d-m-Y') }}</td>
                        <td>{{ \App\Models\Tagihan::STATUS[$t->status] }}</td>
                        <td><a href="{{ route('tagihan.show', $t) }}">Detail</a></td>
                    </tr>
                @empty<tr>
                        <td colspan="7">Belum ada tagihan yang dapat ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $daftar->links('tagihan._pagination') }}
@endsection
