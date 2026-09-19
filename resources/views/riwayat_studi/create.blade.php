@extends('layouts.siakad')

@section('title', 'Tambah Riwayat Studi')

@section('content')
    @php
        $siap = $daftarMahasiswa->isNotEmpty() && $daftarKurikulum->isNotEmpty() && $daftarPeriode->isNotEmpty();
    @endphp

    <div class="page-heading">
        <div>
            <h1>Tambah Riwayat Studi</h1>
            <p class="subtitle">Riwayat baru langsung berstatus aktif.</p>
        </div>

        <a class="button secondary" href="{{ route('admin.riwayat-studi.index') }}">
            Kembali
        </a>
    </div>

    @if ($daftarMahasiswa->isEmpty())
        <div class="alert alert-error" role="alert">
            Belum ada mahasiswa yang memenuhi syarat.
            Periksa akun, peran Mahasiswa, dan riwayat aktif sebelumnya.

            <a href="{{ route('admin.mahasiswa.index') }}">Data mahasiswa</a>
        </div>
    @endif

    @if ($daftarKurikulum->isEmpty())
        <div class="alert alert-error" role="alert">
            Belum ada kurikulum aktif pada program studi aktif.

            <a href="{{ route('admin.kurikulum.index') }}">Data kurikulum</a>
        </div>
    @endif

    @if ($daftarPeriode->isEmpty())
        <div class="alert alert-error" role="alert">
            Buat periode akademik terlebih dahulu.

            <a href="{{ route('admin.periode-akademik.index') }}">
                Data periode akademik
            </a>
        </div>
    @endif

    <section class="card form-card">
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.riwayat-studi.store') }}">
                @csrf

                @include('riwayat_studi._form')

                <div class="actions">
                    <button class="button" type="submit" @disabled(!$siap)>
                        Simpan Riwayat
                    </button>

                    <a class="button secondary" href="{{ route('admin.riwayat-studi.index') }}">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection
