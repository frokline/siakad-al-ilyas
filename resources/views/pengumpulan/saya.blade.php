@extends('layouts.pengumpulan')
@section('title', 'Jawaban Saya')
@section('content')
    <div class="heading">
        <h1>Jawaban saya</h1><a href="{{ route('pengumpulan.index') }}">Semua jawaban</a>
    </div>
    @include('pengumpulan._jadwal')
    <section class="card">
        <h2>Status pengumpulan</h2>
        @forelse($berlaku as $p)
            <p class="notice">Kiriman yang berlaku: <a href="{{ route('pengumpulan.show', $p) }}">versi
                    {{ $p->versi }}</a>,
                dikirim {{ $p->dikirim_at->setTimezone($zona)->format('d-m-Y H:i:s') }}.</p>
        @empty<p class="notice error">Belum ada jawaban final yang terkumpul.</p>
        @endforelse
        <p>Draf baru tidak menggantikan kiriman sebelumnya. Versi baru dimulai kosong; Anda boleh memakai ID berkas milik
            sendiri dari versi terdahulu.</p>
        @if ($bolehTulis)
            @if ($draf)
                <a class="button" href="{{ route('pengumpulan.edit', $draf) }}">Lanjutkan draf versi {{ $draf->versi }}</a>
            @else
                <form method="post" action="{{ route('pengumpulan.store', $kegiatan) }}">
                    @csrf<input type="hidden" name="token_draf" value="{{ old('token_draf', $token) }}">
                    <input type="hidden" name="dasar_versi" value="{{ old('dasar_versi', $dasarVersi) }}">
                    <button type="submit">Buat draf versi {{ $dasarVersi + 1 }}</button>
                </form>
            @endif
        @else<p class="muted">Pembuatan, perubahan, dan pengiriman draf tidak tersedia saat ini. Periksa jadwal dan
                status keikutsertaan Anda.</p>
        @endif
    </section>
    <section class="card table-wrap">
        <h2>Riwayat versi saya</h2>
        <table>
            <thead>
                <tr>
                    <th>Versi</th>
                    <th>Status</th>
                    <th>Dikirim ({{ $zona }})</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $p)
                    <tr>
                        <td>{{ $p->versi }}</td>
                        <td>{{ \App\Models\Pengumpulan::STATUS[$p->status] }}</td>
                        <td>{{ $p->dikirim_at?->setTimezone($zona)->format('d-m-Y H:i:s') ?? 'Belum dikirim' }}</td>
                        <td><a href="{{ route('pengumpulan.show', $p) }}">Buka versi</a></td>
                    </tr>
                @empty<tr>
                        <td colspan="4">Belum ada versi jawaban.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>{{ $daftar->links('pengumpulan._pagination') }}
    </section>
@endsection
