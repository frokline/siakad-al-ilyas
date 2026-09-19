@extends('layouts.keuangan')
@section('title', 'Jenis Biaya')
@section('content')
    <div class="heading">
        <div>
            <h1>Jenis biaya</h1>
            <p>Katalog biaya yang menjadi dasar penagihan akademik.</p>
        </div>
        @can('create', \App\Models\JenisBiaya::class)
            <a class="button" href="{{ route('keuangan.jenis-biaya.create') }}">Tambah jenis biaya</a>
        @endcan
    </div>
    @cannot('create', \App\Models\JenisBiaya::class)
        <p class="notice">Akses Anda hanya untuk membaca. Perubahan dikelola admin keuangan.</p>
    @endcannot
    <form class="card filters" method="get" action="{{ route('keuangan.jenis-biaya.index') }}">
        <div class="field"><label for="q">Kode / nama biaya</label><input id="q" name="q" maxlength="100"
                value="{{ $filter['q'] ?? '' }}"></div>
        <div class="field"><label for="status">Status</label><select id="status" name="status">
                <option value="">Semua status</option>
                <option value="aktif" @selected(($filter['status'] ?? '') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(($filter['status'] ?? '') === 'nonaktif')>Nonaktif</option>
            </select></div>
        <button type="submit">Tampilkan</button><a href="{{ route('keuangan.jenis-biaya.index') }}">Reset</a>
    </form>
    <section class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama biaya</th>
                    <th>Status</th>
                    <th>Revisi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $j)
                    <tr>
                        <td>{{ $j->kode }}</td>
                        <td>{{ $j->nama }}</td>
                        <td><span class="badge">{{ $j->labelStatus() }}</span></td>
                        <td>{{ $j->revisi }}</td>
                        <td><a href="{{ route('keuangan.jenis-biaya.show', $j) }}">Detail</a>
                            @can('update', $j)
                                · <a href="{{ route('keuangan.jenis-biaya.edit', $j) }}">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty<tr>
                        <td colspan="5">Belum ada jenis biaya sesuai filter. Jenis awal yang digunakan proyek ini adalah
                            SPP Bulanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>{{ $daftar->links('jenis_biaya._pagination') }}
    </section>
@endsection
