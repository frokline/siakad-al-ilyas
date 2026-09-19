@extends('layouts.siakad')
@section('title', 'Detail KRS')

@section('content')
    <div class="page-heading">
        <div>
            <h1>KRS #{{ $krs->id }}</h1>
            <p class="subtitle">
                {{ $registrasi->riwayatStudi->mahasiswa->nim }} — {{ $registrasi->riwayatStudi->mahasiswa->user->nama }}
            </p>
        </div>
        <div class="actions">
            @if ($krs->status === \App\Models\Krs::DRAF && $periodeTerbuka && $jendelaTerbuka)
                <a class="button" href="{{ route('admin.krs.edit', $krs) }}">Edit catatan</a>
            @endif
            @if ($krs->status === \App\Models\Krs::DISAHKAN)
                <a class="button" href="{{ route('admin.krs.cetak', $krs) }}">Cetak KRS</a>
            @endif
            <a class="button secondary" href="{{ route('admin.krs.index') }}">Daftar KRS</a>
        </div>
    </div>

    <section class="card">
        <div class="card-header">
            <h2>Identitas akademik</h2>
            <span class="badge" data-krs-status="{{ $krs->status }}">{{ \App\Models\Krs::STATUS[$krs->status] }} · versi
                {{ $krs->versi }}</span>
        </div>
        <div class="panel-body">@include('admin.krs._identitas')</div>
    </section>

    <section class="card krs-section">
        <div class="card-header">
            <h2>Paket mata kuliah</h2>
        </div>
        @include('admin.krs._mata_kuliah')
        <div class="panel-body">
            <p class="help">Keikutsertaan disahkan untuk seluruh paket. Kelas yang sudah selesai tetap menjadi riwayat
                akademik.</p>
        </div>
    </section>

    <section class="card krs-section">
        <div class="card-header">
            <h2>Pengajuan dan pengesahan</h2>
        </div>
        <div class="panel-body">
            <dl class="detail-grid">
                <div>
                    <dt>Diajukan</dt>
                    <dd>{{ $krs->diajukan_at?->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt>Disahkan</dt>
                    <dd>{{ $krs->disahkan_at?->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt>Admin pengesah</dt>
                    <dd>{{ $krs->pengesah?->nama ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Terakhir diperbarui</dt>
                    <dd>{{ $krs->updated_at->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
                    </dd>
                </div>
            </dl>
            <h3>Catatan KRS</h3>
            <p class="detail-multiline">{{ $krs->catatan ?? 'Belum ada catatan.' }}</p>
            @if ($krs->status === \App\Models\Krs::DIBATALKAN)
                <p class="krs-hambatan">KRS ini dibatalkan. Tanggal pengajuan/pengesahan di atas menunjukkan proses
                    sebelumnya.</p>
            @endif
        </div>
    </section>

    <section class="card krs-section">
        <div class="card-header">
            <h2>Tindakan KRS</h2>
        </div>
        <div class="panel-body">
            <p class="help">Pengesahan dan pengembalian boleh diproses setelah batas pengisian, selama periode aktif.
                Revisi dan pengajuan ulang mengikuti jadwal pengisian KRS.</p>
            @include('admin.krs._operasi')
        </div>
    </section>

    <section class="card krs-section" id="riwayat-perubahan">
        <div class="card-header">
            <h2>Riwayat perubahan</h2>
        </div>
        <div class="panel-body">
            <p class="help">Tindakan terbaru ditampilkan terlebih dahulu. Waktu menggunakan
                {{ config('siakad.timezone', 'Asia/Makassar') }}.</p>
            @include('admin.krs._audit')
        </div>
    </section>
@endsection
