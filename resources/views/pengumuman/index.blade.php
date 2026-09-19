@extends('layouts.pengumuman')
@section('title', 'Pengumuman')
@section('content')
    <div class="heading">
        <h1>{{ $mode === 'kelola' ? 'Kelola pengumuman' : 'Pengumuman untuk saya' }}</h1>
        @can('create', \App\Models\Pengumuman::class)
            <a class="button" href="{{ route('pengumuman.create') }}">Buat pengumuman</a>
        @endcan
    </div>
    <p class="muted">
        {{ $mode === 'kelola' ? 'Draf, publikasi, dan arsip yang boleh Anda kelola.' : 'Pengumuman yang sedang tayang dan sesuai sasaran akun Anda.' }}
    </p>
    <form class="card" method="get" action="{{ route('pengumuman.index') }}">
        <input type="hidden" name="mode" value="{{ $mode }}"><label for="q">Cari judul</label><input
            id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}">
        @if ($mode === 'kelola')<label for="status">Status</label><select
                id="status" name="status">
                <option value="">Semua</option>
                @foreach (\App\Models\Pengumuman::STATUS as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(($filter['status'] ?? '') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        @endif
        <button type="submit">Cari</button>
    </form>
    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Status</th>
                    <th>Terbit (WITA)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $p)
                    <tr>
                        <td><a href="{{ route('pengumuman.show', $p) }}">{{ $p->judul }}</a></td>
                        <td>{{ $p->pembuat?->nama }}</td>
                        <td>{{ \App\Models\Pengumuman::STATUS[$p->status] }}@if ($p->status === 'terbit' && $p->kedaluwarsa())
                                · Masa tayang berakhir
                            @endif
                        </td>
                        <td>{{ $p->terbit_at?->setTimezone('Asia/Makassar')->format('d-m-Y H:i') ?? '—' }}</td>
                    </tr>
                    @empty<tr>
                            <td colspan="4">Belum ada pengumuman sesuai pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>{{ $daftar->links('pengumuman._pagination') }}
    @endsection
