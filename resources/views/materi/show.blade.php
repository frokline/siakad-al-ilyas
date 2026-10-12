@extends('layouts.materi')
@section('title', $materi->judul)
@section('content')
    <div class="heading">
        <div>
            <span class="badge">{{ \App\Models\Materi::STATUS[$materi->status] }}</span>
            <h1>{{ $materi->judul }}</h1>
            <p>{{ $materi->kelasKuliah->kode }} · {{ $materi->kelasKuliah->nama_mk_snapshot }}</p>
        </div><a href="{{ route('materi.index', ['kelas' => $materi->kelas_kuliah_id]) }}">Kembali ke daftar</a>
    </div>
    <article class="card">
        <dl class="metadata">
            <div>
                <dt>Pembuat</dt>
                <dd>{{ $materi->pembuat->nama }}</dd>
            </div>
            <div>
                <dt>Pertemuan</dt>
                <dd>{{ $materi->pertemuan ? 'Ke-' . $materi->pertemuan->nomor . ' — ' . $materi->pertemuan->topik : 'Umum kelas' }}
                </dd>
            </div>
            <div>
                <dt>Terbit</dt>
                <dd>{{ $materi->terbit_at?->setTimezone($zona)->format('d-m-Y H:i') ?? 'Belum terbit' }}
                    ({{ $zona }})</dd>
            </div>
            <div>
                <dt>Revisi</dt>
                <dd>{{ $materi->revisi }}</dd>
            </div>
        </dl>
        @if ($materi->pertemuan?->status === 'batal')
            <p class="notice error">Pertemuan dibatalkan. Materi ini disembunyikan dari mahasiswa sampai kaitannya diperbaiki
                atau pertemuan dipulihkan.</p>
        @endif
        <h2>Uraian</h2>
        <div class="prose">{{ $materi->isi ?: 'Tidak ada uraian tambahan.' }}</div>
        @if ($materi->tautan_eksternal)
            <p><a href="{{ $materi->tautan_eksternal }}" target="_blank" rel="noopener noreferrer">Buka referensi eksternal
                    ↗</a></p>
            <small>Anda akan meninggalkan SIAKAD. Izin dokumen eksternal diatur oleh pemiliknya.</small>
        @endif
    </article>
    <section class="card">
        <h2>Lampiran</h2>
        @forelse($materi->lampiran as $p)
            <div class="attachment">
                <div><strong>{{ $p->berkas->label }}</strong><br><small>{{ $p->berkas->nama_asli }} ·
                        {{ $p->berkas->ukuranLabel() }}</small></div>
                @if ($p->berkas->status === \App\Models\Berkas::TERSEDIA)
                    <form method="post"
                        action="{{ route('materi.tautan', ['materi' => $materi->id, 'lampiran' => $p->id]) }}">
                        @csrf<button type="submit">Unduh</button>
                    </form>
                @else<span>Lampiran tidak tersedia</span>
                @endif
            </div>
        @empty<p class="muted">Tidak ada lampiran.</p>
        @endforelse
    </section>
    @can('manage', $materi)
        <section class="card">
            <h2>Pengelolaan materi</h2>
            @can('update', $materi)
                <p><a class="button" href="{{ route('materi.edit', $materi) }}">Edit draf</a></p>
            @endcan
            <p class="muted">Tarik/arsip mencabut akses mahasiswa untuk permintaan berikutnya. File yang sudah diunduh pengguna
                tidak dapat ditarik dari perangkat mereka.</p>
            @foreach (['terbitkan' => 'Terbitkan untuk peserta kelas', 'tarik' => 'Tarik menjadi draf', 'arsipkan' => 'Arsipkan materi', 'pulihkan' => 'Pulihkan menjadi draf'] as $aksi => $label)
                @can($aksi, $materi)
                    <details class="status-box">
                        <summary>{{ $label }}</summary>
                        <form method="post" action="{{ route('materi.' . $aksi, $materi) }}">
                            @csrf
                            <input type="hidden" name="versi" value="{{ old('versi', $materi->versiForm()) }}">
                            <label for="alasan-{{ $aksi }}">Alasan *</label>
                            <textarea id="alasan-{{ $aksi }}" name="alasan" rows="2" minlength="10" maxlength="1000" required>{{ old('alasan') }}</textarea>
                            <label class="check"><input type="checkbox" name="konfirmasi" value="1" required> Saya memahami
                                tindakan ini.</label>
                            <button type="submit">{{ $label }}</button>
                        </form>
                    </details>
                @endcan
            @endforeach
        </section>
        @if ($audit)
            <section class="card">
                <h2>Riwayat perubahan</h2>
                @forelse($audit as $catatan)
                    <details class="status-box">
                        <summary>Revisi {{ $catatan->versi_entitas }} · {{ $catatan->aksi }} ·
                            {{ $catatan->pelaku?->nama ?? 'Sistem' }}</summary>
                        <p>{{ $catatan->waktu?->setTimezone($zona)->format('d-m-Y H:i:s') }} ·
                            {{ $catatan->alasan ?? 'Pembuatan awal' }}</p>
                        <h3>Sebelum</h3>
                        <pre>{{ json_encode($catatan->sebelum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        <h3>Sesudah</h3>
                        <pre>{{ json_encode($catatan->sesudah, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                @empty<p>Belum ada catatan.</p>
                @endforelse
                {{ $audit->links('materi._pagination') }}
            </section>
        @endif
    @endcan
@endsection
