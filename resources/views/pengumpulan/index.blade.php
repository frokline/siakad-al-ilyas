@extends('layouts.pengumpulan')
@section('title', 'Daftar Jawaban')
@section('content')
    <div class="heading">
        <div>
            <h1>Daftar jawaban</h1>
            <p>Draf hanya terlihat oleh pemilik. Pengelola hanya melihat kiriman final kelasnya.</p>
        </div>
        <a class="button" href="{{ route('kegiatan.index') }}">Pilih kegiatan</a>
    </div>
    <form class="card filters" method="get" action="{{ route('pengumpulan.index') }}">
        <div class="field"><label for="q">Judul kegiatan</label><input id="q" name="q" maxlength="100"
                value="{{ $filter['q'] ?? '' }}"></div>
        <div class="field"><label for="kegiatan">ID kegiatan</label><input id="kegiatan" type="number" name="kegiatan"
                min="1" value="{{ $filter['kegiatan'] ?? '' }}"></div>
        <div class="field"><label for="status">Status</label><select id="status" name="status">
                <option value="">Semua yang berhak dilihat</option>
                @foreach (['draf' => 'Draf saya', 'dikirim' => 'Semua versi terkirim', 'berlaku' => 'Kiriman yang berlaku'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(($filter['status'] ?? '') === $nilai)>{{ $label }}</option>
                @endforeach
            </select></div>
        <button type="submit">Tampilkan</button><a href="{{ route('pengumpulan.index') }}">Reset</a>
    </form>
    <section class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kegiatan</th>
                    <th>Mahasiswa</th>
                    <th>Versi</th>
                    <th>Status</th>
                    <th>Dikirim ({{ $zona }})</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar as $p)
                    <tr>
                        <td>{{ $p->kegiatan->judul }}<small>{{ $p->kegiatan->kelasKuliah->kode }}</small></td>
                        <td>{{ $p->pemilik->nama }}</td>
                        <td>{{ $p->versi }}</td>
                        <td>{{ \App\Models\Pengumpulan::STATUS[$p->status] }} @if (in_array($p->id, $berlakuIds, true))
                                <span class="badge">Berlaku</span>
                            @endif
                        </td>
                        <td>{{ $p->dikirim_at?->setTimezone($zona)->format('d-m-Y H:i:s') ?? 'Belum dikirim' }}</td>
                        <td><a href="{{ route('pengumpulan.show', $p) }}">Lihat</a></td>
                    </tr>
                @empty<tr>
                        <td colspan="6">Belum ada jawaban yang dapat ditampilkan. Mulai dari menu Tugas dan Ujian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>{{ $daftar->links('pengumpulan._pagination') }}
    </section>
@endsection
