@extends('layouts.berkas')

@section('title', 'Jadwal Kuliah Saya')

@section('content')
    <div class="heading">
        <div>
            <h1>Jadwal Kuliah Saya</h1>

            <p>
                Jadwal dari kelas yang tercantum pada KRS yang telah disahkan.
            </p>
        </div>
    </div>

    <form
        method="get"
        action="{{ route('portal.jadwal.index') }}"
        class="card"
    >
        <h2>Filter jadwal</h2>

        <label for="periode_id">
            Periode akademik
        </label>

        <select id="periode_id" name="periode_id">
            <option value="">Semua periode</option>

            @foreach ($daftarPeriode as $periode)
                <option
                    value="{{ $periode->id }}"
                    @selected(
                        (string) ($filter['periode_id'] ?? '')
                        === (string) $periode->id
                    )
                >
                    {{ $periode->kode }}
                </option>
            @endforeach
        </select>

        <label for="hari">
            Hari
        </label>

        <select id="hari" name="hari">
            <option value="">Semua hari</option>

            @foreach (\App\Models\JadwalKuliah::HARI as $nilai => $label)
                <option
                    value="{{ $nilai }}"
                    @selected(
                        (string) ($filter['hari'] ?? '')
                        === (string) $nilai
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <label for="metode">
            Metode
        </label>

        <select id="metode" name="metode">
            <option value="">Semua metode</option>

            @foreach (\App\Models\JadwalKuliah::METODE as $nilai => $label)
                <option
                    value="{{ $nilai }}"
                    @selected(($filter['metode'] ?? '') === $nilai)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <button type="submit">
            Terapkan
        </button>

        <a href="{{ route('portal.jadwal.index') }}">
            Reset
        </a>
    </form>

    <div class="card">
        <h2>Daftar jadwal</h2>

        <table>
            <thead>
                <tr>
                    <th>Hari</th>
                    <th>Waktu</th>
                    <th>Kelas</th>
                    <th>Mata kuliah</th>
                    <th>Metode</th>
                    <th>Lokasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($daftarJadwal as $jadwal)
                    <tr>
                        <td>
                            {{ \App\Models\JadwalKuliah::HARI[$jadwal->hari]
                                ?? '—' }}
                        </td>

                        <td>
                            {{ substr($jadwal->jam_mulai, 0, 5) }}
                            –
                            {{ substr($jadwal->jam_selesai, 0, 5) }}
                        </td>

                        <td>
                            {{ $jadwal->kelasKuliah->kode }}
                        </td>

                        <td>
                            {{ $jadwal->kelasKuliah->nama_mk_snapshot }}
                        </td>

                        <td>
                            {{ \App\Models\JadwalKuliah::METODE[$jadwal->metode]
                                ?? $jadwal->metode }}
                        </td>

                        <td>
                            {{ $jadwal->lokasi ?: '—' }}
                        </td>

                        <td>
                            <a
                                href="{{ route(
                                    'portal.jadwal.show',
                                    $jadwal
                                ) }}"
                            >
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            Belum ada jadwal aktif yang dapat ditampilkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection