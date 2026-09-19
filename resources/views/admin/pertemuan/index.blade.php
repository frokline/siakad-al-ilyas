@extends('layouts.siakad')
@section('title', 'Pertemuan Kuliah')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Pertemuan Kuliah</h1>
            <p class="subtitle">Rencana dan pelaksanaan sesi perkuliahan.</p>
        </div>
        <a class="button" href="{{ route('admin.pertemuan.create') }}">Buat pertemuan</a>
    </div>
    <section class="card">
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.pertemuan.index') }}" class="pertemuan-filters">
                <div class="field pertemuan-search">
                    <label for="q">Cari topik, kelas, atau mata kuliah</label>
                    <input id="q" name="q" type="search" maxlength="80" value="{{ $filter['q'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="periode_id">Periode</label>
                    <select id="periode_id" name="periode_id">
                        <option value="">Semua periode</option>
                        @foreach ($daftarPeriode as $periode)
                            <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>{{ $periode->kode }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="dosen_id">Penanggung jawab</label>
                    <select id="dosen_id" name="dosen_id">
                        <option value="">Semua dosen</option>
                        @foreach ($daftarDosen as $dosen)
                            <option value="{{ $dosen->id }}" @selected((string) ($filter['dosen_id'] ?? '') === (string) $dosen->id)>{{ $dosen->kode_dosen }} —
                                {{ $dosen->user->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\Pertemuan::STATUS as $kode => $label)
                            <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="tanggal">Tanggal rencana</label>
                    <input id="tanggal" name="tanggal" type="date" value="{{ $filter['tanggal'] ?? '' }}">
                </div>
                <div class="actions pertemuan-filter-actions">
                    <button class="button" type="submit">Terapkan</button>
                    <a class="button secondary" href="{{ route('admin.pertemuan.index') }}">Reset</a>
                </div>
            </form>
            <div class="pertemuan-toolbar">
                <p class="help">{{ number_format($daftarPertemuan->total(), 0, ',', '.') }} sesi · Waktu
                    {{ config('siakad.timezone', 'Asia/Makassar') }}</p>
                @if ($kelasTerpilih)
                    <span class="badge">Kelas {{ $kelasTerpilih->kode }}</span>
                @endif
            </div>
        </div>
        <div class="table-wrap">
            <table class="pertemuan-table">
                <caption class="pertemuan-sr-only">Daftar pertemuan kuliah</caption>
                <thead>
                    <tr>
                        <th scope="col">Rencana</th>
                        <th scope="col">Kelas / mata kuliah</th>
                        <th scope="col">Jenis / topik</th>
                        <th scope="col">Penanggung jawab</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftarPertemuan as $sesi)
                        <tr>
                            <td>
                                <strong>Pertemuan {{ $sesi->nomor }}</strong>
                                <span
                                    class="pertemuan-sub">{{ $sesi->mulai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y') }}</span>
                                <span
                                    class="pertemuan-sub">{{ $sesi->mulai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('H:i') }}–{{ $sesi->selesai_rencana->setTimezone(config('siakad.timezone', 'Asia/Makassar'))->format('H:i') }}</span>
                            </td>
                            <td>
                                <a
                                    href="{{ route('admin.kelas-kuliah.show', $sesi->kelasKuliah) }}">{{ $sesi->kelasKuliah->kode }}</a>
                                <span class="pertemuan-sub">{{ $sesi->kelasKuliah->nama_mk_snapshot }}</span>
                                <span class="pertemuan-sub">{{ $sesi->kelasKuliah->rombel->kode }} ·
                                    {{ $sesi->kelasKuliah->rombel->periodeAkademik->kode }}</span>
                            </td>
                            <td>{{ \App\Models\Pertemuan::JENIS[$sesi->jenis] ?? $sesi->jenis }}<span
                                    class="pertemuan-sub">{{ $sesi->topik }}</span></td>
                            <td>{{ $sesi->pengajar_snapshot['nama'] ?? '—' }}<span
                                    class="pertemuan-sub">{{ $sesi->pengajar_snapshot['kode_dosen'] ?? '—' }}</span></td>
                            <td><span
                                    class="badge pertemuan-status-{{ $sesi->status }}">{{ \App\Models\Pertemuan::STATUS[$sesi->status] ?? $sesi->status }}</span>
                            </td>
                            <td><a class="button secondary small"
                                    href="{{ route('admin.pertemuan.show', $sesi) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="pertemuan-empty">Tidak ada pertemuan yang sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-body"><x-pagination :paginator="$daftarPertemuan" /></div>
    </section>
@endsection
