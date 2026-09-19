@extends('layouts.siakad')

@section('title', 'Detail Rombel')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Rombel {{ $rombel->kode }}</h1>

            <p class="subtitle">
                {{ $rombel->periodeAkademik->kode }}
                — {{ $rombel->paketSemester->kurikulum->programStudi->nama }}
            </p>
        </div>

        <div class="actions">
            @if ($rombel->dapatDiubah())
                <a class="button" href="{{ route('admin.rombel.edit', $rombel) }}">
                    Edit Rombel
                </a>
            @endif

            <a class="button secondary" href="{{ route('admin.rombel.index') }}">
                Kembali
            </a>
        </div>
    </div>

    <section class="card">
        <div class="panel-body">
            <dl class="detail-grid">
                <div>
                    <dt>Kode rombel</dt>
                    <dd>{{ $rombel->kode }}</dd>
                </div>

                <div>
                    <dt>Periode akademik</dt>
                    <dd>
                        <a href="{{ route('admin.periode-akademik.show', $rombel->periodeAkademik) }}">
                            {{ $rombel->periodeAkademik->kode }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt>Status periode</dt>
                    <dd>
                        <span class="badge" data-periode-status="{{ $rombel->periodeAkademik->status }}">
                            {{ $statusPeriodeOptions[$rombel->periodeAkademik->status] }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt>Program studi</dt>
                    <dd>
                        <a href="{{ route('admin.program-studi.show', $rombel->paketSemester->kurikulum->programStudi) }}">
                            {{ $rombel->paketSemester->kurikulum->programStudi->nama }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt>Kurikulum</dt>
                    <dd>
                        <a href="{{ route('admin.kurikulum.show', $rombel->paketSemester->kurikulum) }}">
                            {{ $rombel->paketSemester->kurikulum->kode }}
                            — {{ $rombel->paketSemester->kurikulum->nama }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt>Paket semester</dt>
                    <dd>
                        <a href="{{ route('admin.paket-semester.show', $rombel->paketSemester) }}">
                            {{ $rombel->paketSemester->nama }}
                            — Versi {{ $rombel->paketSemester->versi }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt>Status paket</dt>
                    <dd>
                        <span class="badge" data-paket-status="{{ $rombel->paketSemester->status }}">
                            {{ $statusPaketOptions[$rombel->paketSemester->status] }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt>Semester studi</dt>
                    <dd>{{ $rombel->paketSemester->semester_studi }}</dd>
                </div>

                <div>
                    <dt>Kapasitas mahasiswa</dt>
                    <dd>
                        @if ($rombel->kapasitas === null)
                            Tanpa batas kapasitas
                        @else
                            {{ $rombel->kapasitas }} mahasiswa
                        @endif
                    </dd>
                </div>
            </dl>

            @if (!$rombel->dapatDiubah())
                <p class="help">
                    Periode sudah diarsipkan. Data rombel hanya dapat dilihat.
                </p>
            @endif

            @if ($rombel->paketSemester->isArsip())
                <p class="help">
                    Paket telah diarsipkan. Rombel ini tetap menyimpan
                    hubungan dengan versi paket yang digunakan.
                </p>
            @endif

            <div class="actions">
                <a class="button secondary" href="{{ route('admin.paket-semester.show', $rombel->paketSemester) }}">
                    Lihat Isi Paket
                </a>
            </div>
        </div>
    </section>
@endsection
