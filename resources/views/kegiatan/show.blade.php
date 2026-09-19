@extends('layouts.kegiatan')
@section('title', $kegiatan->judul)
@section('content')
    <div class="heading">
        <div>
            <span class="badge">{{ \App\Models\Kegiatan::JENIS[$kegiatan->jenis] }} · {{ $kegiatan->labelJadwal() }}</span>
            <h1>{{ $kegiatan->judul }}</h1>
            <p>{{ $kegiatan->kelasKuliah->kode }} · {{ $kegiatan->kelasKuliah->nama_mk_snapshot }}</p>
        </div><a href="{{ route('kegiatan.index', ['kelas' => $kegiatan->kelas_kuliah_id]) }}">Kembali ke daftar</a>
    </div>
    <section class="card">
        <h2>Jadwal dan ketentuan</h2>
        @if ($bacaIsi)
            @include('pengumpulan._tombol_kegiatan')
        @endif
        <dl class="metadata">
            <div>
                <dt>Mulai</dt>
                <dd>{{ $kegiatan->buka_at->setTimezone($zona)->format('d-m-Y H:i') }}</dd>
            </div>
            <div>
                <dt>Tenggat</dt>
                <dd>{{ $kegiatan->tenggat_at->setTimezone($zona)->format('d-m-Y H:i') }}</dd>
            </div>
            <div>
                <dt>Zona waktu</dt>
                <dd>{{ $zona }}</dd>
            </div>
            <div>
                <dt>Pembuat</dt>
                <dd>{{ $kegiatan->pembuat->nama }}</dd>
            </div>
            <div>
                <dt>Pertemuan</dt>
                <dd>{{ $kegiatan->pertemuan ? 'Ke-' . $kegiatan->pertemuan->nomor . ' — ' . $kegiatan->pertemuan->topik : 'Umum kelas' }}
                </dd>
            </div>
            <div>
                <dt>Berkas jawaban</dt>
                <dd>Maksimal {{ $kegiatan->maks_berkas }} berkas, {{ (int) ($kegiatan->maks_ukuran_byte / 1048576) }} MB
                    per berkas.</dd>
            </div>
            <div>
                <dt>Format jawaban</dt>
                <dd>{{ strtoupper(implode(', ', $kegiatan->ekstensi_diizinkan)) }}</dd>
            </div>
        </dl>
        @if ($kegiatan->pertemuan?->status === 'batal')
            <p class="notice error">Pertemuan dibatalkan. Kegiatan ini disembunyikan dari mahasiswa.</p>
        @endif
    </section>
    @if ($bacaIsi)
        <article class="card">
            <h2>Instruksi</h2>
            <div class="prose">{{ $kegiatan->instruksi }}</div>
        </article>
        <section class="card">
            <h2>Lampiran instruksi</h2>
            @forelse($kegiatan->lampiran as $p)
                <div class="attachment">
                    <div><strong>{{ $p->berkas->label }}</strong><small>{{ $p->berkas->nama_asli }} ·
                            {{ $p->berkas->ukuranLabel() }}</small></div>
                    @if ($p->berkas->status === \App\Models\Berkas::TERSEDIA)
                        <form method="post"
                            action="{{ route('kegiatan.tautan', ['kegiatan' => $kegiatan->id, 'lampiran' => $p->id]) }}">
                            @csrf<button type="submit">Unduh</button>
                        </form>
                    @else<span>Lampiran tidak tersedia</span>
                    @endif
                </div>
            @empty<p class="muted">Tidak ada lampiran tambahan.</p>
            @endforelse
        </section>
    @else
        <div class="notice">Instruksi dan lampiran tersedia mulai
            {{ $kegiatan->buka_at->setTimezone($zona)->format('d-m-Y H:i') }} ({{ $zona }}).</div>
    @endif
    @can('manage', $kegiatan)
        <section class="card">
            <h2>Pengelolaan kegiatan</h2>
            <p>Revisi {{ $kegiatan->revisi }} · Status {{ \App\Models\Kegiatan::STATUS[$kegiatan->status] }}</p>
            @can('update', $kegiatan)
                <p><a class="button" href="{{ route('kegiatan.edit', $kegiatan) }}">Edit draf</a></p>
            @endcan
            <p class="muted">Perpanjangan berlaku untuk seluruh peserta kelas. Kegiatan yang ditutup tetap ditutup setelah
                diperpanjang; gunakan Buka Kembali jika diperlukan.
                Arsip menyembunyikan kegiatan dari mahasiswa. Pemulihan kegiatan yang pernah terbit menghasilkan status Ditutup.
            </p>
            @foreach (['terbitkan' => 'Terbitkan kegiatan', 'tutup' => 'Tutup kegiatan', 'bukaKembali' => 'Buka kembali', 'perpanjang' => 'Perpanjang tenggat', 'arsipkan' => 'Arsipkan kegiatan', 'pulihkan' => 'Pulihkan kegiatan'] as $aksi => $label)
                @can($aksi, $kegiatan)
                    <details class="status-box">
                        <summary>{{ $label }}</summary>
                        <form method="post" action="{{ route('kegiatan.' . $aksi, $kegiatan) }}">
                            @csrf<input type="hidden" name="versi" value="{{ old('versi', $kegiatan->versiForm()) }}">
                            @if ($aksi === 'perpanjang')
                                <label for="tenggat_baru">Tenggat baru ({{ $zona }}) *</label>
                                <input type="datetime-local" id="tenggat_baru" name="tenggat_baru" step="60" required
                                    value="{{ old('tenggat_baru') }}">
                            @endif
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
                {{ $audit->links('kegiatan._pagination') }}
            </section>
        @endif
    @endcan
@endsection
