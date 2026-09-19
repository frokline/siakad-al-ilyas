@extends('layouts.siakad')

@section('title', 'Registrasi Semester')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Registrasi Semester</h1>
            <p class="subtitle">Penempatan mahasiswa pada rombel dan periode akademik.</p>
        </div>
        <a class="button" href="{{ route('admin.registrasi-semester.create') }}">Tambah registrasi</a>
    </div>

    <section class="card">
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.registrasi-semester.index') }}" class="filters registrasi-filters">
                <div class="field">
                    <label for="q">Cari mahasiswa atau rombel</label>
                    <input type="search" id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="NIM, nama, kode rombel">
                </div>
                <div class="field">
                    <label for="periode_akademik_id">Periode</label>
                    <select id="periode_akademik_id" name="periode_akademik_id">
                        <option value="">Semua periode</option>
                        @foreach ($periodePilihan as $periode)
                            <option value="{{ $periode->id }}" @selected((string) ($filter['periode_akademik_id'] ?? '') === (string) $periode->id)>
                                {{ $periode->kode }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="program_studi_id">Program studi</label>
                    <select id="program_studi_id" name="program_studi_id">
                        <option value="">Semua program studi</option>
                        @foreach ($prodiPilihan as $prodi)
                            <option value="{{ $prodi->id }}" @selected((string) ($filter['program_studi_id'] ?? '') === (string) $prodi->id)>
                                {{ $prodi->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="semester_studi">Semester studi</label>
                    <input type="number" id="semester_studi" name="semester_studi" min="1" max="32767"
                        value="{{ $filter['semester_studi'] ?? '' }}" placeholder="Semua">
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\RegistrasiSemester::STATUS as $kode => $label)
                            <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="actions registrasi-filter-actions">
                    <button class="button" type="submit">Tampilkan</button>
                    <a class="button secondary" href="{{ route('admin.registrasi-semester.index') }}">Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="card registrasi-section">
        <div class="card-header">
            <h2>Daftar registrasi</h2>
            <span>{{ number_format($registrasiDaftar->total(), 0, ',', '.') }} data</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Mahasiswa</th>
                        <th scope="col">Program studi</th>
                        <th scope="col">Periode</th>
                        <th scope="col">Rombel</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registrasiDaftar as $registrasi)
                        <tr>
                            <td>
                                <strong>{{ $registrasi->riwayatStudi->mahasiswa->user->nama }}</strong>
                                <div class="help">{{ $registrasi->riwayatStudi->mahasiswa->nim }}</div>
                            </td>
                            <td>{{ $registrasi->riwayatStudi->kurikulum->programStudi->nama }}</td>
                            <td>{{ $registrasi->periodeAkademik->kode }}</td>
                            <td>
                                {{ $registrasi->rombel->kode }}
                                <div class="help">Semester {{ $registrasi->semester_studi }}</div>
                            </td>
                            <td>
                                <span class="badge" data-registrasi-status="{{ $registrasi->status }}">
                                    {{ \App\Models\RegistrasiSemester::STATUS[$registrasi->status] }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button small secondary"
                                        href="{{ route('admin.registrasi-semester.show', $registrasi) }}"
                                        aria-label="Detail registrasi {{ $registrasi->riwayatStudi->mahasiswa->nim }}">
                                        Detail
                                    </a>
                                    @if ($registrasi->dapatDiubah())
                                        <a class="button small"
                                            href="{{ route('admin.registrasi-semester.edit', $registrasi) }}"
                                            aria-label="Edit registrasi {{ $registrasi->riwayatStudi->mahasiswa->nim }}">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Tidak ada registrasi yang sesuai pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-body">
            <x-pagination :paginator="$registrasiDaftar" />
        </div>
    </section>
@endsection
