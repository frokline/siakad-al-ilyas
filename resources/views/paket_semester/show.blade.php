@extends('layouts.siakad')

@section('title', 'Detail Paket Semester')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $paketSemester->nama }}</h1>

            <p class="subtitle">
                Semester {{ $paketSemester->semester_studi }}
                — Versi {{ $paketSemester->versi }}
            </p>
        </div>

        <div class="actions">
            @if ($paketSemester->isDraf())
                <a class="button" href="{{ route('admin.paket-semester.edit', $paketSemester) }}">
                    Edit Paket
                </a>
            @endif

            <a class="button secondary" href="{{ route('admin.paket-semester.index') }}">
                Kembali
            </a>
        </div>
    </div>

    <section class="card">
        <div class="panel-body">
            <dl class="detail-grid">
                <div>
                    <dt>Program studi</dt>
                    <dd>{{ $paketSemester->kurikulum->programStudi->nama }}</dd>
                </div>

                <div>
                    <dt>Kurikulum</dt>
                    <dd>
                        <a href="{{ route('admin.kurikulum.show', $paketSemester->kurikulum) }}">
                            {{ $paketSemester->kurikulum->kode }}
                            — {{ $paketSemester->kurikulum->nama }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt>Semester studi</dt>
                    <dd>{{ $paketSemester->semester_studi }}</dd>
                </div>

                <div>
                    <dt>Versi paket</dt>
                    <dd>{{ $paketSemester->versi }}</dd>
                </div>

                <div>
                    <dt>Jumlah mata kuliah</dt>
                    <dd>{{ $paketSemester->details->count() }}</dd>
                </div>

                <div>
                    <dt>Total SKS paket</dt>
                    <dd>{{ number_format((float) $totalSks, 1, ',', '.') }}</dd>
                </div>

                <div>
                    <dt>Status</dt>
                    <dd>
                        <span class="badge" data-paket-status="{{ $paketSemester->status }}">
                            {{ $statusOptions[$paketSemester->status] }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="card-header">
            <h2>Susunan Mata Kuliah</h2>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Mata Kuliah</th>
                        <th scope="col">SKS</th>
                        <th scope="col">Sifat</th>
                        <th scope="col">Status Mata Kuliah</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($paketSemester->details as $detail)
                        @php
                            $item = $detail->kurikulumMataKuliah;
                            $mataKuliah = $item->mataKuliah;
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <a href="{{ route('admin.mata-kuliah.show', $mataKuliah) }}">
                                    {{ $mataKuliah->kode }}
                                </a>
                            </td>

                            <td>{{ $mataKuliah->nama }}</td>

                            <td>
                                {{ number_format((float) $item->sks, 1, ',', '.') }}
                            </td>

                            <td>
                                {{ $item->sifat === 'wajib' ? 'Wajib' : 'Pilihan' }}
                            </td>

                            <td>
                                <span @class([
                                    'badge',
                                    'badge-active' => $mataKuliah->aktif,
                                    'badge-inactive' => !$mataKuliah->aktif,
                                ])>
                                    {{ $mataKuliah->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                Paket belum memiliki mata kuliah.
                                Gunakan Edit Paket untuk memilih mata kuliah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($paketSemester->isDraf())
        <section class="card paket-action-card">
            <div class="panel-body">
                <h2>Terbitkan Paket</h2>

                <p>
                    Setelah diterbitkan, nama dan susunan mata kuliah dikunci.
                    Perubahan berikutnya menggunakan versi paket baru.
                </p>

                @if ($paketSemester->details->isEmpty())
                    <p class="field-error">
                        Tambahkan mata kuliah sebelum menerbitkan paket.
                    </p>
                @endif

                @if (!$indukAktif)
                    <p class="field-error">
                        Kurikulum dan program studi harus aktif.
                    </p>
                @endif

                @if ($adaMataKuliahNonaktif)
                    <p class="field-error">
                        Ada mata kuliah nonaktif di dalam paket.
                        Perbaiki susunan paket atau aktifkan mata kuliah tersebut.
                    </p>
                @endif

                <form method="POST" action="{{ route('admin.paket-semester.terbitkan', $paketSemester) }}">
                    @csrf

                    <input type="hidden" name="version" value="{{ $version }}">

                    <label class="paket-confirm" for="konfirmasi-terbitkan">
                        <input class="paket-checkbox" id="konfirmasi-terbitkan" name="konfirmasi" type="checkbox"
                            value="1" required @disabled(!$dapatTerbit)>

                        <span>
                            Saya sudah memeriksa mata kuliah dan total SKS,
                            serta menyetujui penerbitan paket.
                        </span>
                    </label>

                    <button class="button" type="submit" @disabled(!$dapatTerbit)>
                        Terbitkan Paket
                    </button>
                </form>
            </div>
        </section>
    @endif

    @if (!$paketSemester->isArsip())
        <section class="card paket-action-card">
            <div class="panel-body">
                <h2>Arsipkan Paket</h2>

                <p>
                    Paket arsip tetap tersimpan.
                    Paket tidak dapat diedit atau diterbitkan kembali.
                </p>

                <form method="POST" action="{{ route('admin.paket-semester.arsipkan', $paketSemester) }}">
                    @csrf

                    <input type="hidden" name="version" value="{{ $version }}">

                    <label class="paket-confirm" for="konfirmasi-arsipkan">
                        <input class="paket-checkbox" id="konfirmasi-arsipkan" name="konfirmasi" type="checkbox"
                            value="1" required>

                        <span>Saya menyetujui pengarsipan paket ini.</span>
                    </label>

                    <button class="button danger" type="submit">
                        Arsipkan Paket
                    </button>
                </form>
            </div>
        </section>
    @else
        <div class="alert paket-action-card">
            Paket ini sudah diarsipkan. Data dipertahankan sebagai catatan akademik.
        </div>
    @endif
@endsection
