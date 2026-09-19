@extends('layouts.siakad')

@section('title', 'Edit Nama Peran')

@section('breadcrumb')
    <a href="{{ route('admin.roles.index') }}">Peran pengguna</a>
    <span aria-hidden="true"> / </span>

    <a href="{{ route('admin.roles.show', $role) }}">
        {{ $role->nama }}
    </a>

    <span aria-hidden="true"> / </span>
    <span aria-current="page">Edit</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Edit nama peran</h1>
            <p class="subtitle">
                Perubahan nama tidak mengubah hak akses peran.
            </p>
        </div>
    </div>

    <section class="card form-card" aria-label="Formulir nama peran">
        @error('version')
            <p>
                <a href="{{ route('admin.roles.edit', $role) }}">
                    Muat ulang data terbaru
                </a>
            </p>
        @enderror

        <form
            action="{{ route('admin.roles.update', $role) }}"
            method="POST"
        >
            @csrf
            @method('PATCH')

            <input
                type="hidden"
                name="version"
                value="{{ old('version', $version) }}"
            >

            <div class="field">
                <label for="kode">Kode peran</label>

                <input
                    type="text"
                    id="kode"
                    value="{{ $role->kode }}"
                    readonly
                    aria-describedby="kode-help"
                >

                <p id="kode-help" class="help">
                    Kode merupakan identitas tetap dan tidak dapat diubah.
                </p>
            </div>

            <div class="field">
                <label for="nama">Nama peran</label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="{{ old('nama', $role->nama) }}"
                    minlength="3"
                    maxlength="80"
                    required
                    autocomplete="off"
                    aria-invalid="{{ $errors->has('nama') ? 'true' : 'false' }}"
                    aria-describedby="nama-help{{ $errors->has('nama') ? ' nama-error' : '' }}"
                >

                <p id="nama-help" class="help">
                    Gunakan 3–80 karakter.
                </p>

                @error('nama')
                    <p id="nama-error" class="field-error">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="button">
                    Simpan perubahan
                </button>

                <a
                    href="{{ route('admin.roles.show', $role) }}"
                    class="button button-secondary"
                >
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection