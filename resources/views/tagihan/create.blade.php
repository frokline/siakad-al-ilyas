@extends('layouts.tagihan')
@section('title', 'Buat Tagihan')
@section('content')
    <div class="heading">
        <h1>Buat draf tagihan</h1><a href="{{ route('tagihan.index') }}">Daftar tagihan</a>
    </div>
    @if ($pilihan)
        <div class="card">
            <h2>{{ $pilihan->nama }} — {{ $pilihan->nim }}</h2>
            <p>Registrasi #{{ $pilihan->id }} · Periode #{{ $pilihan->periode_akademik_id }} · Semester
                {{ $pilihan->semester_studi }}</p>
            <p>Pastikan bulan yang ditagihkan sesuai registrasi pilihan. Tidak dibuat otomatis untuk 12 bulan.</p><a
                href="{{ route('tagihan.create') }}">Pilih registrasi lain</a>
        </div>
        <form class="card form-card" method="post" action="{{ route('tagihan.store') }}">@include('tagihan._form', ['baru' => true])</form>
    @else
        <form class="card filters" method="get" action="{{ route('tagihan.create') }}"><label>Cari NIM / nama<input
                    name="cari" maxlength="100" value="{{ $filter['cari'] ?? '' }}"></label><button type="submit">Cari
                registrasi</button></form>
        <div class="card table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Registrasi</th>
                        <th>Periode / semester</th>
                        <th>Pilih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftar as $r)
                        <tr>
                            <td>{{ $r->nim }} — {{ $r->nama }}</td>
                            <td>#{{ $r->id }} · {{ $r->status }}</td>
                            <td>#{{ $r->periode_akademik_id }} / {{ $r->semester_studi }}</td>
                            <td><a href="{{ route('tagihan.create', ['registrasi' => $r->id]) }}">Pilih</a></td>
                        </tr>
                    @empty<tr>
                            <td colspan="4">Tidak ada registrasi terdaftar/aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>{{ $daftar->links('tagihan._pagination') }}
    @endif
@endsection
