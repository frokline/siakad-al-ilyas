@extends('layouts.siakad')

@section('title', 'Detail Mata Kuliah')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $mataKuliah->nama }}</h1>
            <p class="subtitle">Detail mata kuliah {{ $mataKuliah->kode }}.</p>
        </div>

        <div class="actions">
            <a class="button" href="{{ route('admin.mata-kuliah.edit', $mataKuliah) }}">
                Edit Mata Kuliah
            </a>

            <a class="button button-secondary" href="{{ route('admin.mata-kuliah.index') }}">
                Kembali
            </a>
        </div>
    </div>

    <div class="card panel-body">
        <dl class="detail-grid">
            <div>
                <dt>Kode mata kuliah</dt>
                <dd>{{ $mataKuliah->kode }}</dd>
            </div>

            <div>
                <dt>Nama mata kuliah</dt>
                <dd>{{ $mataKuliah->nama }}</dd>
            </div>

            <div>
                <dt>Program studi</dt>
                <dd>
                    <a href="{{ route('admin.program-studi.show', $mataKuliah->programStudi) }}">
                        {{ $mataKuliah->programStudi->kode }} —
                        {{ $mataKuliah->programStudi->nama }}
                    </a>
                </dd>
            </div>

            <div>
                <dt>Status program studi</dt>
                <dd>
                    <span class="badge {{ $mataKuliah->programStudi->aktif ? 'badge-active' : 'badge-inactive' }}">
                        {{ $mataKuliah->programStudi->aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>
            </div>

            <div>
                <dt>Status mata kuliah</dt>
                <dd>
                    <span class="badge {{ $mataKuliah->aktif ? 'badge-active' : 'badge-inactive' }}">
                        {{ $mataKuliah->aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>
@endsection
