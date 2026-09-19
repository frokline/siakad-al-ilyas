@extends('layouts.siakad')
@section('title', 'Buat KRS Paket Semester')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Buat KRS Paket Semester</h1>
            <p class="subtitle">Pilih registrasi aktif, periksa seluruh paket, lalu buat draf.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.krs.index') }}">Daftar KRS</a>
    </div>

    <section class="card">
        <div class="card-header">
            <h2>Pilih registrasi mahasiswa</h2>
        </div>
        <div class="panel-body">
            <form class="krs-filters krs-filters-ringkas" method="GET" action="{{ route('admin.krs.create') }}">
                <div class="field">
                    <label for="q">NIM atau nama</label>
                    <input type="search" id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                        placeholder="Cari mahasiswa">
                </div>
                <div class="field">
                    <label for="periode_id">Periode aktif</label>
                    <select id="periode_id" name="periode_id">
                        <option value="">Semua periode aktif</option>
                        @foreach ($daftarPeriode as $periode)
                            <option value="{{ $periode->id }}" @selected((string) ($filter['periode_id'] ?? '') === (string) $periode->id)>
                                {{ $periode->kode }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="actions">
                    <button class="button" type="submit">Cari</button>
                    <a class="button secondary" href="{{ route('admin.krs.create') }}">Reset</a>
                </div>
            </form>
            <p class="help">Daftar menampilkan registrasi aktif pada periode aktif yang belum memiliki KRS.</p>
        </div>
        <div class="table-wrap">
            <table class="krs-table">
                <thead>
                    <tr>
                        <th scope="col">Mahasiswa</th>
                        <th scope="col">Periode</th>
                        <th scope="col">Rombel / semester</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($calonRegistrasi as $calon)
                        <tr>
                            <td>
                                <strong>{{ $calon->riwayatStudi->mahasiswa->nim }}</strong><br>
                                {{ $calon->riwayatStudi->mahasiswa->user->nama }}
                            </td>
                            <td>{{ $calon->periodeAkademik->kode }}</td>
                            <td>{{ $calon->rombel->kode }} / semester {{ $calon->semester_studi }}</td>
                            <td>
                                <a class="button small"
                                    href="{{ route('admin.krs.create', [
                                        'registrasi_id' => $calon->id,
                                        'q' => $filter['q'] ?? null,
                                        'periode_id' => $filter['periode_id'] ?? null,
                                    ]) . '#paket-krs' }}">Periksa
                                    paket</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Tidak ada registrasi yang sesuai. Periksa aktivasi registrasi atau buka KRS
                                yang sudah ada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-body"><x-pagination :paginator="$calonRegistrasi" /></div>
    </section>

    @if ($registrasi)
        <section class="card krs-section" id="paket-krs">
            <div class="card-header">
                <h2>Identitas dan paket terpilih</h2>
            </div>
            <div class="panel-body">
                @include('admin.krs._identitas')
                @if ($kendala !== [])
                    <div class="alert alert-error krs-section" role="alert">
                        <strong>Pembuatan KRS belum tersedia.</strong>
                        <ul>
                            @foreach ($kendala as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="actions krs-section">
                    @if ($registrasi->krs)
                        <a class="button" href="{{ route('admin.krs.show', $registrasi->krs) }}">Buka KRS yang sudah
                            ada</a>
                    @endif
                    <a class="button secondary"
                        href="{{ route('admin.kelas-kuliah.index', ['rombel_id' => $registrasi->rombel_id]) }}">
                        Periksa kelas rombel
                    </a>
                </div>
            </div>
            <div class="table-wrap">
                <table class="krs-table">
                    <caption>Seluruh {{ $barisPaket->count() }} mata kuliah paket</caption>
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Mata kuliah</th>
                            <th scope="col" class="krs-angka">SKS</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Kesiapan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($barisPaket as $baris)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $baris['nama'] }}</td>
                                <td class="krs-angka">{{ str_replace('.', ',', $baris['sks']) }}</td>
                                <td>{{ $baris['kelas']?->kode ?? 'Belum dibuat' }}</td>
                                <td>{{ $baris['kelas'] ? \App\Models\KelasKuliah::STATUS[$baris['kelas']->status] : 'Lengkapi kelas dahulu' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Paket belum memiliki mata kuliah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($kendala === [])
                <div class="panel-body">
                    <p class="help">Draf mencakup semua mata kuliah di atas. Pengajuan dilakukan setelah seluruh kelas
                        aktif.</p>
                    <form method="POST" action="{{ route('admin.krs.store') }}">
                        @include('admin.krs._form', ['krs' => null, 'bolehSimpan' => true])
                    </form>
                </div>
            @endif
        </section>
    @endif
@endsection
