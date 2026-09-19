@extends('layouts.berkas')
@section('title', 'Berkas Saya')
@section('content')
    <div class="heading">
        <div>
            <h1>Berkas saya</h1>
            <p class="muted">Kelola dokumen yang Anda unggah.</p>
        </div><a class="button" href="{{ route('berkas.create') }}">Unggah berkas</a>
    </div>
    <section class="card">
        <h2>Alokasi penyimpanan</h2>
        <p>{{ number_format($terpakai / 1048576, 2, ',', '.') }} MB / {{ number_format($kuota / 1048576, 0, ',', '.') }} MB
        </p>
        <p class="muted">Menonaktifkan berkas tidak membebaskan alokasi. Hubungi pengelola jika alokasi sudah penuh.</p>
    </section>
    <form class="card filters" method="get" action="{{ route('berkas.index') }}">
        <label>Cari label<input name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"></label>
        <label>Status<select name="status">
                <option value="">Semua status</option>
                @foreach (\App\Models\Berkas::STATUS as $kode => $label)
                    <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                @endforeach
            </select></label>
        <button type="submit">Cari</button><a href="{{ route('berkas.index') }}">Reset</a>
    </form>
    <div class="card table-wrap">
        <table>
            <caption class="sr-only">Berkas yang diunggah oleh akun Anda</caption>
            <thead>
                <tr>
                    <th scope="col">Berkas</th>
                    <th scope="col">Ukuran</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $row)
                    <tr>
                        <td><strong>{{ $row->label }}</strong><span class="sub">{{ $row->nama_asli }}</span></td>
                        <td>{{ $row->ukuranLabel() }}</td>
                        <td><span class="badge {{ $row->status }}">{{ $row->labelStatus() }}</span></td>
                        <td><a href="{{ route('berkas.show', $row) }}">Detail</a></td>
                    </tr>
                @empty<tr>
                        <td colspan="4">Belum ada berkas yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('berkas._pagination', ['paginator' => $daftar])
@endsection
