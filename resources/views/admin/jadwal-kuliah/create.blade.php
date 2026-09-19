@extends('layouts.siakad')
@section('title', 'Tambah Jadwal Kuliah')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Jadwal Kuliah</h1>
            <p class="subtitle">Pilih kelas, kemudian susun pola mingguan.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.jadwal-kuliah.index') }}">Daftar jadwal</a>
    </div>
    @if ($kelas === null)
        <section class="card">
            <div class="card-header">
                <h2>Pilih kelas kuliah</h2>
            </div>
            <div class="panel-body">
                <form method="GET" action="{{ route('admin.jadwal-kuliah.create') }}" class="jadwal-picker-filters">
                    <div class="field">
                        <label for="q">Kode kelas, mata kuliah, atau rombel</label>
                        <input id="q" name="q" type="search" maxlength="80" value="{{ $filter['q'] ?? '' }}">
                    </div>
                    <div class="field">
                        <label for="periode_id">Periode</label>
                        <select id="periode_id" name="periode_id">
                            <option value="">Semua periode terbuka</option>
                            @foreach ($daftarPeriode as $periode)
                                <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>{{ $periode->kode }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="actions jadwal-filter-actions">
                        <button class="button" type="submit">Cari kelas</button>
                        <a class="button secondary" href="{{ route('admin.jadwal-kuliah.create') }}">Reset</a>
                    </div>
                </form>
            </div>
            <div class="table-wrap">
                <table class="jadwal-table">
                    <caption class="jadwal-sr-only">Kelas yang dapat disusun jadwalnya</caption>
                    <thead>
                        <tr>
                            <th scope="col">Kelas</th>
                            <th scope="col">Rombel / periode</th>
                            <th scope="col">Pola aktif</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daftarKelas as $pilihan)
                            <tr>
                                <td><strong>{{ $pilihan->kode }}</strong><span
                                        class="jadwal-sub">{{ $pilihan->nama_mk_snapshot }}</span></td>
                                <td>{{ $pilihan->rombel->kode }}<span
                                        class="jadwal-sub">{{ $pilihan->rombel->periodeAkademik->kode }}</span></td>
                                <td>{{ $pilihan->jumlah_jadwal_aktif }}</td>
                                <td><a class="button small"
                                        href="{{ route('admin.jadwal-kuliah.create', ['kelas_id' => $pilihan->id]) }}">Pilih
                                        kelas</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="jadwal-empty">Belum ada kelas terbuka yang sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="panel-body"><x-pagination :paginator="$daftarKelas" /></div>
        </section>
    @else
        <section class="card jadwal-section">
            <div class="card-header jadwal-toolbar">
                <h2>Kelas terpilih</h2><a href="{{ route('admin.jadwal-kuliah.create') }}">Ganti kelas</a>
            </div>
            <div class="panel-body">@include('admin.jadwal-kuliah._kelas')</div>
        </section>
        <section class="card jadwal-section">
            <div class="card-header">
                <h2>Pola baru</h2>
            </div>
            <div class="panel-body">
                @unless ($boleh)
                    <div class="alert alert-error" role="alert">Kelas/periode harus terbuka, prodi dan kurikulum aktif, serta
                        paket sudah diterbitkan.</div>
                @endunless
                <form method="POST" action="{{ route('admin.jadwal-kuliah.store') }}">
                    @include('admin.jadwal-kuliah._form')
                </form>
            </div>
        </section>
        <section class="card jadwal-section">
            <div class="card-header">
                <h2>Pola kelas yang sudah tersedia</h2>
            </div>
            @include('admin.jadwal-kuliah._pola')
        </section>
    @endif
@endsection
