@extends('layouts.keuangan')

@section('title', 'Dashboard Keuangan')

@section('content')
    <div class="heading">
        <div>
            <p>Administrasi keuangan</p>

            <h1>Dashboard Admin Keuangan</h1>

            <p>
                Selamat datang, {{ $user->nama }}.
                Berikut ringkasan tagihan dan pembayaran mahasiswa.
            </p>
        </div>
    </div>

    <section class="card" aria-labelledby="ringkasan-tagihan">
        <h2 id="ringkasan-tagihan">Ringkasan tagihan</h2>

        <table>
            <thead>
                <tr>
                    <th scope="col">Data</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Akses</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>Jenis biaya aktif</td>
                    <td>{{ $ringkasan['jenis_biaya_aktif'] }}</td>
                    <td>
                        <a href="{{ route('keuangan.jenis-biaya.index') }}">
                            Kelola
                        </a>
                    </td>
                </tr>

                <tr>
                    <td>Tagihan draf</td>
                    <td>{{ $ringkasan['tagihan_draf'] }}</td>
                    <td>
                        @can('akses-tagihan')
                            <a href="{{ route('tagihan.index', [
                                'status' => \App\Models\Tagihan::DRAF,
                            ]) }}">
                                Lihat
                            </a>
                        @else
                            —
                        @endcan
                    </td>
                </tr>

                <tr>
                    <td>Tagihan terbit</td>
                    <td>{{ $ringkasan['tagihan_terbit'] }}</td>
                    <td>
                        @can('akses-tagihan')
                            <a href="{{ route('tagihan.index', [
                                'status' => \App\Models\Tagihan::TERBIT,
                            ]) }}">
                                Lihat
                            </a>
                        @else
                            —
                        @endcan
                    </td>
                </tr>

                <tr>
                    <td>Tagihan dibatalkan</td>
                    <td>{{ $ringkasan['tagihan_dibatalkan'] }}</td>
                    <td>
                        @can('akses-tagihan')
                            <a href="{{ route('tagihan.index', [
                                'status' => \App\Models\Tagihan::DIBATALKAN,
                            ]) }}">
                                Lihat
                            </a>
                        @else
                            —
                        @endcan
                    </td>
                </tr>

                <tr>
                    <td>Nominal seluruh tagihan terbit</td>
                    <td>
                        Rp{{ number_format(
                            $ringkasan['nominal_tagihan_terbit'],
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                    <td>
                        @can('akses-tagihan')
                            <a href="{{ route('tagihan.index') }}">
                                Buka tagihan
                            </a>
                        @else
                            —
                        @endcan
                    </td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="card" aria-labelledby="ringkasan-pembayaran">
        <h2 id="ringkasan-pembayaran">Ringkasan pembayaran</h2>

        <table>
            <thead>
                <tr>
                    <th scope="col">Status</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Akses</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>Menunggu verifikasi</td>
                    <td>{{ $ringkasan['pembayaran_menunggu'] }}</td>
                    <td>
                        @can('akses-pembayaran')
                            <a href="{{ route('pembayaran.index', [
                                'status' => \App\Models\Pembayaran::MENUNGGU,
                            ]) }}">
                                Verifikasi
                            </a>
                        @else
                            —
                        @endcan
                    </td>
                </tr>

                <tr>
                    <td>Diterima</td>
                    <td>{{ $ringkasan['pembayaran_diterima'] }}</td>
                    <td>
                        @can('akses-pembayaran')
                            <a href="{{ route('pembayaran.index', [
                                'status' => \App\Models\Pembayaran::DITERIMA,
                            ]) }}">
                                Lihat
                            </a>
                        @else
                            —
                        @endcan
                    </td>
                </tr>

                <tr>
                    <td>Ditolak</td>
                    <td>{{ $ringkasan['pembayaran_ditolak'] }}</td>
                    <td>
                        @can('akses-pembayaran')
                            <a href="{{ route('pembayaran.index', [
                                'status' => \App\Models\Pembayaran::DITOLAK,
                            ]) }}">
                                Lihat
                            </a>
                        @else
                            —
                        @endcan
                    </td>
                </tr>

                <tr>
                    <td>Dibatalkan mahasiswa</td>
                    <td>{{ $ringkasan['pembayaran_dibatalkan'] }}</td>
                    <td>
                        @can('akses-pembayaran')
                            <a href="{{ route('pembayaran.index', [
                                'status' => \App\Models\Pembayaran::DIBATALKAN,
                            ]) }}">
                                Lihat
                            </a>
                        @else
                            —
                        @endcan
                    </td>
                </tr>

                <tr>
                    <td>Nominal pembayaran diterima</td>
                    <td>
                        Rp{{ number_format(
                            $ringkasan['nominal_pembayaran_diterima'],
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                    <td>
                        @can('akses-pembayaran')
                            <a href="{{ route('pembayaran.index') }}">
                                Buka pembayaran
                            </a>
                        @else
                            —
                        @endcan
                    </td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="card" aria-labelledby="akses-cepat">
        <h2 id="akses-cepat">Akses cepat</h2>

        <nav aria-label="Layanan keuangan">
            <p>
                <a href="{{ route('keuangan.jenis-biaya.index') }}">
                    Kelola jenis biaya
                </a>
            </p>

            @can('akses-tagihan')
                <p>
                    <a href="{{ route('tagihan.index') }}">
                        Kelola tagihan mahasiswa
                    </a>
                </p>
            @endcan

            @can('akses-pembayaran')
                <p>
                    <a href="{{ route('pembayaran.index') }}">
                        Verifikasi pembayaran
                    </a>
                </p>
            @endcan
        </nav>
    </section>

    <section class="card" aria-labelledby="catatan-dashboard">
        <h2 id="catatan-dashboard">Catatan</h2>

        <p>
            Ringkasan dihitung langsung dari data tagihan dan pembayaran.
            Dashboard ini tidak mengubah status transaksi.
        </p>
    </section>
@endsection