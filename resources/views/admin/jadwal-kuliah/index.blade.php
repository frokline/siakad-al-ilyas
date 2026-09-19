@extends('layouts.siakad')
@section('title', 'Jadwal Kuliah')
@section('content')
    <div class="page-heading">
        <div>
            <h1>Jadwal Kuliah</h1>
            <p class="subtitle">Pola mingguan kelas, rombel, dan tim pengajar.</p>
        </div>
        <a class="button" href="{{ route('admin.jadwal-kuliah.create') }}">Tambah jadwal</a>
    </div>
    <section class="card">
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.jadwal-kuliah.index') }}" class="jadwal-filters">
                @if ($rombelTerpilih)
                    <input type="hidden" name="rombel_id" value="{{ $rombelTerpilih->id }}">
                @endif
                @if ($kelasTerpilih)
                    <input type="hidden" name="kelas_id" value="{{ $kelasTerpilih->id }}">
                @endif
                <div class="field jadwal-search">
                    <label for="q">Cari kelas / mata kuliah / rombel</label>
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
                    <label for="dosen_id">Dosen dalam tim aktif</label>
                    <select id="dosen_id" name="dosen_id">
                        <option value="">Semua dosen</option>
                        @foreach ($daftarDosen as $dosen)
                            <option value="{{ $dosen->id }}" @selected((string) ($filter['dosen_id'] ?? '') === (string) $dosen->id)>{{ $dosen->kode_dosen }} —
                                {{ $dosen->user->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="hari">Hari</label>
                    <select id="hari" name="hari">
                        <option value="">Semua hari</option>
                        @foreach (\App\Models\JadwalKuliah::HARI as $kode => $label)
                            <option value="{{ $kode }}" @selected((string) ($filter['hari'] ?? '') === (string) $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="tanggal">Berlaku pada tanggal</label>
                    <input id="tanggal" name="tanggal" type="date" value="{{ $filter['tanggal'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="aktif">Status pola</label>
                    <select id="aktif" name="aktif">
                        <option value="">Semua status</option>
                        <option value="1" @selected(($filter['aktif'] ?? '') === '1')>Aktif</option>
                        <option value="0" @selected(($filter['aktif'] ?? '') === '0')>Nonaktif</option>
                    </select>
                </div>
                <div class="actions jadwal-filter-actions">
                    <button class="button" type="submit">Terapkan</button>
                    <a class="button secondary" href="{{ route('admin.jadwal-kuliah.index') }}">Reset</a>
                </div>
            </form>
            <div class="jadwal-toolbar">
                <p class="help">{{ number_format($daftarJadwal->total(), 0, ',', '.') }} pola sesuai filter · Jam
                    {{ config('siakad.timezone', 'Asia/Makassar') }}</p>
                @if ($rombelTerpilih)
                    <span class="badge">Rombel {{ $rombelTerpilih->kode }}</span>
                @endif
                @if ($kelasTerpilih)
                    <span class="badge">Kelas {{ $kelasTerpilih->kode }}</span>
                @endif
            </div>
            <p class="help">Filter tanggal menampilkan pola yang jatuh pada hari tersebut. Tanggal libur dan perubahan
                pertemuan diatur pada modul pelaksanaan kuliah.</p>
        </div>
        <div class="table-wrap">
            <table class="jadwal-table">
                <caption class="jadwal-sr-only">Daftar pola jadwal kuliah</caption>
                <thead>
                    <tr>
                        <th scope="col">Hari / jam</th>
                        <th scope="col">Kelas / mata kuliah</th>
                        <th scope="col">Rombel / periode</th>
                        <th scope="col">Berlaku</th>
                        <th scope="col">Metode / lokasi</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daftarJadwal as $jadwal)
                        <tr>
                            <td>
                                <strong>{{ \App\Models\JadwalKuliah::HARI[$jadwal->hari] }}</strong>
                                <span
                                    class="jadwal-sub jadwal-nowrap">{{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                            </td>
                            <td>
                                <a
                                    href="{{ route('admin.kelas-kuliah.show', $jadwal->kelasKuliah) }}">{{ $jadwal->kelasKuliah->kode }}</a>
                                <span class="jadwal-sub">{{ $jadwal->kelasKuliah->nama_mk_snapshot }}</span>
                                <span
                                    class="jadwal-sub">{{ $jadwal->kelasKuliah->pengajarKelas->where('aktif', true)->map(fn($p) => $p->dosen->user->nama)->implode(', ') ?: 'Belum ada dosen aktif' }}</span>
                            </td>
                            <td>
                                <a
                                    href="{{ route('admin.jadwal-kuliah.index', ['rombel_id' => $jadwal->kelasKuliah->rombel_id]) }}">{{ $jadwal->kelasKuliah->rombel->kode }}</a>
                                <span class="jadwal-sub">{{ $jadwal->kelasKuliah->rombel->periodeAkademik->kode }}</span>
                            </td>
                            <td>{{ $jadwal->berlaku_mulai->format('d-m-Y') }}<span class="jadwal-sub">s.d.
                                    {{ $jadwal->berlaku_selesai->format('d-m-Y') }}</span></td>
                            <td>{{ \App\Models\JadwalKuliah::METODE[$jadwal->metode] }}<span
                                    class="jadwal-sub">{{ $jadwal->lokasi ?? '—' }}</span></td>
                            <td><span
                                    class="badge {{ $jadwal->aktif ? 'jadwal-badge-active' : 'jadwal-badge-muted' }}">{{ $jadwal->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td><a class="button secondary small"
                                    href="{{ route('admin.jadwal-kuliah.show', $jadwal) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="jadwal-empty">Tidak ada jadwal yang sesuai. Ubah filter atau buat
                                jadwal baru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-body"><x-pagination :paginator="$daftarJadwal" /></div>
    </section>
@endsection
