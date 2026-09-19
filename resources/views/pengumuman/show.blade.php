@extends('layouts.pengumuman')
@section('title', $item->judul)
@section('content')
    <article class="card">
        <h1>{{ $item->judul }}</h1>
        <p>Oleh {{ $item->pembuat?->nama }} · {{ \App\Models\Pengumuman::STATUS[$item->status] }}</p>
        @if ($item->status === 'terbit' && $item->kedaluwarsa())
            <p class="notice">Masa tayang sudah berakhir. Hanya pengelola yang dapat membuka halaman ini.</p>
        @endif
        <p class="muted">Terbit:
            {{ $item->terbit_at?->setTimezone('Asia/Makassar')->format('d-m-Y H:i') ?? 'Belum terbit' }} · Batas tayang:
            {{ $item->berakhir_at?->setTimezone('Asia/Makassar')->format('d-m-Y H:i') ?? 'Sampai diarsipkan' }}</p>
        <div class="peng-isi">{{ $item->isi }}</div>
    </article>
    @can('update', $item)
        <a class="button" href="{{ route('pengumuman.edit', $item) }}">Edit draf</a>
    @endcan
    @if ($kelola)
        <section class="card">
            <h2>Sasaran pembaca</h2>
            <ul>
                @foreach ($item->sasaran as $s)
                    <li>{{ ucfirst($s->lingkup) }}@if ($s->lingkup === 'prodi')
                            : {{ $s->programStudi?->nama }}
                            @endif
@if ($s->lingkup === 'kelas')
                                : {{ $s->kelasKuliah?->kode }}
                            @endif · {{ $s->role?->kode ?? 'Semua peran sesuai lingkup' }}</li>
                @endforeach
            </ul>
            <p class="muted">Keanggotaan diperiksa saat halaman dibuka. Dosen harus masih mengampu; mahasiswa kelas harus
                memiliki KRS disahkan, detail KRS aktif, registrasi dan riwayat studi aktif.</p>
        </section>
        @foreach (['terbit' => 'Terbitkan', 'arsip' => 'Arsipkan'] as $aksi => $label)
            @can($aksi, $item)
                <form class="card" method="post" action="{{ route('pengumuman.tindakan', $item) }}">@csrf
                    <h2>{{ $label }} pengumuman</h2><input type="hidden" name="aksi"
                        value="{{ $aksi }}"><input type="hidden" name="versi" value="{{ $item->versiForm() }}">
                    <label for="alasan-{{ $aksi }}">Alasan (untuk audit pengelola)</label>
                    <textarea id="alasan-{{ $aksi }}" name="alasan" required minlength="10" maxlength="1000" rows="2"></textarea>
                    <label><input type="checkbox" name="konfirmasi" value="1" required> Saya sudah memeriksa isi dan
                        sasaran.
                        {{ $aksi === 'terbit' ? 'Isi dan sasaran terkunci setelah terbit.' : 'Arsip tidak dapat diterbitkan ulang.' }}</label>
                    <button type="submit">{{ $label }}</button>
                </form>
            @endcan
        @endforeach
        <section class="card">
            <h2>Audit pengelola</h2>
            @foreach ($audit as $log)
                <details>
                    <summary>Revisi {{ $log->versi_entitas }} · {{ $log->aksi }} · {{ $log->pelaku?->nama }} ·
                        {{ \Carbon\CarbonImmutable::parse($log->waktu)->setTimezone('Asia/Makassar')->format('d-m-Y H:i:s') }}
                    </summary>
                    <p>{{ $log->alasan }}</p>
                    <h3>Sebelum</h3>
                    <pre class="peng-json">{{ json_encode($log->sebelum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    <h3>Sesudah</h3>
                    <pre class="peng-json">{{ json_encode($log->sesudah, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            @endforeach
            {{ $audit->links('pengumuman._pagination') }}
        </section>
    @endif
    <a href="{{ route('pengumuman.index', ['mode' => $kelola ? 'kelola' : 'bacaan']) }}">Kembali ke daftar</a>
@endsection
