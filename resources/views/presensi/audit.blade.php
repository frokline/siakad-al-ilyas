@extends('layouts.presensi')
@section('title', 'Riwayat presensi')
@section('content')
    <div class="heading">
        <div>
            <h1>Riwayat pencatatan</h1>
            <p>{{ $baris->peserta_snapshot['nama'] }} · {{ $baris->peserta_snapshot['nim'] }} · Pertemuan
                {{ $sesi->nomor }}</p>
        </div><a href="{{ route('presensi.show', $sesi) }}">Kembali</a>
    </div>
    <div class="card table-wrap">
        <table>
            <caption class="sr-only">Audit pencatatan dan koreksi presensi</caption>
            <thead>
                <tr>
                    <th scope="col">Revisi / waktu</th>
                    <th scope="col">Pelaku / tindakan</th>
                    <th scope="col">Sebelum</th>
                    <th scope="col">Sesudah</th>
                    <th scope="col">Alasan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audit as $log)
                    <tr>
                        <td>{{ $log->versi_entitas }}<span
                                class="sub">{{ $log->waktu->setTimezone($zona)->format('d-m-Y H:i:s') }}</span></td>
                        <td>{{ $log->pelaku?->nama ?? '—' }}<span class="sub">{{ ucfirst($log->aksi) }}</span></td>
                        <td>{{ \App\Models\Presensi::STATUS[$log->sebelum['status'] ?? ''] ?? '—' }}<span
                                class="sub whitespace">{{ $log->sebelum['catatan'] ?? '—' }}</span></td>
                        <td>{{ \App\Models\Presensi::STATUS[$log->sesudah['status'] ?? ''] ?? '—' }}<span
                                class="sub whitespace">{{ $log->sesudah['catatan'] ?? '—' }}</span></td>
                        <td class="whitespace">{{ $log->alasan ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada audit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('presensi._pagination', ['paginator' => $audit])
@endsection
