@extends('layouts.siakad')

@section('title', 'Detail Program Studi')

@section('breadcrumb')
    <a href="{{ route('admin.program-studi.index') }}">Program Studi</a>
    <span> / {{ $programStudi->kode }}</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $programStudi->nama }}</h1>
            <p class="subtitle">Detail program studi.</p>
        </div>

        <div class="actions">
            <a href="{{ route('admin.program-studi.edit', $programStudi) }}" class="button">
                Edit Program Studi
            </a>

            <a href="{{ route('admin.program-studi.index') }}" class="button button-secondary">
                Kembali
            </a>
        </div>
    </div>

    <section class="card panel-body">
        <dl class="detail-grid">
            <dt>Kode</dt>
            <dd>{{ $programStudi->kode }}</dd>

            <dt>Nama program studi</dt>
            <dd>{{ $programStudi->nama }}</dd>

            <dt>Jenjang / program</dt>
            <dd>{{ $programStudi->jenjang }}</dd>

            <dt>Status</dt>
            <dd>
                <span class="badge {{ $programStudi->aktif ? 'badge-active' : 'badge-inactive' }}">
                    {{ $programStudi->aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
            </dd>

            <dt>Dibuat</dt>
            <dd>{{ $programStudi->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>

            <dt>Diperbarui</dt>
            <dd>{{ $programStudi->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
        </dl>

        <p class="help">
            Program studi dapat dinonaktifkan melalui halaman Edit.
            Datanya tetap tersimpan.
        </p>
    </section>
@endsection
