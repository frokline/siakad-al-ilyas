@extends('layouts.permohonan_surat')
@section('title', 'Permohonan Surat')
@section('content')
    <div class="heading">
        <h1>Permohonan surat</h1>
        @can('create', \App\Models\PermohonanSurat::class)
            <a class="button" href="{{ route('surat.create') }}">Ajukan surat</a>
        @endcan
    </div>
    <form class="card filters" method="get" action="{{ route('surat.index') }}">
        <label for="q">Nomor pengajuan / nomor surat</label><input id="q" name="q" maxlength="100"
            value="{{ $filter['q'] ?? '' }}">
        <label for="status">Status</label><select id="status" name="status">
            <option value="">Semua</option>
            @foreach (\App\Models\PermohonanSurat::STATUS as $kode => $label)
                <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
            @endforeach
        </select><button type="submit">Tampilkan</button><a href="{{ route('surat.index') }}">Reset</a>
    </form>
    <section class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Pengajuan</th>
                    <th>Mahasiswa</th>
                    <th>Layanan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $p)
                    <tr>
                        <td>{{ $p->nomor_pengajuan }}</td>
                        <td>{{ $p->akademik_snapshot['nim'] }} — {{ $p->akademik_snapshot['nama'] }}</td>
                        <td>{{ $p->jenis_snapshot['nama'] }}</td>
                        <td>{{ \App\Models\PermohonanSurat::STATUS[$p->status] }}</td>
                        <td><a href="{{ route('surat.show', $p) }}">Detail</a></td>
                    </tr>
                @empty<tr>
                        <td colspan="5">Belum ada permohonan sesuai filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>{{ $daftar->links('permohonan_surat._pagination') }}
    </section>
@endsection
