@extends('layouts.siakad')

@section('title', 'Tambah Registrasi Semester')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Tambah Registrasi Semester</h1>
            <p class="subtitle">Pilih mahasiswa, periode, lalu rombel yang sesuai kurikulum.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.registrasi-semester.index') }}">Daftar registrasi</a>
    </div>

    @if ($riwayat === null)
        <section class="card">
            <div class="panel-body">
                <form method="GET" action="{{ route('admin.registrasi-semester.create') }}" class="registrasi-search">
                    <div class="field">
                        <label for="q">Cari mahasiswa</label>
                        <input type="search" id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                            placeholder="NIM atau nama mahasiswa">
                    </div>
                    @if ($periodeId > 0)
                        <input type="hidden" name="periode_akademik_id" value="{{ $periodeId }}">
                    @endif
                    <button type="submit" class="button">Cari</button>
                </form>
                <p class="help">Menampilkan riwayat studi aktif dengan akun mahasiswa, kurikulum, dan program studi aktif.
                </p>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Program studi / kurikulum</th>
                            <th scope="col">Angkatan</th>
                            <th scope="col">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($riwayatPilihan as $pilihan)
                            <tr>
                                <td>
                                    <strong>{{ $pilihan->mahasiswa->user->nama }}</strong>
                                    <div class="help">{{ $pilihan->mahasiswa->nim }}</div>
                                </td>
                                <td>
                                    {{ $pilihan->kurikulum->programStudi->nama }}
                                    <div class="help">{{ $pilihan->kurikulum->kode }}</div>
                                </td>
                                <td>{{ $pilihan->angkatan }}</td>
                                <td>
                                    <a class="button small"
                                        href="{{ route('admin.registrasi-semester.create', [
                                            'riwayat_studi_id' => $pilihan->id,
                                            'periode_akademik_id' => $periodeId > 0 ? $periodeId : null,
                                        ]) }}"
                                        aria-label="Pilih mahasiswa {{ $pilihan->mahasiswa->nim }}">
                                        Pilih
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Mahasiswa yang memenuhi syarat belum ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="panel-body">
                <x-pagination :paginator="$riwayatPilihan" />
            </div>
        </section>
    @else
        <section class="card">
            <div class="card-header">
                <h2>Mahasiswa terpilih</h2>
                <a
                    href="{{ route('admin.registrasi-semester.create', [
                        'periode_akademik_id' => $periodeId > 0 ? $periodeId : null,
                    ]) }}">Pilih
                    mahasiswa lain</a>
            </div>
            <div class="panel-body">
                @include('admin.registrasi-semester._identitas')
            </div>
        </section>

        <section class="card registrasi-section">
            <div class="panel-body">
                <form method="GET" action="{{ route('admin.registrasi-semester.create') }}" class="registrasi-search">
                    <input type="hidden" name="riwayat_studi_id" value="{{ $riwayat->id }}">
                    <div class="field">
                        <label for="periode_akademik_id">Periode akademik</label>
                        <select name="periode_akademik_id" id="periode_akademik_id" required>
                            <option value="">Pilih periode</option>
                            @foreach ($periodePilihan as $periode)
                                <option value="{{ $periode->id }}" @selected($periodeId === $periode->id)>
                                    {{ $periode->kode }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="button secondary" @disabled($periodePilihan->isEmpty())>
                        Tampilkan rombel
                    </button>
                </form>

                @if ($periodePilihan->isEmpty())
                    <p class="help">Belum ada periode persiapan atau aktif. Siapkan periode akademik terlebih dahulu.</p>
                @elseif ($registrasiLama !== null)
                    <div class="alert">
                        Mahasiswa ini sudah memiliki registrasi untuk periode tersebut.
                        <a href="{{ route('admin.registrasi-semester.show', $registrasiLama) }}">
                            Buka registrasi yang sudah ada
                        </a>.
                    </div>
                @elseif ($rombelPilihan->isEmpty())
                    <p class="help">
                        Belum ada rombel yang memenuhi syarat untuk kurikulum dan periode ini.
                        Periksa paket terbit, mata kuliah aktif, dan rombel.
                    </p>
                @else
                    <form method="POST" action="{{ route('admin.registrasi-semester.store') }}"
                        class="registrasi-section">
                        @include('admin.registrasi-semester._form')
                    </form>
                @endif
            </div>
        </section>
    @endif
@endsection
