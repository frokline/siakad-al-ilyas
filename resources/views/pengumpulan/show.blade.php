@extends('layouts.pengumpulan')

@section('title', 'Detail Jawaban')

@section('content')
    @php
        $kegiatan = $pengumpulan->kegiatan;

        $status = \App\Models\Pengumpulan::STATUS[
            $pengumpulan->status
        ] ?? $pengumpulan->status;
    @endphp

    <div class="heading">
        <div>
            <p>Jawaban mahasiswa</p>
            <h1>{{ $kegiatan?->judul ?? 'Detail jawaban' }}</h1>

            <p>
                {{ $kegiatan?->kelasKuliah?->kode ?? 'Kelas' }}
                —
                {{ $kegiatan?->kelasKuliah?->nama_mk_snapshot
                    ?? 'Mata kuliah' }}
            </p>
        </div>

        <a href="{{ route(
            'pengumpulan.saya',
            $kegiatan
        ) }}">
            Kembali
        </a>
    </div>

    @include('pengumpulan._jadwal', [
        'kegiatan' => $kegiatan,
    ])

    <section class="card">
        <h2>Status jawaban</h2>

        <dl class="metadata">
            <div>
                <dt>Status</dt>
                <dd>{{ $status }}</dd>
            </div>

            <div>
                <dt>Dikirim</dt>
                <dd>
                    {{ $pengumpulan->dikirim_at
                        ? $pengumpulan->dikirim_at
                            ->setTimezone($zona)
                            ->format('d-m-Y H:i')
                        : '—' }}
                </dd>
            </div>

            <div>
                <dt>Terakhir diubah</dt>
                <dd>
                    {{ $pengumpulan->diubah_at
                        ? $pengumpulan->diubah_at
                            ->setTimezone($zona)
                            ->format('d-m-Y H:i')
                        : 'Belum pernah diubah' }}
                </dd>
            </div>
        </dl>
    </section>

    <section class="card">
        <h2>Pesan untuk dosen</h2>

        @if (filled($pengumpulan->jawaban_teks))
            <div class="peng-isi">
                {{ $pengumpulan->jawaban_teks }}
            </div>
        @else
            <p class="muted">
                Tidak ada pesan tambahan.
            </p>
        @endif
    </section>

    <section class="card">
        <h2>Berkas jawaban</h2>

        @forelse ($pengumpulan->lampiran as $lampiran)
            <div class="attachment">
                <div>
                    <strong>
                        {{ $lampiran->nama_asli }}
                    </strong>

                    <small>
                        {{ number_format(
                            $lampiran->ukuran_byte / 1048576,
                            2,
                            ',',
                            '.'
                        ) }} MB
                    </small>
                </div>

                <form
                    method="post"
                    action="{{ route(
                        'pengumpulan.tautan',
                        [
                            $pengumpulan,
                            $lampiran,
                        ]
                    ) }}"
                >
                    @csrf

                    <button type="submit" class="secondary">
                        Unduh
                    </button>
                </form>
            </div>
        @empty
            <p class="muted">
                Tidak ada berkas jawaban.
            </p>
        @endforelse
    </section>

    @if ($bolehTulis)
        <section class="card">
            <h2>Kelola jawaban</h2>

            <p>
                Jawaban masih dapat diubah atau dihapus karena
                tenggat belum berakhir.
            </p>

            <div class="actions">
                <a
                    class="button"
                    href="{{ route(
                        'pengumpulan.edit',
                        $pengumpulan
                    ) }}"
                >
                    Edit jawaban
                </a>

                <form
                    method="post"
                    action="{{ route(
                        'pengumpulan.destroy',
                        $pengumpulan
                    ) }}"
                    onsubmit="return confirm('Hapus jawaban ini?')"
                >
                    @csrf
                    @method('DELETE')

                    <input
                        type="hidden"
                        name="versi_form"
                        value="{{ $pengumpulan->versiForm() }}"
                    >

                    <button type="submit" class="secondary">
                        Hapus jawaban
                    </button>
                </form>
            </div>
        </section>
    @elseif (
        $pemilik
        && $pengumpulan->status
            === \App\Models\Pengumpulan::TERKIRIM
    )
        <section class="card">
            <p class="muted">
                Tenggat telah berakhir. Jawaban hanya dapat dilihat
                dan tidak dapat diubah lagi.
            </p>
        </section>
    @endif
@endsection