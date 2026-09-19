@extends('layouts.pengumpulan')
@section('title', 'Detail Jawaban')
@section('content')
    <div class="heading">
        <h1>{{ $pengumpulan->kegiatan?->judul ?? 'Detail jawaban' }}</h1><a
            href="{{ route('pengumpulan.index') }}">Kembali</a>
    </div>
    @if (session('info'))
        <div class="notice" role="status">{{ session('info') }}</div>
    @endif
    @if (isset($errors) && $errors->any())
        <div class="notice error" role="alert">
            <ul>
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <article class="card">
        <h2>Informasi jawaban</h2>
        <dl>
            <dt>Pemilik</dt>
            <dd>{{ $pengumpulan->pemilik?->nama ?? '—' }}</dd>
            <dt>Status</dt>
            <dd>{{ ucfirst($pengumpulan->status) }}</dd>
            <dt>Versi</dt>
            <dd>{{ $pengumpulan->versi }}</dd>
            <dt>Dikirim</dt>
            <dd>{{ $pengumpulan->dikirim_at?->setTimezone($zona)->format('d-m-Y H:i:s') ?? 'Belum dikirim' }}</dd>
        </dl>
    </article>
    @if ($bolehTulis)
        <section class="card">
            <h2>Jawaban</h2>
            <form method="post" action="{{ route('pengumpulan.update', $pengumpulan) }}">@csrf @method('PATCH')
                <label for="jawaban_teks">Isi jawaban</label>
                <textarea id="jawaban_teks" name="jawaban_teks" rows="10" maxlength="10000">{{ old('jawaban_teks', $pengumpulan->jawaban_teks) }}</textarea>
                <input type="hidden" name="versi" value="{{ $pengumpulan->versiForm() }}"><button type="submit">Simpan
                    draf</button>
            </form>
            <form method="post" action="{{ route('pengumpulan.kirim', $pengumpulan) }}">@csrf
                <input type="hidden" name="versi" value="{{ $pengumpulan->versiForm() }}"><label><input type="checkbox"
                        name="konfirmasi" value="1" required> Saya memastikan jawaban ini siap dikirim.</label><button
                    type="submit">Kirim jawaban final</button>
            </form>
        </section>
    @else
        <section class="card">
            <h2>Isi jawaban</h2>
            <div class="peng-isi">{{ $pengumpulan->jawaban_teks }}</div>
        </section>
    @endif
    <section class="card">
        <h2>Lampiran</h2>
        @forelse ($pengumpulan->lampiran as $lampiran)
            <div class="attachment"><span>{{ $lampiran->nama_asli }}</span>
                @if ($lampiran->aktif)
                    <a href="{{ route('pengumpulan.tautan', [$pengumpulan, $lampiran]) }}">Unduh</a>
                @endif
            </div>
        @empty<p class="muted">Tidak ada lampiran.</p>
        @endforelse
    </section>
    @if ($pemilik)
        <section class="card">
            <h2>Audit jawaban</h2>
            @forelse ($audit as $log)
                <details>
                    <summary>Revisi {{ $log->versi_entitas }} · {{ $log->aksi }}</summary>
                    <p>{{ $log->alasan }}</p>
            </details>@empty<p class="muted">Belum ada audit yang dapat ditampilkan.</p>
            @endforelse
            {{ $audit->links('pengumpulan._pagination') }}
        </section>
    @endif
@endsection
