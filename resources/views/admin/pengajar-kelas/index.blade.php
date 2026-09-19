@extends('layouts.siakad')
@section('title', 'Pengajar Kelas')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Pengajar Kelas</h1>
            <p class="subtitle">Penugasan dosen, koordinator, dan riwayat tim pengajar.</p>
        </div>
        <a class="button" href="{{ route('admin.pengajar-kelas.create') }}">Tambah penugasan</a>
    </div>

    <section class="card">
        <div class="panel-body">
            @if (!empty($filter['kelas_id']) || !empty($filter['dosen_id']))
                <p class="help">
                    @if (!empty($filter['kelas_id']))
                        Kelas #{{ $filter['kelas_id'] }}.
                    @endif
                    @if (!empty($filter['dosen_id']))
                        Dosen #{{ $filter['dosen_id'] }}.
                    @endif
                    Gunakan Reset untuk menampilkan seluruh penugasan.
                </p>
            @endif
            <form class="pengajar-filters" method="GET" action="{{ route('admin.pengajar-kelas.index') }}">
                @foreach (['kelas_id', 'dosen_id'] as $kunci)
                    @if (!empty($filter[$kunci]))
                        <input type="hidden" name="{{ $kunci }}" value="{{ $filter[$kunci] }}">
                    @endif
                @endforeach
                <div class="field">
                    <label for="q">Dosen atau kelas</label>
                    <input id="q" name="q" type="search" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Kode, nama dosen, mata kuliah">
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
                    <label for="peran">Peran</label>
                    <select id="peran" name="peran">
                        <option value="">Semua peran</option>
                        @foreach (\App\Models\PengajarKelas::PERAN as $kode => $label)
                            <option value="{{ $kode }}" @selected(($filter['peran'] ?? '') === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="aktif">Penugasan</label>
                    <select id="aktif" name="aktif">
                        <option value="">Semua status</option>
                        <option value="1" @selected(($filter['aktif'] ?? '') === '1')>Aktif</option>
                        <option value="0" @selected(($filter['aktif'] ?? '') === '0')>Nonaktif</option>
                    </select>
                </div>
                <div class="actions">
                    <button class="button" type="submit">Tampilkan</button>
                    <a class="button secondary" href="{{ route('admin.pengajar-kelas.index') }}">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-wrap">
            <table class="pengajar-table">
                <caption>{{ $daftarPenugasan->total() }} penugasan ditemukan</caption>
                <thead>
                    <tr>
                        <th scope="col">Dosen</th>
                        <th scope="col">Kelas / mata kuliah</th>
                        <th scope="col">Periode / rombel</th>
                        <th scope="col">Peran / status</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarPenugasan as $penugasan)
                        <tr>
                            <td><strong>{{ $penugasan->dosen->kode_dosen }}</strong><br>{{ $penugasan->dosen->user->nama }}
                            </td>
                            <td>
                                {{ $penugasan->kelasKuliah->kode }}<br>{{ $penugasan->kelasKuliah->nama_mk_snapshot }}
                                <div class="help">{{ \App\Models\KelasKuliah::STATUS[$penugasan->kelasKuliah->status] }}
                                </div>
                            </td>
                            <td>
                                {{ $penugasan->kelasKuliah->rombel->periodeAkademik->kode }}<br>{{ $penugasan->kelasKuliah->rombel->kode }}
                            </td>
                            <td>
                                <span class="badge"
                                    data-pengajar-peran="{{ $penugasan->peran }}">{{ \App\Models\PengajarKelas::PERAN[$penugasan->peran] }}</span>
                                <span class="badge"
                                    data-pengajar-aktif="{{ $penugasan->aktif ? '1' : '0' }}">{{ $penugasan->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td><a class="button small" href="{{ route('admin.pengajar-kelas.show', $penugasan) }}">Detail
                                    #{{ $penugasan->id }}</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Belum ada penugasan sesuai pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-body"><x-pagination :paginator="$daftarPenugasan" /></div>
    </section>
@endsection
