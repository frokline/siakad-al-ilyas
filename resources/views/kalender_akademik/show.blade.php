@extends('layouts.kalender_akademik')
@section('title', $agenda->judul)
@section('content')
    <div class="heading">
        <div><span class="badge">{{ \App\Models\KalenderAkademik::STATUS[$agenda->status] }}</span>
            <h1>{{ $agenda->judul }}</h1>
        </div>
        <a
            href="{{ route('kalender.index', ['bulan' => $agenda->mulai_at->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('Y-m')]) }}">Kembali
            ke kalender</a>
    </div>
    @if ($agenda->status === 'batal')
        <div class="notice error"><strong>Agenda dibatalkan.</strong> Waktu di bawah merupakan jadwal yang sudah tidak
            berlaku.</div>
    @endif
    <section class="card">
        <dl class="metadata">
            <div>
                <dt>Jenis</dt>
                <dd>{{ \App\Models\KalenderAkademik::JENIS[$agenda->jenis] }}</dd>
            </div>
            <div>
                <dt>Periode</dt>
                <dd>{{ $agenda->periodeAkademik->kode }}</dd>
            </div>
            <div>
                <dt>Sasaran</dt>
                <dd>{{ $agenda->programStudi?->nama ?? 'Seluruh kampus' }}</dd>
            </div>
            <div>
                <dt>Mulai</dt>
                <dd>{{ $agenda->mulai_at->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('d-m-Y H:i') }} WITA</dd>
            </div>
            <div>
                <dt>Selesai</dt>
                <dd>{{ $agenda->selesai_at->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('d-m-Y H:i') }} WITA
                </dd>
            </div>
            <div>
                <dt>Terakhir diperbarui</dt>
                <dd>{{ $agenda->updated_at->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('d-m-Y H:i') }} WITA
                </dd>
            </div>
        </dl>
        <h2>Keterangan</h2>
        <div class="kal-teks">{{ $agenda->keterangan ?? 'Tidak ada keterangan tambahan.' }}</div>
        @if ($agenda->catatan_perubahan)
            <h2>Catatan perubahan terakhir</h2>
            <div class="kal-teks">{{ $agenda->catatan_perubahan }}</div>
        @endif
        @if ($agenda->jenis === 'krs')
            <p class="notice">Agenda KRS ini bersifat informatif. Batas pengisian/pengesahan KRS mengikuti aturan Periode
                Akademik.</p>
        @endif
        @can('update', $agenda)
            <p><a class="button" href="{{ route('kalender.edit', $agenda) }}">Edit agenda</a></p>
        @endcan
    </section>
    @foreach (['terbitkan' => 'Terbitkan agenda', 'batalkan' => 'Batalkan agenda'] as $aksi => $label)
        @can($aksi, $agenda)
            <form method="post" action="{{ route('kalender.tindakan', $agenda) }}" class="card form-card">@csrf
                <h2>{{ $label }}</h2><input type="hidden" name="aksi" value="{{ $aksi }}"><input
                    type="hidden" name="versi" value="{{ old('versi', $agenda->versiForm()) }}">
                <label for="alasan-{{ $aksi }}">Alasan *</label>
                <textarea id="alasan-{{ $aksi }}" name="alasan" minlength="10" maxlength="1000" rows="3" required>{{ old('alasan') }}</textarea>
                <p class="muted">Alasan tampil pada catatan agenda. Agenda terbit yang dibatalkan tetap terlihat dengan penanda
                    pembatalan.</p>
                <label class="check"><input type="checkbox" name="konfirmasi" value="1" required> Saya sudah memeriksa
                    tindakan dan informasi agenda.</label><button type="submit">{{ $label }}</button>
            </form>
        @endcan
    @endforeach
    @if ($audit)
        <section class="card">
            <h2>Audit admin</h2>
            @forelse($audit as $a)
                <details class="status-box">
                    <summary>Revisi {{ $a->versi_entitas }} · {{ $a->aksi }} · {{ $a->pelaku?->nama ?? 'Petugas' }}
                    </summary>
                    <p>{{ $a->waktu->setTimezone(\App\Models\KalenderAkademik::ZONA)->format('d-m-Y H:i') }} WITA ·
                        {{ $a->alasan }}</p>
                    <h3>Sebelum</h3>
                    <pre>{{ json_encode($a->sebelum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    <h3>Sesudah</h3>
                    <pre>{{ json_encode($a->sesudah, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </details>
            @empty<p>Belum ada catatan audit.</p>
            @endforelse {{ $audit->links('kalender_akademik._pagination') }}
        </section>
    @endif
@endsection
