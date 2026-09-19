@extends('layouts.siakad')

@section('title', 'Detail Mata Kuliah Kurikulum')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $detail->mataKuliah->nama }}</h1>

            <p class="subtitle">
                {{ $kurikulum->kode }} — {{ $kurikulum->nama }}
            </p>
        </div>

        <div class="actions">
            @if ($bisaUbah)
                <a class="button" href="{{ route('admin.kurikulum.mata-kuliah.edit', [$kurikulum, $detail]) }}">
                    Edit Rincian
                </a>
            @endif

            <a class="button button-secondary" href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}">
                Kembali
            </a>
        </div>
    </div>

    @if (!$bisaUbah)
        <div class="alert" role="status">
            Kurikulum aktif/arsip: rincian ini terkunci.
        </div>
    @endif

    <div class="card panel-body">
        <dl class="detail-grid">
            <div>
                <dt>Mata kuliah</dt>
                <dd>
                    <a href="{{ route('admin.mata-kuliah.show', $detail->mataKuliah) }}">
                        {{ $detail->mataKuliah->kode }} —
                        {{ $detail->mataKuliah->nama }}
                    </a>
                </dd>
            </div>

            <div>
                <dt>Program studi</dt>
                <dd>{{ $kurikulum->programStudi->nama }}</dd>
            </div>

            <div>
                <dt>SKS</dt>
                <dd>{{ str_replace('.', ',', $detail->sks) }}</dd>
            </div>

            <div>
                <dt>Semester rekomendasi</dt>
                <dd>{{ $detail->semester_rekomendasi }}</dd>
            </div>

            <div>
                <dt>Sifat mata kuliah</dt>
                <dd>{{ $sifatOptions[$detail->sifat] }}</dd>
            </div>

            <div>
                <dt>Status pada katalog</dt>
                <dd>
                    <span class="badge {{ $detail->mataKuliah->aktif ? 'badge-active' : 'badge-inactive' }}">
                        {{ $detail->mataKuliah->aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>

    @if ($bisaUbah)
        <div class="card panel-body deactivate-panel">
            <h2>Keluarkan dari Kurikulum</h2>

            <p class="help">
                Tindakan ini menghapus penempatan mata kuliah pada draf kurikulum ini.
            </p>

            <form method="POST" action="{{ route('admin.kurikulum.mata-kuliah.destroy', [$kurikulum, $detail]) }}">
                @csrf
                @method('DELETE')

                @php
                    $formVersion = old('version', $version);
                @endphp

                <input type="hidden" name="version" value="{{ is_string($formVersion) ? $formVersion : '' }}">

                @error('version')
                    <p class="field-error" role="alert">{{ $message }}</p>

                    <p class="help">
                        <a href="{{ route('admin.kurikulum.mata-kuliah.show', [$kurikulum, $detail]) }}">
                            Muat ulang data terbaru
                        </a>
                    </p>
                @enderror

                <div class="field">
                    <label class="checkbox-label">
                        <input type="checkbox" name="konfirmasi" value="1" required>
                        Saya ingin mengeluarkan mata kuliah ini dari kurikulum.
                    </label>

                    @error('konfirmasi')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <button class="button button-danger" type="submit">
                    Keluarkan Mata Kuliah
                </button>
            </form>
        </div>
    @endif
@endsection
