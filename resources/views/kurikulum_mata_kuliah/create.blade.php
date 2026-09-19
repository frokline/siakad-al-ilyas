@extends('layouts.siakad')

@section('title', 'Tambah Mata Kuliah Kurikulum')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Mata Kuliah Kurikulum</h1>

            <p class="subtitle">
                {{ $kurikulum->kode }} — {{ $kurikulum->nama }}
            </p>
        </div>
    </div>

    @if ($daftarMataKuliah->isEmpty())
        <div class="alert" role="status">
            Tidak ada mata kuliah aktif yang dapat ditambahkan.
            Periksa katalog mata kuliah program studi ini.

            <a
                href="{{ route('admin.mata-kuliah.index', [
                    'program_studi_id' => $kurikulum->program_studi_id,
                ]) }}">
                Buka katalog
            </a>
        </div>
    @endif

    <div class="card form-card panel-body">
        <form method="POST" action="{{ route('admin.kurikulum.mata-kuliah.store', $kurikulum) }}">
            @csrf

            @include('kurikulum_mata_kuliah._form')

            <div class="actions">
                <button class="button" type="submit" @disabled($daftarMataKuliah->isEmpty())>
                    Tambahkan
                </button>

                <a class="button button-secondary" href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
