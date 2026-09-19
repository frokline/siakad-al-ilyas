@extends('layouts.siakad')

@section('title', 'Detail Periode Akademik')

@section('breadcrumb')
    <a href="{{ route('admin.periode-akademik.index') }}">Periode Akademik</a>
    <span> / {{ $periodeAkademik->kode }}</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $periodeAkademik->kode }}</h1>
            <p class="subtitle">
                Tahun ajaran {{ $periodeAkademik->tahunAjaran() }}
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('admin.periode-akademik.edit', $periodeAkademik) }}" class="button">
                Edit Periode
            </a>

            <a href="{{ route('admin.periode-akademik.index') }}" class="button button-secondary">
                Kembali
            </a>
        </div>
    </div>

    <section class="card panel-body">
        <dl class="detail-grid">
            <dt>Kode</dt>
            <dd>{{ $periodeAkademik->kode }}</dd>

            <dt>Tahun ajaran</dt>
            <dd>{{ $periodeAkademik->tahunAjaran() }}</dd>

            <dt>Jenis semester</dt>
            <dd>{{ $jenisOptions[$periodeAkademik->jenis] }}</dd>

            <dt>Mulai perkuliahan</dt>
            <dd>{{ $periodeAkademik->mulai->format('d/m/Y') }}</dd>

            <dt>Selesai perkuliahan</dt>
            <dd>{{ $periodeAkademik->selesai->format('d/m/Y') }}</dd>

            <dt>Status periode</dt>
            <dd>
                <span class="badge" data-periode-status="{{ $periodeAkademik->status }}">
                    {{ $statusOptions[$periodeAkademik->status] }}
                </span>
            </dd>

            <dt>Zona waktu jadwal</dt>
            <dd>{{ config('siakad.timezone') }}</dd>

            <dt>Awal pengisian KRS</dt>
            <dd>
                {{ $periodeAkademik->krs_mulai?->setTimezone(config('siakad.timezone'))->format('d/m/Y H:i') ?? 'Belum diatur' }}
            </dd>

            <dt>Batas akhir pengisian KRS</dt>
            <dd>
                {{ $periodeAkademik->krs_selesai?->setTimezone(config('siakad.timezone'))->format('d/m/Y H:i') ?? 'Belum diatur' }}
            </dd>

            <dt>KRS saat ini</dt>
            <dd>
                @if ($periodeAkademik->isKrsOpen())
                    <span class="badge badge-active">Terbuka</span>
                @else
                    <span class="badge badge-inactive">Tertutup</span>
                @endif
            </dd>
        </dl>
    </section>
@endsection
