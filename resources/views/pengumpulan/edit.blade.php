@extends('layouts.pengumpulan')

@section('title', 'Edit Jawaban')

@section('content')
    @php
        $kegiatan = $pengumpulan->kegiatan;
        $lampiran = $pengumpulan->lampiran;

        $format = strtoupper(
            implode(', ', $kegiatan->ekstensi_diizinkan)
        );
    @endphp

    <div class="heading">
        <div>
            <p>Pembelajaran mahasiswa</p>
            <h1>Edit jawaban</h1>
            <p>{{ $kegiatan->judul }}</p>
        </div>

        <a href="{{ route(
            'pengumpulan.show',
            $pengumpulan
        ) }}">
            Kembali
        </a>
    </div>

    @include('pengumpulan._jadwal', [
        'kegiatan' => $kegiatan,
    ])

    <form
        method="post"
        action="{{ route(
            'pengumpulan.update',
            $pengumpulan
        ) }}"
        enctype="multipart/form-data"
        class="card form-card"
    >
        @csrf
        @method('PATCH')

        <input
            type="hidden"
            name="versi_form"
            value="{{ old(
                'versi_form',
                $pengumpulan->versiForm()
            ) }}"
        >

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
                placeholder="Pesan bersifat opsional."
            >{{ old(
                'jawaban_teks',
                $pengumpulan->jawaban_teks
            ) }}</textarea>

            <small>
                Pesan boleh dikosongkan selama masih ada berkas jawaban.
            </small>
        </div>

        <div class="field">
            <label for="berkas_baru">
                Tambahkan berkas
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

        @if ($lampiran->isNotEmpty())
            <section class="field">
                <h2>Berkas saat ini</h2>

                <p class="muted">
                    Centang berkas yang ingin dilepas dari jawaban.
                </p>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Berkas</th>
                                <th>Ukuran</th>
                                <th>Hapus</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($lampiran as $item)
                                <tr>
                                    <td>
                                        {{ $item->nama_asli }}
                                    </td>

                                    <td>
                                        {{ number_format(
                                            $item->ukuran_byte / 1048576,
                                            2,
                                            ',',
                                            '.'
                                        ) }} MB
                                    </td>

                                    <td>
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="lampiran_dihapus[]"
                                                value="{{ $item->id }}"
                                                @checked(
                                                    in_array(
                                                        (string) $item->id,
                                                        array_map(
                                                            'strval',
                                                            (array) old(
                                                                'lampiran_dihapus',
                                                                []
                                                            )
                                                        ),
                                                        true
                                                    )
                                                )
                                            >

                                            Lepas
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <div class="actions">
            <button type="submit">
                Simpan perubahan
            </button>

            <a href="{{ route(
                'pengumpulan.show',
                $pengumpulan
            ) }}">
                Batal
            </a>
        </div>

        <p class="notice">
            Perubahan langsung menjadi jawaban terbaru.
            Tidak ada proses simpan draf atau kirim final kedua.
        </p>
    </form>
@endsection