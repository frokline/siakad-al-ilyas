@extends('layouts.siakad')

@section('title', 'Detail Riwayat Studi')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Detail Riwayat Studi</h1>

            <p class="subtitle">
                {{ $riwayatStudi->mahasiswa->nim }}
                — {{ $riwayatStudi->mahasiswa->user->nama }}
            </p>
        </div>

        <div class="actions">
            @if ($riwayatStudi->isAktif())
                <a class="button" href="{{ route('admin.riwayat-studi.edit', $riwayatStudi) }}">
                    Edit Riwayat
                </a>
            @endif

            <a class="button secondary" href="{{ route('admin.riwayat-studi.index') }}">
                Kembali
            </a>
        </div>
    </div>

    <section class="card">
        <div class="panel-body">
            <dl class="detail-grid">
                <div>
                    <dt>NIM</dt>
                    <dd>
                        <a href="{{ route('admin.mahasiswa.show', $riwayatStudi->mahasiswa) }}">
                            {{ $riwayatStudi->mahasiswa->nim }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt>Nama mahasiswa</dt>
                    <dd>{{ $riwayatStudi->mahasiswa->user->nama }}</dd>
                </div>

                <div>
                    <dt>Program studi</dt>
                    <dd>
                        <a href="{{ route('admin.program-studi.show', $riwayatStudi->kurikulum->programStudi) }}">
                            {{ $riwayatStudi->kurikulum->programStudi->nama }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt>Kurikulum</dt>
                    <dd>
                        <a href="{{ route('admin.kurikulum.show', $riwayatStudi->kurikulum) }}">
                            {{ $riwayatStudi->kurikulum->kode }}
                            — {{ $riwayatStudi->kurikulum->nama }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt>Angkatan</dt>
                    <dd>{{ $riwayatStudi->angkatan }}</dd>
                </div>

                <div>
                    <dt>Status studi</dt>
                    <dd>
                        <span @class([
                            'badge',
                            'badge-active' => $riwayatStudi->isAktif(),
                            'badge-inactive' => !$riwayatStudi->isAktif(),
                        ])>
                            {{ $statusOptions[$riwayatStudi->status] }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt>Periode mulai</dt>
                    <dd>
                        <a href="{{ route('admin.periode-akademik.show', $riwayatStudi->periodeMulai) }}">
                            {{ $riwayatStudi->periodeMulai->kode }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt>Periode akhir</dt>
                    <dd>
                        @if ($riwayatStudi->periodeAkhir)
                            <a href="{{ route('admin.periode-akademik.show', $riwayatStudi->periodeAkhir) }}">
                                {{ $riwayatStudi->periodeAkhir->kode }}
                            </a>
                        @else
                            Belum berakhir
                        @endif
                    </dd>
                </div>

                <div>
                    <dt>Dosen pembimbing akademik</dt>
                    <dd>
                        @if ($riwayatStudi->dosenPa)
                            <a href="{{ route('admin.dosen.show', $riwayatStudi->dosenPa) }}">
                                {{ $riwayatStudi->dosenPa->kode_dosen }}
                                — {{ $riwayatStudi->dosenPa->user->nama }}
                            </a>
                        @else
                            Belum ditentukan
                        @endif
                    </dd>
                </div>

                <div>
                    <dt>Status akun mahasiswa</dt>
                    <dd>
                        <span @class([
                            'badge',
                            'badge-active' => $riwayatStudi->mahasiswa->user->isAktif(),
                            'badge-inactive' => !$riwayatStudi->mahasiswa->user->isAktif(),
                        ])>
                            {{ $riwayatStudi->mahasiswa->user->isAktif() ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </dd>
                </div>
            </dl>

            @if (!$riwayatStudi->isAktif())
                <p class="help">
                    Riwayat ini sudah ditutup dan dipertahankan sebagai
                    catatan akademik.
                </p>
            @endif
        </div>
    </section>
@endsection
