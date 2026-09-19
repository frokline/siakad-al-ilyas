@extends('layouts.siakad')
@section('title', 'Detail Jadwal Kuliah')
@section('content')
    @php
        $tokenLama = old('versi_jadwal');
        $usang = $tokenLama !== null && (!is_string($tokenLama) || !hash_equals($versi, $tokenLama));
        $alasanLama = old('alasan', '');
        $tautan = $jadwal->tautan_pertemuan;
        $tanggalPertama = $jadwal->tanggalPertama();
    @endphp
    <div class="page-heading">
        <div>
            <h1>Jadwal #{{ $jadwal->id }}</h1>
            <p class="subtitle">{{ $kelas->kode }} · Revisi {{ $jadwal->revisi }}</p>
        </div>
        <div class="actions">
            @if ($jadwal->dapatDiubah())
                <a class="button" href="{{ route('admin.jadwal-kuliah.edit', $jadwal) }}">Edit jadwal</a>
                <a class="button secondary"
                    href="{{ route('admin.jadwal-kuliah.create', ['kelas_id' => $kelas->id]) }}">Tambah pola</a>
            @endif
            <a class="button secondary" href="{{ route('admin.jadwal-kuliah.index', ['kelas_id' => $kelas->id]) }}">Daftar
                kelas ini</a>
        </div>
    </div>
    <section class="card jadwal-section">
        <div class="panel-body">
            <div class="jadwal-hero">
                <div>
                    <span class="jadwal-eyebrow">Setiap {{ \App\Models\JadwalKuliah::HARI[$jadwal->hari] }}</span>
                    <p class="jadwal-time">{{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}
                    </p>
                    <p>{{ config('siakad.timezone', 'Asia/Makassar') }}</p>
                </div>
                <span
                    class="badge {{ $jadwal->aktif ? 'jadwal-badge-active' : 'jadwal-badge-muted' }}">{{ $jadwal->aktif ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
            <dl class="detail-grid">
                <div>
                    <dt>Rentang berlaku</dt>
                    <dd>{{ $jadwal->berlaku_mulai->format('d-m-Y') }} s.d. {{ $jadwal->berlaku_selesai->format('d-m-Y') }}
                    </dd>
                </div>
                <div>
                    <dt>Tanggal pertama sesuai pola</dt>
                    <dd>{{ $tanggalPertama?->format('d-m-Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Metode</dt>
                    <dd>{{ \App\Models\JadwalKuliah::METODE[$jadwal->metode] }}</dd>
                </div>
                <div>
                    <dt>Lokasi</dt>
                    <dd>{{ $jadwal->lokasi ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Diperbarui</dt>
                    <dd>{{ $jadwal->updated_at->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
                    </dd>
                </div>
                <div>
                    <dt>Dinonaktifkan</dt>
                    <dd>{{ $jadwal->dinonaktifkan_at?->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') ?? '—' }}
                    </dd>
                </div>
            </dl>
            <div class="jadwal-link-panel">
                <strong>Tautan pertemuan</strong>
                @if ($tautan !== null && \App\Rules\TautanPertemuanAman::sesuai($tautan))
                    <a class="button secondary small" href="{{ $tautan }}" target="_blank" rel="noopener noreferrer"
                        referrerpolicy="no-referrer">Buka tautan tersimpan</a>
                @elseif($tautan !== null)
                    <p class="help">Tautan tersimpan tidak memenuhi format yang diizinkan. Perbarui melalui formulir edit.
                    </p>
                @else
                    <p class="help">Belum ada tautan pertemuan.</p>
                @endif
            </div>
            <p class="help">Pola mingguan adalah rencana. Realisasi pertemuan, hari libur, dan presensi ditangani pada
                modul berikutnya.</p>
        </div>
    </section>
    <section class="card jadwal-section">
        <div class="card-header">
            <h2>Kelas dan tim pengajar</h2>
        </div>
        <div class="panel-body">@include('admin.jadwal-kuliah._kelas')</div>
    </section>
    <section class="card jadwal-section">
        <div class="card-header">
            <h2>Seluruh pola kelas</h2>
        </div>
        @include('admin.jadwal-kuliah._pola')
    </section>
    @if ($jadwal->aktif)
        <section class="card jadwal-section jadwal-danger-zone">
            <div class="card-header">
                <h2>Nonaktifkan pola</h2>
            </div>
            <div class="panel-body">
                <p>Pemesanan waktu rombel dan dosen untuk pola ini akan dilepaskan. Data pola dan audit tetap tersimpan.</p>
                @if ($usang)
                    <div class="alert alert-error" role="alert">Data telah berubah. <a
                            href="{{ route('admin.jadwal-kuliah.show', $jadwal) }}">Muat ulang halaman</a> sebelum
                        menonaktifkan.</div>
                @endif
                <form method="POST" action="{{ route('admin.jadwal-kuliah.nonaktifkan', $jadwal) }}">
                    @csrf
                    <input type="hidden" name="versi_jadwal" value="{{ $versi }}">
                    <fieldset class="jadwal-fieldset" @disabled($usang)>
                        <legend class="jadwal-sr-only">Konfirmasi penonaktifan pola</legend>
                        <div class="field">
                            <label for="alasan_nonaktif">Alasan penonaktifan <span aria-hidden="true">*</span></label>
                            <textarea id="alasan_nonaktif" name="alasan" rows="3" minlength="10" maxlength="2000" required>{{ is_string($alasanLama) ? $alasanLama : '' }}</textarea>
                            @error('alasan')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <label class="jadwal-confirm" for="konfirmasi_nonaktif">
                            <input id="konfirmasi_nonaktif" name="konfirmasi" type="checkbox" value="1" required>
                            <span>Saya menyetujui penonaktifan pola jadwal ini.</span>
                        </label>
                        @error('konfirmasi')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                        <button class="button danger jadwal-actions" type="submit">Nonaktifkan jadwal</button>
                    </fieldset>
                </form>
            </div>
        </section>
    @endif
    <section class="card jadwal-section">
        <div class="card-header">
            <h2>Riwayat perubahan</h2>
        </div>
        <div class="panel-body">@include('admin.jadwal-kuliah._audit')</div>
    </section>
@endsection
