@extends('layouts.siakad')

@section('title', 'Rombel')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Rombel</h1>
            <p class="subtitle">Kelompok belajar berdasarkan periode dan paket semester.</p>
        </div>

        <a class="button" href="{{ route('admin.rombel.create') }}">
            Tambah Rombel
        </a>
    </div>

    <section class="card">
        <div class="panel-body">
            <form class="filters filters-rombel" method="GET" action="{{ route('admin.rombel.index') }}">
                <div class="field">
                    <label for="q">Pencarian</label>

                    <input id="q" name="q" type="search" maxlength="150"
                        placeholder="Kode rombel / nama paket" value="{{ $filters['q'] ?? '' }}">
                </div>

                <div class="field">
                    <label for="periode_akademik_id">Periode akademik</label>

                    <select id="periode_akademik_id" name="periode_akademik_id">
                        <option value="">Semua periode</option>

                        @foreach ($daftarPeriode as $periode)
                            <option value="{{ $periode->id }}" @selected((string) ($filters['periode_akademik_id'] ?? '') === (string) $periode->id)>
                                {{ $periode->kode }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="program_studi_id">Program studi</label>

                    <select id="program_studi_id" name="program_studi_id">
                        <option value="">Semua prodi</option>

                        @foreach ($daftarProdi as $prodi)
                            <option value="{{ $prodi->id }}" @selected((string) ($filters['program_studi_id'] ?? '') === (string) $prodi->id)>
                                {{ $prodi->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="semester_studi">Semester studi</label>

                    <input id="semester_studi" name="semester_studi" type="number" min="1" max="32767"
                        step="1" placeholder="Semua" value="{{ $filters['semester_studi'] ?? '' }}">
                </div>

                <div class="actions">
                    <button class="button" type="submit">Cari</button>

                    <a class="button secondary" href="{{ route('admin.rombel.index') }}">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Kode Rombel</th>
                        <th scope="col">Periode</th>
                        <th scope="col">Program / Kurikulum</th>
                        <th scope="col">Paket</th>
                        <th scope="col">Semester</th>
                        <th scope="col">Kapasitas</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftarRombel as $rombel)
                        <tr>
                            <td>{{ $daftarRombel->firstItem() + $loop->index }}</td>

                            <td>
                                <strong>{{ $rombel->kode }}</strong>
                            </td>

                            <td>
                                <div>{{ $rombel->periodeAkademik->kode }}</div>

                                <span class="badge" data-periode-status="{{ $rombel->periodeAkademik->status }}">
                                    {{ $statusPeriodeOptions[$rombel->periodeAkademik->status] }}
                                </span>
                            </td>

                            <td>
                                <div>
                                    {{ $rombel->paketSemester->kurikulum->programStudi->nama }}
                                </div>

                                <div class="help">
                                    {{ $rombel->paketSemester->kurikulum->kode }}
                                </div>
                            </td>

                            <td>
                                <div>{{ $rombel->paketSemester->nama }}</div>

                                <div class="help">
                                    Versi {{ $rombel->paketSemester->versi }}
                                </div>
                            </td>

                            <td>{{ $rombel->paketSemester->semester_studi }}</td>

                            <td>
                                {{ $rombel->kapasitas === null ? 'Tanpa batas' : $rombel->kapasitas }}
                            </td>

                            <td>
                                <div class="actions">
                                    <a class="button secondary small" href="{{ route('admin.rombel.show', $rombel) }}">
                                        Detail
                                    </a>

                                    @if ($rombel->dapatDiubah())
                                        <a class="button small" href="{{ route('admin.rombel.edit', $rombel) }}">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Tidak ada rombel yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel-body">
            <x-pagination :paginator="$daftarRombel" />
        </div>
    </section>
@endsection
