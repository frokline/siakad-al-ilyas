@extends('layouts.presensi')
@section('title', 'Daftar pertemuan')
@section('content')
    <div class="heading">
        <div>
            <h1>Presensi perkuliahan</h1>
            <p class="muted">Pilih pertemuan yang akan dicatat. Zona waktu: {{ $zona }}.</p>
        </div>
    </div>
    <form method="get" action="{{ route('presensi.index') }}" class="card filters">
        <label>Status pertemuan
            <select name="status">
                <option value="">Semua status</option>
                @foreach (['terjadwal' => 'Terjadwal', 'berlangsung' => 'Berlangsung', 'selesai' => 'Selesai', 'batal' => 'Batal'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(($filter['status'] ?? '') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>Tanggal rencana <input type="date" name="tanggal" value="{{ $filter['tanggal'] ?? '' }}"></label>
        <button type="submit">Terapkan</button><a href="{{ route('presensi.index') }}">Reset</a>
    </form>
    <div class="card table-wrap">
        <table>
            <caption class="sr-only">Daftar pertemuan yang dapat diakses</caption>
            <thead>
                <tr>
                    <th scope="col">Kelas / mata kuliah</th>
                    <th scope="col">Pertemuan</th>
                    <th scope="col">Waktu rencana</th>
                    <th scope="col">Status</th>
                    <th scope="col">Presensi</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $sesi)
                    <tr>
                        <td><strong>{{ $sesi->kelasKuliah->kode }}</strong><span
                                class="sub">{{ $sesi->kelasKuliah->nama_mk_snapshot }}</span></td>
                        <td>{{ $sesi->nomor }}<span class="sub">{{ $sesi->topik }}</span></td>
                        <td>{{ $sesi->mulai_rencana->setTimezone($zona)->format('d-m-Y H:i') }}<span class="sub">s.d.
                                {{ $sesi->selesai_rencana->setTimezone($zona)->format('H:i') }}</span></td>
                        <td>{{ ucfirst($sesi->status) }}</td>
                        <td>{{ $sesi->presensiPertemuan ? ucfirst($sesi->presensiPertemuan->status) : 'Belum disiapkan' }}
                        </td>
                        <td><a class="button" href="{{ route('presensi.show', $sesi) }}">Buka</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Belum ada pertemuan yang sesuai atau Anda belum ditugaskan ke kelas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('presensi._pagination', ['paginator' => $daftar])
@endsection
