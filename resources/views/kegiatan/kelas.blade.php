@extends('layouts.kegiatan')
@section('title', 'Pilih Kelas Kegiatan')
@section('content')
    <h1>Pilih kelas kegiatan</h1>
    <p class="muted">Dosen hanya melihat kelas penugasan aktifnya. Kegiatan baru memerlukan periode aktif.</p>
    <form method="get" action="{{ route('kegiatan.kelas') }}" class="card filters">
        <div><label for="q">Kode kelas atau mata kuliah</label><input id="q" name="q" maxlength="100"
                value="{{ $filter['q'] ?? '' }}"></div>
        <button type="submit">Cari</button><a href="{{ route('kegiatan.index') }}">Kembali</a>
    </form>
    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kelas</th>
                    <th>Mata kuliah</th>
                    <th>Status</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $kelas)
                    <tr>
                        <td>{{ $kelas->kode }}</td>
                        <td>{{ $kelas->nama_mk_snapshot }}</td>
                        <td>{{ $kelas->status }}</td>
                        <td>
                            <a href="{{ route('kegiatan.index', ['kelas' => $kelas->id]) }}">Lihat kegiatan</a>
                            @can('create', [\App\Models\Kegiatan::class, $kelas])
                                · <a href="{{ route('kegiatan.create', ['kelas' => $kelas->id]) }}">Buat draf</a>
                            @endcan
                        </td>
                    </tr>
                @empty<tr>
                        <td colspan="4">Belum ada kelas yang dapat dikelola.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $daftar->links('kegiatan._pagination') }}
@endsection
