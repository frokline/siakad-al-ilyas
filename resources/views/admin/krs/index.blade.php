@extends('layouts.siakad')
@section('title', 'Kartu Rencana Studi')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Kartu Rencana Studi</h1>
            <p class="subtitle">KRS paket semester dan pengesahan oleh admin akademik.</p>
        </div>
        <a class="button" href="{{ route('admin.krs.create') }}">Buat KRS</a>
    </div>

    <section class="card">
        <div class="panel-body">
            <form class="krs-filters" method="GET" action="{{ route('admin.krs.index') }}">
                <div class="field">
                    <label for="q">NIM atau nama</label>
                    <input id="q" type="search" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Cari mahasiswa">
                </div>
                <div class="field">
                    <label for="periode_id">Periode</label>
                    <select name="periode_id" id="periode_id">
                        <option value="">Semua periode</option>
                        @foreach ($daftarPeriode as $periode)
                            <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>
                                {{ $periode->kode }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="program_studi_id">Program studi</label>
                    <select name="program_studi_id" id="program_studi_id">
                        <option value="">Semua program studi</option>
                        @foreach ($daftarProdi as $prodi)
                            <option value="{{ $prodi->id }}" @selected((string) ($filter['program_studi_id'] ?? '') === (string) $prodi->id)>
                                {{ $prodi->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="status">Status KRS</label>
                    <select name="status" id="status">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\Krs::STATUS as $kode => $label)
                            <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="actions">
                    <button class="button" type="submit">Tampilkan</button>
                    <a class="button secondary" href="{{ route('admin.krs.index') }}">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table class="krs-table">
                <caption>{{ $daftarKrs->total() }} KRS ditemukan</caption>
                <thead>
                    <tr>
                        <th scope="col">Mahasiswa</th>
                        <th scope="col">Periode / rombel</th>
                        <th scope="col">Paket</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarKrs as $krs)
                        <tr>
                            <td>
                                <strong>{{ $krs->registrasiSemester->riwayatStudi->mahasiswa->nim }}</strong><br>
                                {{ $krs->registrasiSemester->riwayatStudi->mahasiswa->user->nama }}
                                <div class="help">
                                    {{ $krs->registrasiSemester->riwayatStudi->kurikulum->programStudi->nama }}</div>
                            </td>
                            <td>
                                {{ $krs->registrasiSemester->periodeAkademik->kode }}<br>
                                {{ $krs->registrasiSemester->rombel->kode }}
                            </td>
                            <td>
                                Semester {{ $krs->registrasiSemester->semester_studi }}<br>
                                {{ $krs->details->count() }} mata kuliah · {{ str_replace('.', ',', $krs->totalSks()) }}
                                SKS
                            </td>
                            <td>
                                <span class="badge"
                                    data-krs-status="{{ $krs->status }}">{{ \App\Models\Krs::STATUS[$krs->status] }}</span>
                                <div class="help">Versi {{ $krs->versi }}</div>
                            </td>
                            <td>
                                <a class="button small" href="{{ route('admin.krs.show', $krs) }}">Buka KRS
                                    #{{ $krs->id }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Belum ada KRS sesuai pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-body"><x-pagination :paginator="$daftarKrs" /></div>
    </section>
@endsection
