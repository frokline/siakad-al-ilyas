@extends('layouts.surat')
@section('title', 'Jenis Surat')
@section('content')
    <div class="heading">
        <div>
            <h1>Jenis surat</h1>
            <p>Kelola jenis layanan dan persyaratan surat akademik.</p>
        </div>
        @can('create', \App\Models\JenisSurat::class)
            <a class="button" href="{{ route('admin.jenis-surat.create') }}">Tambah jenis surat</a>
        @endcan
    </div>
    <form class="card filters" method="get" action="{{ route('admin.jenis-surat.index') }}">
        <div class="field"><label for="q">Kode / nama surat</label><input id="q" name="q" maxlength="100"
                value="{{ $filter['q'] ?? '' }}"></div>
        <div class="field"><label for="status">Status</label><select id="status" name="status">
                <option value="">Semua status</option>
                <option value="aktif" @selected(($filter['status'] ?? '') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(($filter['status'] ?? '') === 'nonaktif')>Nonaktif</option>
            </select></div>
        <button type="submit">Tampilkan</button><a href="{{ route('admin.jenis-surat.index') }}">Reset</a>
    </form>
    <section class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama surat</th>
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
                        <td><a href="{{ route('admin.jenis-surat.show', $j) }}">Detail</a>
                            @can('update', $j)
                                · <a href="{{ route('admin.jenis-surat.edit', $j) }}">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty<tr>
                        <td colspan="5">Belum ada jenis surat sesuai filter. Jenis awal yang digunakan proyek ini adalah
                            Surat Aktif Kuliah.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>{{ $daftar->links('jenis_surat._pagination') }}
    </section>
@endsection
