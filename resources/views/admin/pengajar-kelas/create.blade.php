@extends('layouts.siakad')
@section('title', 'Tambah Pengajar Kelas')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Pengajar Kelas</h1>
            <p class="subtitle">Pilih kelas, periksa tim, lalu tentukan dosen dan perannya.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.pengajar-kelas.index') }}">Daftar penugasan</a>
    </div>

    <section class="card">
        <div class="card-header">
            <h2>Pilih kelas</h2>
        </div>
        <div class="panel-body">
            <form class="pengajar-filters pengajar-filters-ringkas" method="GET"
                action="{{ route('admin.pengajar-kelas.create') }}">
                <div class="field">
                    <label for="q">Kelas, mata kuliah, atau rombel</label>
                    <input type="search" name="q" id="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Cari kelas">
                </div>
                <div class="field">
                    <label for="periode_id">Periode</label>
                    <select name="periode_id" id="periode_id">
                        <option value="">Semua periode terbuka</option>
                        @foreach ($daftarPeriode as $periode)
                            <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>{{ $periode->kode }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="actions">
                    <button class="button" type="submit">Cari</button>
                    <a class="button secondary" href="{{ route('admin.pengajar-kelas.create') }}">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table class="pengajar-table">
                <thead>
                    <tr>
                        <th scope="col">Kelas / mata kuliah</th>
                        <th scope="col">Periode / rombel</th>
                        <th scope="col">Status kelas</th>
                        <th scope="col">Penugasan aktif</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($calonKelas as $calon)
                        <tr>
                            <td><strong>{{ $calon->kode }}</strong><br>{{ $calon->nama_mk_snapshot }}</td>
                            <td>{{ $calon->rombel->periodeAkademik->kode }}<br>{{ $calon->rombel->kode }}</td>
                            <td>{{ \App\Models\KelasKuliah::STATUS[$calon->status] }}</td>
                            <td>{{ $calon->pengajar_aktif_count }} dosen</td>
                            <td>
                                <a class="button small"
                                    href="{{ route('admin.pengajar-kelas.create', [
                                        'kelas_id' => $calon->id,
                                        'q' => $filter['q'] ?? null,
                                        'periode_id' => $filter['periode_id'] ?? null,
                                    ]) . '#kelas-terpilih' }}">Pilih
                                    kelas</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Tidak ada kelas persiapan/aktif pada periode terbuka sesuai pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-body"><x-pagination :paginator="$calonKelas" /></div>
    </section>

    @if ($kelas)
        <section class="card pengajar-section" id="kelas-terpilih">
            <div class="card-header">
                <h2>Kelas terpilih</h2>
            </div>
            <div class="panel-body">@include('admin.pengajar-kelas._kelas')</div>
        </section>

        <section class="card pengajar-section">
            <div class="card-header">
                <h2>Tim pengajar saat ini</h2>
            </div>
            @include('admin.pengajar-kelas._tim')
        </section>

        <section class="card pengajar-section">
            <div class="card-header">
                <h2>Penugasan baru</h2>
            </div>
            <div class="panel-body">
                @unless ($bolehSimpan)
                    <div class="alert alert-error" role="alert">
                        @if ($dosenPilihan->isEmpty())
                            Belum ada dosen yang dapat ditambahkan. Periksa data dosen, akun dan role, atau gunakan penugasan
                            lama pada tim di atas.
                        @else
                            Periksa status kelas/periode serta keaktifan program studi dan kurikulumnya. Penugasan baru hanya
                            tersedia pada kelas persiapan/aktif dengan sumber akademik yang sesuai.
                        @endif
                    </div>
                @endunless
                <form method="POST" action="{{ route('admin.pengajar-kelas.store') }}">
                    @include('admin.pengajar-kelas._form')
                </form>
            </div>
        </section>
    @endif
@endsection
