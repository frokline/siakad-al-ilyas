@extends('layouts.pengumpulan')
@section('title', 'Rekap Pengumpulan')
@section('content')
    <div class="heading">
        <h1>Rekap pengumpulan</h1><a href="{{ route('kegiatan.show', $kegiatan) }}">Kembali ke kegiatan</a>
    </div>
    @include('pengumpulan._jadwal')
    <section class="card">
        <h2>Peserta aktif saat ini</h2>
        <p>{{ $total }} peserta aktif · {{ $sudah }} sudah mengirim · {{ $total - $sudah }} belum mengirim.
        </p>
        <p class="muted">Angka tidak dipengaruhi filter. Draf tidak dibaca atau dihitung sebagai kiriman. Rekap ini bukan
            nilai.</p>
    </section>
    <form class="card filters" method="get" action="{{ route('pengumpulan.rekap', $kegiatan) }}">
        <div class="field"><label for="q">Nama / NIM peserta aktif</label><input id="q" name="q"
                maxlength="100" value="{{ $filter['q'] ?? '' }}"></div>
        <div class="field"><label for="status">Status kiriman</label><select id="status" name="status">
                <option value="">Semua peserta aktif</option>
                <option value="sudah" @selected(($filter['status'] ?? '') === 'sudah')>Sudah mengirim</option>
                <option value="belum" @selected(($filter['status'] ?? '') === 'belum')>Belum mengirim</option>
            </select></div><button type="submit">Tampilkan</button><a
            href="{{ route('pengumpulan.rekap', $kegiatan) }}">Reset</a>
    </form>
    <section class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Kiriman yang berlaku</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peserta as $p)
                    <tr>
                        <td>{{ $p->nim }}</td>
                        <td>{{ $p->nama }}</td>
                        <td>{{ $p->pengumpulan_id ? 'Sudah mengirim' : 'Belum mengirim' }}</td>
                        <td>
                            @if ($p->pengumpulan_id)
                                <a href="{{ route('pengumpulan.show', $p->pengumpulan_id) }}">Lihat versi
                                {{ $p->versi }}</a>@else—
                            @endif
                        </td>
                    </tr>
                @empty<tr>
                        <td colspan="4">Tidak ada peserta aktif sesuai filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>{{ $peserta->links('pengumpulan._pagination') }}
    </section>
    <section class="card table-wrap">
        <h2>Seluruh versi final yang pernah diterima</h2>
        <p>Daftar historis ini tidak mengikuti filter peserta aktif. Kiriman peserta yang kemudian nonaktif tetap tercatat.
        </p>
        <table>
            <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>Detail KRS</th>
                    <th>Versi</th>
                    <th>Dikirim ({{ $zona }})</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historis as $p)
                    <tr>
                        <td>{{ $p->pemilik->nama }}</td>
                        <td>{{ $p->detail_krs_id }}</td>
                        <td>{{ $p->versi }}</td>
                        <td>{{ $p->dikirim_at->setTimezone($zona)->format('d-m-Y H:i:s') }}</td>
                        <td><a href="{{ route('pengumpulan.show', $p) }}">Lihat</a></td>
                    </tr>
                @empty<tr>
                        <td colspan="5">Belum ada kiriman final.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>{{ $historis->links('pengumpulan._pagination') }}
    </section>
@endsection
