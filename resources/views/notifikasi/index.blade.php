@extends('layouts.notifikasi')
@section('content')
    <h1>
        Notifikasi saya</h1>
    <p><strong>{{ $belum }}</strong> belum dibaca dan masih dapat diakses.</p>
    <p class="muted">Buka sumber untuk melihat keadaan terbaru. Notifikasi lama dapat merujuk revisi sebelumnya. Sumber yang
        sudah tidak boleh Anda akses otomatis tidak ditampilkan.</p>
    <form class="card" method="get" action="{{ route('notifikasi.index') }}">
        <label for="status">Status baca</label><select id="status" name="status">
            @foreach (['semua' => 'Semua', 'belum' => 'Belum dibaca', 'sudah' => 'Sudah dibaca'] as $nilai => $label)
                <option value="{{ $nilai }}" @selected(($filter['status'] ?? 'semua') === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
        <label for="jenis">Jenis sumber</label><select id="jenis" name="jenis">
            <option value="">Semua jenis</option>
            @foreach ($jenisAktif as $jenis)
                <option value="{{ $jenis }}" @selected(($filter['jenis'] ?? '') === $jenis)>{{ ucfirst($jenis) }}</option>
            @endforeach
        </select>
        <button type="submit">Terapkan</button>
    </form>
    @php($idsBelum = $daftar->getCollection()->whereNull('dibaca_at')->pluck('id'))
    @if ($idsBelum->isNotEmpty())
        <form method="post" action="{{ route('notifikasi.baca-halaman') }}">@csrf
            @foreach ($idsBelum as $id)
                <input type="hidden" name="ids[]" value="{{ $id }}">
            @endforeach
            <button type="submit">Tandai {{ $idsBelum->count() }} notifikasi pada halaman ini sudah dibaca</button>
        </form>
    @endif
    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Notifikasi</th>
                    <th>Diterima (WITA)</th>
                    <th>Status</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $n)
                    <tr>
                        <td><strong>{{ $n->judul }}</strong>
                            <p class="muted">Sumber #{{ $n->sumber_id }} · Revisi {{ $n->sumber_revisi }}</p>
                        </td>
                        <td>{{ $n->created_at->setTimezone('Asia/Makassar')->format('d-m-Y H:i') }}</td>
                        <td>{{ $n->dibaca_at ? 'Sudah dibaca' : 'Belum dibaca' }}
                            @if ($n->dibaca_at)
                                <small>{{ $n->dibaca_at->setTimezone('Asia/Makassar')->format('d-m-Y H:i') }}</small>
                            @endif
                        </td>
                        <td>
                            <form method="post" action="{{ route('notifikasi.buka', $n) }}">@csrf<button
                                    type="submit">Buka sumber</button></form>
                            @if (!$n->dibaca_at)
                                <form method="post" action="{{ route('notifikasi.baca', $n) }}">@csrf<button
                                        type="submit" class="secondary">Tandai dibaca</button></form>
                            @endif
                        </td>
                    </tr>
                @empty<tr>
                        <td colspan="4">Belum ada notifikasi yang sesuai. Sinkronisasi berjalan berkala apabila scheduler
                            aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $daftar->links('notifikasi._pagination') }}
@endsection
