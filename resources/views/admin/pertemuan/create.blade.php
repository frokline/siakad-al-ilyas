@extends('layouts.siakad')
@section('title', 'Buat Pertemuan')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Buat Pertemuan</h1>
            <p class="subtitle">Pilih kelas lalu catat rencana sesi.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.pertemuan.index') }}">Daftar pertemuan</a>
    </div>
    @if ($kelas === null)
        <section class="card">
            <div class="card-header">
                <h2>Pilih kelas kuliah</h2>
            </div>
            <div class="panel-body">
                <form method="GET" action="{{ route('admin.pertemuan.create') }}" class="pertemuan-picker-filters">
                    <div class="field"><label for="q">Kode kelas atau mata kuliah</label><input id="q"
                            name="q" type="search" maxlength="80" value="{{ $filter['q'] ?? '' }}"></div>
                    <div class="field"><label for="periode_id">Periode</label><select id="periode_id" name="periode_id">
                            <option value="">Semua periode terbuka</option>
                            @foreach ($daftarPeriode as $periode)
                                <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>{{ $periode->kode }}
                                </option>
                            @endforeach
                        </select></div>
                    <div class="actions pertemuan-filter-actions"><button class="button" type="submit">Cari
                            kelas</button><a class="button secondary" href="{{ route('admin.pertemuan.create') }}">Reset</a>
                    </div>
                </form>
            </div>
            <div class="table-wrap">
                <table class="pertemuan-table">
                    <caption class="pertemuan-sr-only">Kelas yang dapat dibuatkan sesi</caption>
                    <thead>
                        <tr>
                            <th scope="col">Kelas</th>
                            <th scope="col">Rombel / periode</th>
                            <th scope="col">Jumlah sesi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daftarKelas as $pilihan)
                            <tr>
                                <td><strong>{{ $pilihan->kode }}</strong><span
                                        class="pertemuan-sub">{{ $pilihan->nama_mk_snapshot }}</span></td>
                                <td>{{ $pilihan->rombel->kode }}<span
                                        class="pertemuan-sub">{{ $pilihan->rombel->periodeAkademik->kode }}</span></td>
                                <td>{{ $pilihan->pertemuan_count }}</td>
                                <td><a class="button small"
                                        href="{{ route('admin.pertemuan.create', ['kelas_id' => $pilihan->id]) }}">Pilih
                                        kelas</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="pertemuan-empty">Belum ada kelas terbuka yang sesuai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="panel-body"><x-pagination :paginator="$daftarKelas" /></div>
        </section>
    @else
        <section class="card pertemuan-section">
            <div class="card-header pertemuan-toolbar">
                <h2>Kelas terpilih</h2><a href="{{ route('admin.pertemuan.create') }}">Ganti kelas</a>
            </div>
            <div class="panel-body">@include('admin.pertemuan._kelas')</div>
        </section>
        <section class="card pertemuan-section">
            <div class="card-header">
                <h2>Rencana sesi</h2>
            </div>
            <div class="panel-body">
                @unless ($boleh)
                    <div class="alert alert-error" role="alert">Kelas/periode belum terbuka atau belum memiliki penugasan
                        dosen aktif.</div>
                @endunless
                <form method="POST" action="{{ route('admin.pertemuan.store') }}">@include('admin.pertemuan._form')</form>
            </div>
        </section>
        <section class="card pertemuan-section">
            <div class="card-header">
                <h2>Sesi kelas yang sudah tercatat</h2>
            </div>@include('admin.pertemuan._daftar-sesi')
        </section>
    @endif
@endsection
