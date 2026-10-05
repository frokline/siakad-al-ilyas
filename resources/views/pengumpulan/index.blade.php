@extends('layouts.pengumpulan')

@section('title', 'Daftar Pengumpulan')

@section('content')
    <div class="heading">
        <div>
            <p>Pembelajaran</p>
            <h1>Daftar pengumpulan</h1>

            <p>
                Daftar jawaban tugas yang dapat Anda akses.
            </p>
        </div>
    </div>

    <form
        method="get"
        action="{{ route('pengumpulan.index') }}"
        class="card form-card"
    >
        <h2>Cari pengumpulan</h2>

        <div class="field">
            <label for="q">
                Judul pembelajaran
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
                Status jawaban
            </label>

            <select id="status" name="status">
                <option value="">
                    Semua status
                </option>

                <option
                    value="berlaku"
                    @selected(
                        ($filter['status'] ?? '') === 'berlaku'
                    )
                >
                    Jawaban aktif
                </option>

                <option
                    value="{{ \App\Models\Pengumpulan::TERKIRIM }}"
                    @selected(
                        ($filter['status'] ?? '')
                            === \App\Models\Pengumpulan::TERKIRIM
                    )
                >
                    Terkirim
                </option>

                <option
                    value="{{ \App\Models\Pengumpulan::DIBATALKAN }}"
                    @selected(
                        ($filter['status'] ?? '')
                            === \App\Models\Pengumpulan::DIBATALKAN
                    )
                >
                    Dibatalkan
                </option>
            </select>
        </div>

        @if (! empty($filter['kegiatan']))
            <input
                type="hidden"
                name="kegiatan"
                value="{{ $filter['kegiatan'] }}"
            >
        @endif

        <div class="actions">
            <button type="submit">
                Cari
            </button>

            <a href="{{ route('pengumpulan.index') }}">
                Reset
            </a>
        </div>
    </form>

    <section class="card table-wrap">
        <h2>Jawaban</h2>

        <table>
            <thead>
                <tr>
                    <th>Pembelajaran</th>
                    <th>Mahasiswa</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($daftar as $item)
                    @php
                        $status = \App\Models\Pengumpulan::STATUS[
                            $item->status
                        ] ?? $item->status;

                        $aktif = in_array(
                            (int) $item->id,
                            $berlakuIds,
                            true
                        );
                    @endphp

                    <tr>
                        <td>
                            <strong>
                                {{ $item->kegiatan?->judul
                                    ?? 'Pembelajaran tidak tersedia' }}
                            </strong>

                            <br>

                            <small>
                                {{ $item->kegiatan?->kelasKuliah?->kode
                                    ?? '—' }}
                            </small>
                        </td>

                        <td>
                            {{ $item->pemilik?->nama ?? '—' }}
                        </td>

                        <td>
                            {{ $status }}

                            @if ($aktif)
                                <small>— jawaban aktif</small>
                            @endif
                        </td>

                        <td>
                            @if (
                                $item->status
                                    === \App\Models\Pengumpulan::DIBATALKAN
                                && $item->dibatalkan_at
                            )
                                Dibatalkan:
                                {{ $item->dibatalkan_at
                                    ->setTimezone($zona)
                                    ->format('d-m-Y H:i') }}
                            @elseif ($item->diubah_at)
                                Diubah:
                                {{ $item->diubah_at
                                    ->setTimezone($zona)
                                    ->format('d-m-Y H:i') }}
                            @elseif ($item->dikirim_at)
                                Dikirim:
                                {{ $item->dikirim_at
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
                            Belum ada pengumpulan yang dapat ditampilkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $daftar->links('pengumpulan._pagination') }}
    </section>
@endsection