@extends('layouts.siakad')
@section('title', 'Detail Pertemuan')
@section('content')
    @php
        $zona = config('siakad.timezone', 'Asia/Makassar');
        $tautan = $sesi->tautan_pertemuan;
        $pengajar = is_array($sesi->pengajar_snapshot) ? $sesi->pengajar_snapshot : [];
        $sumber = is_array($sesi->jadwal_snapshot) ? $sesi->jadwal_snapshot : [];
    @endphp
    <div class="page-heading">
        <div>
            <h1>Pertemuan {{ $sesi->nomor }}</h1>
            <p class="subtitle">{{ $kelas->kode }} · {{ $kelas->nama_mk_snapshot }} · Revisi {{ $sesi->revisi }}</p>
        </div>
        <div class="actions">
            @if ($sesi->dapatDiubah())
                <a class="button" href="{{ route('admin.pertemuan.edit', $sesi) }}">Edit rencana</a>
            @endif
            <a class="button secondary" href="{{ route('admin.pertemuan.create', ['kelas_id' => $kelas->id]) }}">Buat sesi
                lain</a>
            <a class="button secondary" href="{{ route('admin.pertemuan.index', ['kelas_id' => $kelas->id]) }}">Daftar
                kelas</a>
        </div>
    </div>
    <section class="card pertemuan-section">
        <div class="panel-body">
            <div class="pertemuan-hero">
                <div><span class="pertemuan-eyebrow">{{ \App\Models\Pertemuan::JENIS[$sesi->jenis] ?? $sesi->jenis }} · Sesi
                        {{ $sesi->nomor }}</span>
                    <h2>{{ $sesi->topik }}</h2>
                    <p class="pertemuan-time">
                        {{ $sesi->mulai_rencana->setTimezone($zona)->format('d-m-Y H:i') }}–{{ $sesi->selesai_rencana->setTimezone($zona)->format('H:i') }}
                    </p>
                    <p>{{ $zona }}</p>
                </div><span
                    class="badge pertemuan-status-{{ $sesi->status }}">{{ \App\Models\Pertemuan::STATUS[$sesi->status] ?? $sesi->status }}</span>
            </div>
            <dl class="detail-grid">
                <div>
                    <dt>Penanggung jawab</dt>
                    <dd>{{ $pengajar['nama'] ?? '—' }} · {{ $pengajar['kode_dosen'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Peran</dt>
                    <dd>{{ $pengajar['peran'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Metode</dt>
                    <dd>{{ \App\Models\JadwalKuliah::METODE[$sesi->metode] ?? $sesi->metode }}</dd>
                </div>
                <div>
                    <dt>Lokasi</dt>
                    <dd>{{ $sesi->lokasi ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Mulai aktual</dt>
                    <dd>{{ $sesi->mulai_aktual?->setTimezone($zona)->format('d-m-Y H:i:s') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Selesai aktual</dt>
                    <dd>{{ $sesi->selesai_aktual?->setTimezone($zona)->format('d-m-Y H:i:s') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Dibatalkan</dt>
                    <dd>{{ $sesi->dibatalkan_at?->setTimezone($zona)->format('d-m-Y H:i:s') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Pola sumber</dt>
                    <dd>{{ $sumber['id'] ?? 'Pertemuan tambahan' }}{{ isset($sumber['hari']) ? ' · ' . (\App\Models\JadwalKuliah::HARI[(int) $sumber['hari']] ?? '') : '' }}
                    </dd>
                </div>
            </dl>
            @if ($sesi->rencana)
                <div class="pertemuan-text-block">
                    <h3>Rencana pembelajaran</h3>
                    <p class="detail-multiline">{{ $sesi->rencana }}</p>
                </div>
            @endif
            @if ($sesi->realisasi)
                <div class="pertemuan-text-block">
                    <h3>Realisasi</h3>
                    <p class="detail-multiline">{{ $sesi->realisasi }}</p>
                </div>
            @endif
            <div class="pertemuan-link-panel"><strong>Tautan pertemuan</strong>
                @if ($tautan !== null && \App\Rules\TautanPertemuanAman::sesuai($tautan))
                    <a class="button secondary small" href="{{ $tautan }}" target="_blank" rel="noopener noreferrer"
                        referrerpolicy="no-referrer">Buka tautan</a>
                @elseif($tautan !== null)
                    <span class="help">Tautan tersimpan tidak lagi memenuhi format yang diizinkan; perbarui
                    rencana.</span>@else<span class="help">Tidak ada tautan.</span>
                @endif
            </div>
            <p class="pertemuan-note">Sesi ini menyimpan snapshot sumber dan pengajar. Perubahan jadwal/tim berikutnya tidak
                mengubah catatan historis.</p>
        </div>
    </section>
    <section class="card pertemuan-section">
        <div class="card-header">
            <h2>Aksi pelaksanaan</h2>
        </div>
        <div class="panel-body">@include('admin.pertemuan._aksi')</div>
    </section>
    <section class="card pertemuan-section">
        <div class="card-header">
            <h2>Informasi kelas</h2>
        </div>
        <div class="panel-body">@include('admin.pertemuan._kelas')</div>
    </section>
    <section class="card pertemuan-section">
        <div class="card-header">
            <h2>Seluruh sesi kelas</h2>
        </div>@include('admin.pertemuan._daftar-sesi')
    </section>
    <section class="card pertemuan-section">
        <div class="card-header">
            <h2>Riwayat perubahan</h2>
        </div>
        <div class="panel-body">@include('admin.pertemuan._audit')</div>
    </section>
@endsection
