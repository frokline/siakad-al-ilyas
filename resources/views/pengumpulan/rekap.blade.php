@extends('layouts.pengumpulan')

@section('title', 'Rekap Jawaban Mahasiswa')

@section('content')
    <div class="heading">
        <div>
            <p>Rekap dosen</p>
            <h1>Rekap jawaban mahasiswa</h1>

            <p>
                {{ $kegiatan->judul }}
                —
                {{ $kegiatan->kelasKuliah?->kode ?? 'Kelas' }}
            </p>
        </div>

        <a href="{{ route('kegiatan.show', $kegiatan) }}">
            Kembali ke pembelajaran
        </a>
    </div>

    <section class="card">
        <h2>Ringkasan</h2>

        <dl class="metadata">
            <div>
                <dt>Jumlah peserta</dt>
                <dd>{{ $total }}</dd>
            </div>

            <div>
                <dt>Sudah mengumpulkan</dt>
                <dd>{{ $sudah }}</dd>
            </div>

            <div>
                <dt>Belum mengumpulkan</dt>
                <dd>{{ max(0, $total - $sudah) }}</dd>
            </div>

            <div>
                <dt>Persentase</dt>
                <dd>
                    {{ $total > 0
                        ? number_format(
                            ($sudah / $total) * 100,
                            1,
                            ',',
                            '.'
                        )
                        : '0' }}%
                </dd>
            </div>
        </dl>
    </section>

    <form
        method="get"
        action="{{ route(
            'pengumpulan.rekap',
            $kegiatan
        ) }}"
        class="card form-card"
    >
        <h2>Filter peserta</h2>

        <div class="field">
            <label for="q">
                Nama atau NIM
            </label>

            <input
                id="q"
                name="q"
                type="search"
                maxlength="100"
                value="{{ $filter['q'] ?? '' }}"
            >
        </div>

        <div class="field">
            <label for="status">
                Status pengumpulan
            </label>

            <select id="status" name="status">
                <option value="">
                    Semua peserta
                </option>

                <option
                    value="sudah"
                    @selected(
                        ($filter['status'] ?? '') === 'sudah'
                    )
                >
                    Sudah mengumpulkan
                </option>

                <option
                    value="belum"
                    @selected(
                        ($filter['status'] ?? '') === 'belum'
                    )
                >
                    Belum mengumpulkan
                </option>
            </select>
        </div>

        <div class="actions">
            <button type="submit">
                Terapkan
            </button>

            <a href="{{ route(
                'pengumpulan.rekap',
                $kegiatan
            ) }}">
                Reset
            </a>
        </div>
    </form>

    <section class="card table-wrap">
        <h2>Daftar peserta</h2>

        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama mahasiswa</th>
                    <th>Status</th>
                    <th>Waktu pengumpulan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($peserta as $item)
                    <tr>
                        <td>{{ $item->nim }}</td>
                        <td>{{ $item->nama }}</td>

                        <td>
                            @if ($item->pengumpulan_id)
                                Sudah mengumpulkan
                            @else
                                Belum mengumpulkan
                            @endif
                        </td>

                        <td>
                            @if ($item->diubah_at)
                                Diubah:
                                {{ \Illuminate\Support\Carbon::parse(
                                    $item->diubah_at
                                )
                                    ->setTimezone($zona)
                                    ->format('d-m-Y H:i') }}
                            @elseif ($item->dikirim_at)
                                {{ \Illuminate\Support\Carbon::parse(
                                    $item->dikirim_at
                                )
                                    ->setTimezone($zona)
                                    ->format('d-m-Y H:i') }}
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            @if ($item->pengumpulan_id)
                                <a href="{{ route(
                                    'pengumpulan.show',
                                    $item->pengumpulan_id
                                ) }}">
                                    Lihat jawaban
                                </a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            Tidak ada peserta sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $peserta->links('pengumpulan._pagination') }}
    </section>

    <section class="card table-wrap">
        <h2>Riwayat pengumpulan</h2>

        <p class="muted">
            Riwayat tetap disimpan untuk keperluan pemeriksaan,
            termasuk jawaban yang pernah dibatalkan.
        </p>

        <table>
            <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>Status</th>
                    <th>Dikirim</th>
                    <th>Terakhir diubah</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($historis as $item)
                    <tr>
                        <td>
                            {{ $item->pemilik?->nama ?? '—' }}
                        </td>

                        <td>
                            {{ \App\Models\Pengumpulan::STATUS[
                                $item->status
                            ] ?? $item->status }}
                        </td>

                        <td>
                            {{ $item->dikirim_at
                                ? $item->dikirim_at
                                    ->setTimezone($zona)
                                    ->format('d-m-Y H:i')
                                : '—' }}
                        </td>

                        <td>
                            @if ($item->dibatalkan_at)
                                Dibatalkan:
                                {{ $item->dibatalkan_at
                                    ->setTimezone($zona)
                                    ->format('d-m-Y H:i') }}
                            @elseif ($item->diubah_at)
                                {{ $item->diubah_at
                                    ->setTimezone($zona)
                                    ->format('d-m-Y H:i') }}
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            <a href="{{ route(
                                'pengumpulan.show',
                                $item
                            ) }}">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            Belum ada riwayat pengumpulan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $historis->links('pengumpulan._pagination') }}
    </section>
@endsection