@extends('layouts.berkas')

@section('title', 'KRS Saya')

@section('content')
    <div class="heading">
        <div>
            <h1>KRS Saya</h1>
            <p>Daftar Kartu Rencana Studi setiap semester.</p>
        </div>
    </div>

    <form method="get" action="{{ route('portal.krs.index') }}" class="card">
        <h2>Filter KRS</h2>

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

        <label for="status">
            Status KRS
        </label>

        <select id="status" name="status">
            <option value="">Semua status</option>

            @foreach (\App\Models\Krs::STATUS as $nilai => $label)
                <option
                    value="{{ $nilai }}"
                    @selected(($filter['status'] ?? '') === $nilai)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <button type="submit">
            Terapkan
        </button>

        <a href="{{ route('portal.krs.index') }}">
            Reset
        </a>
    </form>

    <div class="card">
        <h2>Daftar KRS</h2>

        <table>
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Semester</th>
                    <th>Rombel</th>
                    <th>Total SKS</th>
                    <th>Status</th>
                    <th>Versi</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($daftarKrs as $krs)
                    @php
                        $registrasi = $krs->registrasiSemester;
                    @endphp

                    <tr>
                        <td>
                            {{ $registrasi->periodeAkademik->kode }}
                        </td>

                        <td>
                            Semester {{ $registrasi->semester_studi }}
                        </td>

                        <td>
                            {{ $registrasi->rombel->kode ?? '—' }}
                        </td>

                        <td>
                            {{ $krs->totalSks() }} SKS
                        </td>

                        <td>
                            {{ \App\Models\Krs::STATUS[$krs->status]
                                ?? $krs->status }}
                        </td>

                        <td>
                            {{ $krs->versi }}
                        </td>

                        <td>
                            <a href="{{ route('portal.krs.show', $krs) }}">
                                Detail
                            </a>

                            @if ($krs->status === \App\Models\Krs::DISAHKAN)
                                ·
                                <a
                                    href="{{ route('portal.krs.cetak', $krs) }}"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    Cetak
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            Belum ada KRS yang dapat ditampilkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $daftarKrs->links() }}
@endsection