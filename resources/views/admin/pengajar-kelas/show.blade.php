@extends('layouts.siakad')
@section('title', 'Detail Penugasan Dosen')

@section('content')
    @php
        $zona = config('siakad.timezone', 'Asia/Makassar');
        $profilSiap =
            $penugasan->dosen->status === \App\Models\Dosen::AKTIF &&
            $penugasan->dosen->user->isAktif() &&
            $penugasan->dosen->user->roles->contains('kode', \App\Models\Role::DOSEN);
    @endphp
    <div class="page-heading">
        <div>
            <h1>Penugasan #{{ $penugasan->id }}</h1>
            <p class="subtitle">{{ $penugasan->dosen->kode_dosen }} — {{ $penugasan->dosen->user->nama }}</p>
        </div>
        <div class="actions">
            @if ($penugasan->dapatDiubah())
                <a class="button" href="{{ route('admin.pengajar-kelas.edit', $penugasan) }}">Edit penugasan</a>
                <a class="button secondary"
                    href="{{ route('admin.pengajar-kelas.create', ['kelas_id' => $kelas->id]) }}">Tambah dosen kelas ini</a>
            @endif
            <a class="button secondary" href="{{ route('admin.pengajar-kelas.index') }}">Daftar penugasan</a>
        </div>
    </div>

    <section class="card">
        <div class="card-header">
            <h2>Identitas kelas</h2>
        </div>
        <div class="panel-body">@include('admin.pengajar-kelas._kelas')</div>
    </section>

    <section class="card pengajar-section">
        <div class="card-header">
            <h2>Penugasan dosen</h2>
            <span class="badge"
                data-pengajar-aktif="{{ $penugasan->aktif ? '1' : '0' }}">{{ $penugasan->aktif ? 'Aktif' : 'Nonaktif' }}</span>
        </div>
        <div class="panel-body">
            <dl class="detail-grid">
                <div>
                    <dt>Dosen</dt>
                    <dd><a
                            href="{{ route('admin.dosen.show', $penugasan->dosen) }}">{{ $penugasan->dosen->user->nama }}</a>{{ $penugasan->dosen->gelar ? ', ' . $penugasan->dosen->gelar : '' }}
                    </dd>
                </div>
                <div>
                    <dt>Peran</dt>
                    <dd>{{ \App\Models\PengajarKelas::PERAN[$penugasan->peran] }}</dd>
                </div>
                <div>
                    <dt>Revisi</dt>
                    <dd>{{ $penugasan->revisi }}</dd>
                </div>
                <div>
                    <dt>Status profil dosen</dt>
                    <dd>{{ $penugasan->dosen->status === \App\Models\Dosen::AKTIF ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                <div>
                    <dt>Aktivasi terakhir</dt>
                    <dd>{{ $penugasan->diaktifkan_at->timezone($zona)->format('d-m-Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt>Penonaktifan terakhir</dt>
                    <dd>{{ $penugasan->dinonaktifkan_at?->timezone($zona)->format('d-m-Y H:i:s') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Dibuat</dt>
                    <dd>{{ $penugasan->created_at->timezone($zona)->format('d-m-Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt>Diperbarui</dt>
                    <dd>{{ $penugasan->updated_at->timezone($zona)->format('d-m-Y H:i:s') }}</dd>
                </div>
            </dl>
            @unless ($profilSiap)
                <p class="pengajar-note">Profil/akun/role dosen belum memenuhi syarat mengajar. Periksa data dosen atau tetapkan
                    pengganti sesuai kebutuhan kelas.</p>
            @endunless
            <p class="help">Akses pembelajaran mengikuti penugasan aktif, kelayakan akun dosen, serta kelas dan periode
                yang aktif. Riwayat penugasan tetap tersedia setelah kelas selesai.</p>
        </div>
    </section>

    <section class="card pengajar-section">
        <div class="card-header">
            <h2>Seluruh tim kelas ini</h2>
        </div>
        @include('admin.pengajar-kelas._tim')
    </section>

    <section class="card pengajar-section">
        <div class="card-header">
            <h2>Riwayat penugasan</h2>
        </div>
        <div class="panel-body">
            <p class="help">Perubahan terbaru ditampilkan terlebih dahulu. Waktu menggunakan {{ $zona }}.</p>
            @include('admin.pengajar-kelas._audit')
        </div>
    </section>
@endsection
