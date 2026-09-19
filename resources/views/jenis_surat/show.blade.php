@extends('layouts.surat')
@section('title', $jenisSurat->nama)
@section('content')
    <div class="heading">
        <div><span class="badge">{{ $jenisSurat->labelStatus() }}</span>
            <h1>{{ $jenisSurat->kode }} — {{ $jenisSurat->nama }}</h1>
        </div>
        <a href="{{ route('admin.jenis-surat.index') }}">Daftar jenis surat</a>
    </div>
    <section class="card">
        <h2>Informasi jenis surat</h2>
        <dl class="metadata">
            <div>
                <dt>ID</dt>
                <dd>{{ $jenisSurat->id }}</dd>
            </div>
            <div>
                <dt>Revisi</dt>
                <dd>{{ $jenisSurat->revisi }}</dd>
            </div>
            <div>
                <dt>Pembuat</dt>
                <dd>{{ $jenisSurat->pembuat->nama }}</dd>
            </div>
            <div>
                <dt>Dibuat</dt>
                <dd>{{ $jenisSurat->created_at->setTimezone($zona)->format('d-m-Y H:i:s') }} ({{ $zona }})</dd>
            </div>
            @if (!$jenisSurat->aktif)
                <div>
                    <dt>Dinonaktifkan</dt>
                    <dd>{{ $jenisSurat->dinonaktifkan_at->setTimezone($zona)->format('d-m-Y H:i:s') }}</dd>
                </div>
            @endif
        </dl>
        <h3>Persyaratan</h3>
        <div class="prose">{{ $jenisSurat->syarat ?? 'Tidak ada syarat tambahan.' }}</div>
        <p class="notice">Persyaratan adalah informasi layanan. Status aktif dan syarat registrasi mahasiswa diperiksa pada
            modul Permohonan Surat.</p>
        @can('update', $jenisSurat)
            <p><a class="button" href="{{ route('admin.jenis-surat.edit', $jenisSurat) }}">Edit nama / syarat</a></p>
        @endcan
    </section>
    @foreach (['nonaktifkan' => 'Nonaktifkan jenis surat', 'aktifkan' => 'Aktifkan kembali'] as $aksi => $label)
        @can($aksi, $jenisSurat)
            <section class="card">
                <h2>{{ $label }}</h2>
                <p>{{ $aksi === 'nonaktifkan' ? 'Jenis surat akan berhenti tersedia untuk permohonan baru. Permohonan dan dokumen lama tetap disimpan.' : 'Kode lama digunakan kembali; tidak membuat jenis surat baru.' }}
                </p>
                <form method="post" action="{{ route('admin.jenis-surat.' . $aksi, $jenisSurat) }}">
                    @csrf<input type="hidden" name="versi" value="{{ old('versi', $jenisSurat->versiForm()) }}">
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
        {{ $audit->links('jenis_surat._pagination') }}
    </section>
@endsection
