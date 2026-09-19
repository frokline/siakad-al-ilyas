@extends('layouts.kegiatan')
@section('title', 'Tugas dan Ujian')
@section('content')
    <div class="heading">
        <div>
            <h1>Tugas dan ujian</h1>
            <p class="muted">Jadwal, instruksi, dan lampiran kegiatan kelas Anda.</p>
        </div>
        @if ($pengelola)
            <a class="button" href="{{ route('kegiatan.kelas') }}">Pilih kelas / buat kegiatan</a>
        @endif
    </div>
    <form method="get" action="{{ route('kegiatan.index') }}" class="card filters">
        <div><label for="q">Judul</label><input id="q" name="q" maxlength="100"
                value="{{ $filter['q'] ?? '' }}"></div>
        <div><label for="jenis">Jenis</label><select id="jenis" name="jenis">
                <option value="">Semua jenis</option>
                @foreach (\App\Models\Kegiatan::JENIS as $kode => $label)
                    <option value="{{ $kode }}" @selected(($filter['jenis'] ?? '') === $kode)>{{ $label }}</option>
                @endforeach
            </select></div>
        @if ($pengelola)
            <div><label for="status">Status</label><select id="status" name="status">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Kegiatan::STATUS as $kode => $label)
                        <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                    @endforeach
                </select></div>
        @endif
        @if (!empty($filter['kelas']))
            <input type="hidden" name="kelas" value="{{ $filter['kelas'] }}">
        @endif
        <button type="submit">Cari</button><a href="{{ route('kegiatan.index') }}">Reset</a>
    </form>
    <div class="grid">
        @forelse($daftar as $item)
            <article class="card">
                <span class="badge">{{ \App\Models\Kegiatan::JENIS[$item->jenis] }} · {{ $item->labelJadwal() }}</span>
                <h2><a href="{{ route('kegiatan.show', $item) }}">{{ $item->judul }}</a></h2>
                <p>{{ $item->kelasKuliah->kode }} · {{ $item->kelasKuliah->nama_mk_snapshot }}</p>
                <p>Mulai: {{ $item->buka_at->setTimezone($zona)->format('d-m-Y H:i') }}<br>
                    Tenggat:
                    {{ $item->tenggat_at->setTimezone($zona)->format('d-m-Y H:i') }}<br><small>{{ $zona }}</small>
                </p>
                <a href="{{ route('kegiatan.show', $item) }}">Buka kegiatan →</a>
            </article>
        @empty<div class="card empty">Belum ada kegiatan yang dapat ditampilkan untuk akun Anda.</div>
        @endforelse
    </div>
    {{ $daftar->links('kegiatan._pagination') }}
@endsection
