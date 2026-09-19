@extends('layouts.siakad')

@section('title', 'Detail Kurikulum')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $kurikulum->nama }}</h1>
            <p class="subtitle">Detail kurikulum {{ $kurikulum->kode }}.</p>
        </div>

        <div class="actions">
            <a class="button" href="{{ route('admin.kurikulum.edit', $kurikulum) }}">
                Edit Kurikulum
            </a>

            <a class="button button-secondary" href="{{ route('admin.kurikulum.index') }}">
                Kembali
            </a>
            <a class="button button-secondary" href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}">
                Susunan Mata Kuliah
            </a>
        </div>
    </div>

    <div class="card panel-body">
        <dl class="detail-grid">
            <div>
                <dt>Kode kurikulum</dt>
                <dd>{{ $kurikulum->kode }}</dd>
            </div>

            <div>
                <dt>Nama kurikulum</dt>
                <dd>{{ $kurikulum->nama }}</dd>
            </div>

            <div>
                <dt>Program studi</dt>
                <dd>
                    <a href="{{ route('admin.program-studi.show', $kurikulum->programStudi) }}">
                        {{ $kurikulum->programStudi->kode }} —
                        {{ $kurikulum->programStudi->nama }}
                    </a>
                </dd>
            </div>

            <div>
                <dt>Status program studi</dt>
                <dd>
                    <span class="badge {{ $kurikulum->programStudi->aktif ? 'badge-active' : 'badge-inactive' }}">
                        {{ $kurikulum->programStudi->aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>
            </div>

            <div>
                <dt>Tahun berlaku</dt>
                <dd>{{ $kurikulum->tahun_berlaku }}</dd>
            </div>

            <div>
                <dt>Status kurikulum</dt>
                <dd>
                    <span class="badge" data-kurikulum-status="{{ $kurikulum->status }}">
                        {{ $statusOptions[$kurikulum->status] }}
                    </span>
                </dd>
            </div>

            <div>
                <dt>Perubahan identitas</dt>
                <dd>
                    {{ $kurikulum->identitasDapatDiubah()
                        ? 'Dapat diedit selama berstatus draf'
                        : 'Terkunci; perubahan menggunakan versi baru' }}
                </dd>
            </div>
        </dl>
    </div>
@endsection
