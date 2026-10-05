@extends('layouts.berkas')

@section('title', 'Detail Jadwal Kuliah')

@section('content')
    <div class="heading">
        <div>
            <h1>Detail Jadwal Kuliah</h1>

            <p>
                {{ $kelas->nama_mk_snapshot }}
            </p>
        </div>

        <a href="{{ route('portal.jadwal.index') }}">
            Kembali ke jadwal
        </a>
    </div>

    <div class="card">
        <h2>Informasi kelas</h2>

        <dl>
            <dt>Kode kelas</dt>
            <dd>{{ $kelas->kode }}</dd>

            <dt>Mata kuliah</dt>
            <dd>{{ $kelas->nama_mk_snapshot }}</dd>

            <dt>SKS</dt>
            <dd>{{ $kelas->sks_snapshot }}</dd>

            <dt>Rombel</dt>
            <dd>{{ $kelas->rombel->kode }}</dd>

            <dt>Periode akademik</dt>
            <dd>
                {{ $kelas->rombel->periodeAkademik->kode }}
            </dd>
        </dl>
    </div>

    <div class="card">
        <h2>Waktu dan tempat</h2>

        <dl>
            <dt>Hari</dt>
            <dd>
                {{ \App\Models\JadwalKuliah::HARI[$jadwal->hari]
                    ?? '—' }}
            </dd>

            <dt>Jam</dt>
            <dd>
                {{ substr($jadwal->jam_mulai, 0, 5) }}
                –
                {{ substr($jadwal->jam_selesai, 0, 5) }}
            </dd>

            <dt>Berlaku mulai</dt>
            <dd>
                {{ $jadwal->berlaku_mulai->format('d-m-Y') }}
            </dd>

            <dt>Berlaku sampai</dt>
            <dd>
                {{ $jadwal->berlaku_selesai->format('d-m-Y') }}
            </dd>

            <dt>Metode</dt>
            <dd>
                {{ \App\Models\JadwalKuliah::METODE[$jadwal->metode]
                    ?? $jadwal->metode }}
            </dd>

            <dt>Lokasi</dt>
            <dd>{{ $jadwal->lokasi ?: '—' }}</dd>
        </dl>

        @if (
            in_array($jadwal->metode, ['daring', 'campuran'], true)
            && $jadwal->tautan_pertemuan
        )
            <p>
                <a
                    href="{{ $jadwal->tautan_pertemuan }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    referrerpolicy="no-referrer"
                >
                    Buka tautan pertemuan
                </a>
            </p>
        @endif
    </div>

    <div class="card">
        <h2>Tim pengajar</h2>

        <ul>
            @forelse ($kelas->pengajarAktif as $pengajar)
                <li>
                    {{ $pengajar->dosen->user->nama }}
                    —
                    {{ \App\Models\PengajarKelas::PERAN[$pengajar->peran]
                        ?? $pengajar->peran }}
                </li>
            @empty
                <li>Tim pengajar belum tersedia.</li>
            @endforelse
        </ul>
    </div>
@endsection