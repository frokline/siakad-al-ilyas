@extends('layouts.siakad')

@section('title', 'Detail Peran')

@section('breadcrumb')
    <a href="{{ route('admin.roles.index') }}">Peran pengguna</a>
    <span aria-hidden="true"> / </span>
    <span aria-current="page">Detail</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $role->nama }}</h1>
            <p class="subtitle">Informasi peran pengguna.</p>
        </div>

        <div class="actions">
            <a
                href="{{ route('admin.roles.index') }}"
                class="button button-secondary"
            >
                Kembali
            </a>

            @can('kelola-peran')
                <a
                    href="{{ route('admin.roles.edit', $role) }}"
                    class="button"
                >
                    Edit nama
                </a>
            @endcan
        </div>
    </div>

    <section class="card" aria-label="Informasi peran">
        <dl class="detail-grid">
            <dt>Nama peran</dt>
            <dd>{{ $role->nama }}</dd>

            <dt>Kode peran</dt>
            <dd><code>{{ $role->kode }}</code></dd>

            <dt>Dibuat</dt>
            <dd>
                {{ $role->created_at
                    ?->timezone(config('app.timezone'))
                    ->format('d/m/Y H:i') ?? '—' }}
            </dd>

            <dt>Diperbarui</dt>
            <dd>
                {{ $role->updated_at
                    ?->timezone(config('app.timezone'))
                    ->format('d/m/Y H:i') ?? '—' }}
            </dd>
        </dl>
    </section>
@endsection