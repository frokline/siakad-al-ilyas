@extends('layouts.keuangan')
@section('title', $jenisBiaya->nama)
@section('content')
    <div class="heading">
        <div><span class="badge">{{ $jenisBiaya->labelStatus() }}</span>
            <h1>{{ $jenisBiaya->kode }} — {{ $jenisBiaya->nama }}</h1>
        </div>
        <a href="{{ route('keuangan.jenis-biaya.index') }}">Daftar jenis biaya</a>
    </div>
    <section class="card">
        <h2>Informasi jenis biaya</h2>
        <dl class="metadata">
            <div>
                <dt>ID</dt>
                <dd>{{ $jenisBiaya->id }}</dd>
            </div>
            <div>
                <dt>Revisi</dt>
                <dd>{{ $jenisBiaya->revisi }}</dd>
            </div>
            <div>
                <dt>Pembuat</dt>
                <dd>{{ $jenisBiaya->pembuat->nama }}</dd>
            </div>
            <div>
                <dt>Dibuat</dt>
                <dd>{{ $jenisBiaya->created_at->setTimezone($zona)->format('d-m-Y H:i:s') }} ({{ $zona }})</dd>
            </div>
            @if (!$jenisBiaya->aktif)
                <div>
                    <dt>Dinonaktifkan</dt>
                    <dd>{{ $jenisBiaya->dinonaktifkan_at->setTimezone($zona)->format('d-m-Y H:i:s') }}</dd>
                </div>
            @endif
        </dl>
        <h3>Keterangan</h3>
        <div class="prose">{{ $jenisBiaya->keterangan ?? 'Tidak ada keterangan tambahan.' }}</div>
        @if ($jenisBiaya->kode === \App\Models\JenisBiaya::SPP)
            <p class="notice">SPP dibayar per bulan. Nominal dan bulan yang ditagihkan mengikuti penetapan admin pada modul
                Tagihan.</p>
        @endif
        @can('update', $jenisBiaya)
            <p><a class="button" href="{{ route('keuangan.jenis-biaya.edit', $jenisBiaya) }}">Edit nama / keterangan</a></p>
        @endcan
    </section>
    @foreach (['nonaktifkan' => 'Nonaktifkan jenis biaya', 'aktifkan' => 'Aktifkan kembali'] as $aksi => $label)
        @can($aksi, $jenisBiaya)
            <section class="card">
                <h2>{{ $label }}</h2>
                <p>{{ $aksi === 'nonaktifkan' ? 'Jenis biaya akan berhenti tersedia untuk penagihan baru. Riwayat dan kewajiban yang sudah tercatat tetap ada.' : 'Kode lama digunakan kembali; tidak membuat jenis biaya baru.' }}
                </p>
                <form method="post" action="{{ route('keuangan.jenis-biaya.' . $aksi, $jenisBiaya) }}">
                    @csrf<input type="hidden" name="versi" value="{{ old('versi', $jenisBiaya->versiForm()) }}">
                    <label for="alasan-{{ $aksi }}">Alasan *</label>
                    <textarea id="alasan-{{ $aksi }}" name="alasan" rows="3" minlength="10" maxlength="1000" required>{{ old('alasan') }}</textarea>
                    <label class="check"><input type="checkbox" name="konfirmasi" value="1" required> Saya memahami
                        perubahan status ini.</label>
                    <button type="submit">{{ $label }}</button>
                </form>
            </section>
        @endcan
    @endforeach
    <section class="card">
        <h2>Riwayat perubahan</h2>
        @forelse($audit as $a)
            <details class="status-box">
                <summary>Revisi {{ $a->versi_entitas }} · {{ $a->aksi }} · {{ $a->pelaku?->nama ?? 'Sistem' }}
                </summary>
                <p>{{ $a->waktu->setTimezone($zona)->format('d-m-Y H:i:s') }} · {{ $a->alasan ?? 'Pembuatan awal' }}</p>
                <h3>Sebelum</h3>
                <pre>{{ json_encode($a->sebelum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                <h3>Sesudah</h3>
                <pre>{{ json_encode($a->sesudah, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        @empty<p>Belum ada catatan perubahan.</p>
        @endforelse
        {{ $audit->links('jenis_biaya._pagination') }}
    </section>
@endsection
