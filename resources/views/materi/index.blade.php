@extends('layouts.materi')
@section('title', 'Materi Kuliah')
@section('content')
    <div class="heading">
        <div>
            <h1>Materi kuliah</h1>
            <p class="muted">Materi dan referensi pembelajaran kelas Anda.</p>
        </div>
        @if ($pengelola)
            <a class="button" href="{{ route('materi.kelas') }}">Pilih kelas / buat materi</a>
        @endif
    </div>
    <form method="get" action="{{ route('materi.index') }}" class="card filters">
        <div><label for="q">Judul materi</label><input id="q" name="q" maxlength="100"
                value="{{ $filter['q'] ?? '' }}"></div>
        @if (!empty($filter['kelas']))
            <input type="hidden" name="kelas" value="{{ $filter['kelas'] }}">
        @endif
        @if ($pengelola)
            <div><label for="status">Status</label><select id="status" name="status">
                    <option value="">Semua status yang dapat diakses</option>
                    @foreach (\App\Models\Materi::STATUS as $kode => $label)
                        <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                    @endforeach
                </select></div>
        @endif
        <button type="submit">Cari</button><a href="{{ route('materi.index') }}">Reset</a>
    </form>
    <div class="grid">
        @forelse($daftar as $item)
            <article class="card">
                <span class="badge">{{ \App\Models\Materi::STATUS[$item->status] }}</span>
                <h2><a href="{{ route('materi.show', $item) }}">{{ $item->judul }}</a></h2>
                <p>{{ $item->kelasKuliah->kode }} · {{ $item->kelasKuliah->nama_mk_snapshot }}</p>
                <p class="muted">{{ \Illuminate\Support\Str::limit((string) $item->isi, 180) }}</p>
                <a href="{{ route('materi.show', $item) }}">Buka materi →</a>
            </article>
        @empty
            <div class="card empty">Belum ada materi yang dapat ditampilkan. Mahasiswa memerlukan KRS disahkan dan materi
                terbit.</div>
        @endforelse
    </div>
    {{ $daftar->links('materi._pagination') }}
@endsection
