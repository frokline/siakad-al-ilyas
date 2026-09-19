@extends('layouts.siakad')

@section('title', 'Kelas Kuliah')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Kelas Kuliah</h1>
            <p class="subtitle">Penawaran mata kuliah pada rombel dan periode akademik.</p>
        </div>
        <a class="button" href="{{ route('admin.kelas-kuliah.create') }}">Tambah kelas</a>
    </div>

    @if ($rombelFilter !== null)
        <div class="alert">
            Menampilkan kelas rombel <strong>{{ $rombelFilter->kode }}</strong>.
            <a href="{{ route('admin.kelas-kuliah.index', \Illuminate\Support\Arr::except($filter, ['rombel_id'])) }}">
                Hapus filter rombel
            </a>
        </div>
    @endif

    <section class="card">
        <div class="panel-body">
            <form method="GET" action="{{ route('admin.kelas-kuliah.index') }}" class="filters kelas-filters">
                @if ($rombelFilter !== null)
                    <input type="hidden" name="rombel_id" value="{{ $rombelFilter->id }}">
                @endif
                <div class="field">
                    <label for="q">Cari kelas</label>
                    <input type="search" id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Kode, mata kuliah, rombel">
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
                        placeholder="Semua" value="{{ $filter['semester_studi'] ?? '' }}">
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\KelasKuliah::STATUS as $kode => $label)
                            <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="actions kelas-filter-actions">
                    <button type="submit" class="button">Tampilkan</button>
                    <a class="button secondary" href="{{ route('admin.kelas-kuliah.index') }}">Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="card kelas-section">
        <div class="card-header">
            <h2>Daftar kelas</h2>
            <span>{{ number_format($kelasDaftar->total(), 0, ',', '.') }} kelas</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Kelas / mata kuliah</th>
                        <th scope="col">Rombel</th>
                        <th scope="col">Periode</th>
                        <th scope="col">SKS</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kelasDaftar as $kelas)
                        <tr>
                            <td>
                                <strong>{{ $kelas->kode }}</strong>
                                <div>{{ $kelas->nama_mk_snapshot }}</div>
                            </td>
                            <td>
                                {{ $kelas->rombel->kode }}
                                <div class="help">
                                    {{ $kelas->rombel->paketSemester->kurikulum->programStudi->nama }}
                                    · Semester {{ $kelas->rombel->paketSemester->semester_studi }}
                                </div>
                            </td>
                            <td>{{ $kelas->rombel->periodeAkademik->kode }}</td>
                            <td>{{ str_replace('.', ',', $kelas->sks_snapshot) }}</td>
                            <td>
                                <span class="badge" data-kelas-status="{{ $kelas->status }}">
                                    {{ \App\Models\KelasKuliah::STATUS[$kelas->status] }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button small secondary" href="{{ route('admin.kelas-kuliah.show', $kelas) }}"
                                        aria-label="Detail kelas {{ $kelas->kode }} rombel {{ $kelas->rombel->kode }}">
                                        Detail
                                    </a>
                                    @if ($kelas->dapatDiubah())
                                        <a class="button small" href="{{ route('admin.kelas-kuliah.edit', $kelas) }}"
                                            aria-label="Edit kelas {{ $kelas->kode }} rombel {{ $kelas->rombel->kode }}">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Tidak ada kelas yang sesuai pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-body">
            <x-pagination :paginator="$kelasDaftar" />
        </div>
    </section>
@endsection
