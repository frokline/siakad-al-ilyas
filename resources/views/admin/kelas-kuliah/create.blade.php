@extends('layouts.siakad')

@section('title', 'Tambah Kelas Kuliah')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Kelas Kuliah</h1>
            <p class="subtitle">Pilih rombel, kemudian mata kuliah dari paketnya.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.kelas-kuliah.index') }}">Daftar kelas</a>
    </div>

    @if ($rombel === null)
        <section class="card">
            <div class="panel-body">
                <form method="GET" action="{{ route('admin.kelas-kuliah.create') }}" class="kelas-search">
                    <div class="field">
                        <label for="q">Cari rombel atau paket</label>
                        <input type="search" name="q" id="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                            placeholder="Kode rombel atau nama paket">
                    </div>
                    <div class="field">
                        <label for="periode_akademik_id">Periode</label>
                        <select name="periode_akademik_id" id="periode_akademik_id">
                            <option value="">Semua periode terbuka</option>
                            @foreach ($periodePilihan as $periode)
                                <option value="{{ $periode->id }}" @selected((string) ($filter['periode_akademik_id'] ?? '') === (string) $periode->id)>
                                    {{ $periode->kode }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="button">Cari</button>
                </form>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Rombel</th>
                            <th scope="col">Periode</th>
                            <th scope="col">Paket</th>
                            <th scope="col">Kelas tercatat</th>
                            <th scope="col">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rombelPilihan as $pilihan)
                            <tr>
                                <td>
                                    <strong>{{ $pilihan->kode }}</strong>
                                    <div class="help">{{ $pilihan->paketSemester->kurikulum->programStudi->nama }}</div>
                                </td>
                                <td>{{ $pilihan->periodeAkademik->kode }}</td>
                                <td>
                                    {{ $pilihan->paketSemester->nama }}
                                    <div class="help">
                                        Semester {{ $pilihan->paketSemester->semester_studi }}
                                        · Versi {{ $pilihan->paketSemester->versi }}
                                    </div>
                                </td>
                                <td>{{ $pilihan->kelas_kuliah_count }} / {{ $pilihan->paketSemester->details_count }} mata
                                    kuliah</td>
                                <td>
                                    <a class="button small"
                                        href="{{ route('admin.kelas-kuliah.create', ['rombel_id' => $pilihan->id]) }}"
                                        aria-label="Pilih rombel {{ $pilihan->kode }} periode {{ $pilihan->periodeAkademik->kode }}">
                                        Pilih
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Belum ada rombel yang memenuhi syarat pencarian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="panel-body">
                <p class="help">Jumlah kelas tercatat mencakup semua status, termasuk arsip.</p>
                <x-pagination :paginator="$rombelPilihan" />
            </div>
        </section>
    @else
        <section class="card">
            <div class="card-header">
                <h2>Rombel terpilih</h2>
                <a href="{{ route('admin.kelas-kuliah.create') }}">Pilih rombel lain</a>
            </div>
            <div class="panel-body">
                @include('admin.kelas-kuliah._identitas')
            </div>
        </section>

        <section class="card kelas-section">
            <div class="card-header">
                <h2>Data kelas baru</h2>
            </div>
            <div class="panel-body">
                @if (!$bolehMembuat)
                    <p class="help">
                        Kelas baru memerlukan periode persiapan atau aktif, kurikulum dan program studi aktif,
                        serta paket rombel yang sudah pernah diterbitkan.
                    </p>
                @elseif ($detailPilihan->isEmpty())
                    @if ($kelasAda->count() >= (int) $rombel->paketSemester->details_count)
                        <p class="help">
                            Semua mata kuliah paket sudah memiliki kelas. Gunakan daftar di bawah untuk membuka kelasnya.
                        </p>
                    @else
                        <p class="help">
                            Tidak ada mata kuliah aktif yang dapat ditambahkan. Periksa status mata kuliah dalam paket.
                        </p>
                    @endif
                @else
                    <form method="POST" action="{{ route('admin.kelas-kuliah.store') }}">
                        @include('admin.kelas-kuliah._form')
                    </form>
                @endif
            </div>
        </section>

        <section class="card kelas-section">
            <div class="card-header">
                <h2>Kelas dalam rombel ini</h2>
                <span>{{ $kelasAda->count() }} / {{ $rombel->paketSemester->details_count }} mata kuliah</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Kode kelas</th>
                            <th scope="col">Mata kuliah</th>
                            <th scope="col">SKS</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kelasAda as $item)
                            <tr>
                                <td>{{ $item->kode }}</td>
                                <td>{{ $item->nama_mk_snapshot }}</td>
                                <td>{{ str_replace('.', ',', $item->sks_snapshot) }}</td>
                                <td>
                                    <span class="badge" data-kelas-status="{{ $item->status }}">
                                        {{ \App\Models\KelasKuliah::STATUS[$item->status] }}
                                    </span>
                                </td>
                                <td>
                                    <a class="button small secondary" href="{{ route('admin.kelas-kuliah.show', $item) }}"
                                        aria-label="Buka kelas {{ $item->kode }}">
                                        Buka kelas
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Belum ada kelas dalam rombel ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
