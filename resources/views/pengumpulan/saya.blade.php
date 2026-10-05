@extends('layouts.pengumpulan')

@section('title', 'Pengumpulan Jawaban')

@section('content')
    @php
        $format = strtoupper(
            implode(', ', $kegiatan->ekstensi_diizinkan)
        );

        $aktif = $pengumpulan
            && $pengumpulan->status
                === \App\Models\Pengumpulan::TERKIRIM;

        $dibatalkan = $pengumpulan
            && $pengumpulan->status
                === \App\Models\Pengumpulan::DIBATALKAN;
    @endphp

    <div class="heading">
        <div>
            <p>Pembelajaran mahasiswa</p>
            <h1>{{ $kegiatan->judul }}</h1>

            <p>
                {{ $kegiatan->kelasKuliah?->kode ?? 'Kelas' }}
                —
                {{ $kegiatan->kelasKuliah?->nama_mk_snapshot ?? 'Mata kuliah' }}
            </p>
        </div>

        <a href="{{ route('kegiatan.show', $kegiatan) }}">
            Lihat instruksi
        </a>
    </div>

    @include('pengumpulan._jadwal', [
        'kegiatan' => $kegiatan,
    ])

    @if ($aktif)
        <section class="card">
            <h2>Jawaban sudah dikumpulkan</h2>

            <p class="notice">
                Jawaban dikumpulkan pada
                {{ $pengumpulan->dikirim_at
                    ->setTimezone($zona)
                    ->format('d-m-Y H:i') }}.

                @if ($pengumpulan->diubah_at)
                    Terakhir diubah pada
                    {{ $pengumpulan->diubah_at
                        ->setTimezone($zona)
                        ->format('d-m-Y H:i') }}.
                @endif
            </p>

            <div class="actions">
                <a
                    class="button"
                    href="{{ route(
                        'pengumpulan.show',
                        $pengumpulan
                    ) }}"
                >
                    Lihat jawaban
                </a>

                @if ($bolehTulis)
                    <a
                        class="button secondary"
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
                        onsubmit="return confirm('Hapus jawaban ini? Tindakan akan dicatat oleh sistem.')"
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
                @endif
            </div>

            @unless ($bolehTulis)
                <p class="muted">
                    Jawaban tidak dapat diubah karena waktu pengumpulan
                    telah berakhir atau status akademik tidak aktif.
                </p>
            @endunless
        </section>
    @elseif ($dibatalkan)
        <section class="card">
            <h2>Jawaban telah dihapus</h2>

            <p class="notice error">
                Jawaban ini dibatalkan pada
                {{ $pengumpulan->dibatalkan_at
                    ? $pengumpulan->dibatalkan_at
                        ->setTimezone($zona)
                        ->format('d-m-Y H:i')
                    : 'waktu yang tidak tersedia' }}.
            </p>

            <a
                href="{{ route(
                    'pengumpulan.show',
                    $pengumpulan
                ) }}"
            >
                Lihat riwayat jawaban
            </a>
        </section>
    @elseif ($bolehTulis)
        <form
            method="post"
            action="{{ route(
                'pengumpulan.store',
                $kegiatan
            ) }}"
            enctype="multipart/form-data"
            class="card form-card"
        >
            @csrf

            <h2>Kirim jawaban</h2>

            <div class="field">
                <label for="jawaban_teks">
                    Pesan untuk dosen
                </label>

                <textarea
                    id="jawaban_teks"
                    name="jawaban_teks"
                    rows="5"
                    maxlength="{{ config(
                        'pengumpulan.maks_karakter_jawaban',
                        10000
                    ) }}"
                    placeholder="Opsional. Contoh: Izin mengumpulkan tugas."
                >{{ old('jawaban_teks') }}</textarea>

                <small>
                    Pesan boleh dikosongkan jika Anda mengunggah berkas.
                </small>
            </div>

            <div class="field">
                <label for="berkas_baru">
                    Pilih berkas jawaban
                </label>

                <input
                    id="berkas_baru"
                    name="berkas_baru[]"
                    type="file"
                    multiple
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"
                >

                <small>
                    Format: {{ $format }}.
                    Maksimal {{ $kegiatan->maks_berkas }} berkas,
                    masing-masing maksimal
                    {{ (int) (
                        $kegiatan->maks_ukuran_byte / 1048576
                    ) }} MB.
                </small>
            </div>

            <div class="actions">
                <button type="submit">
                    Kirim jawaban
                </button>

                <a href="{{ route('kegiatan.show', $kegiatan) }}">
                    Batal
                </a>
            </div>

            <p class="notice">
                Setelah dikirim, jawaban masih dapat diedit atau
                dihapus selama tenggat belum berakhir.
            </p>
        </form>
    @else
        <section class="card">
            <h2>Pengumpulan tidak tersedia</h2>

            <p class="notice error">
                Waktu pengumpulan belum dimulai, telah berakhir,
                atau status keikutsertaan kelas Anda tidak aktif.
            </p>
        </section>
    @endif
@endsection