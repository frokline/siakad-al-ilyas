@extends('layouts.siakad')

@section('title', 'Mata Kuliah Kurikulum')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Mata Kuliah Kurikulum</h1>

            <p class="subtitle">
                {{ $kurikulum->kode }} — {{ $kurikulum->nama }}
            </p>

            <p class="help">{{ $kurikulum->programStudi->nama }}</p>
        </div>

        <div class="actions">
            @if ($bisaTambah)
                <a class="button" href="{{ route('admin.kurikulum.mata-kuliah.create', $kurikulum) }}">
                    Tambah Mata Kuliah
                </a>
            @endif

            <a class="button button-secondary" href="{{ route('admin.kurikulum.show', $kurikulum) }}">
                Detail Kurikulum
            </a>
        </div>
    </div>

    @if (!$bisaUbah)
        <div class="alert" role="status">
            Kurikulum aktif/arsip: susunan mata kuliah terkunci.
        </div>
    @elseif (!$kurikulum->programStudi->aktif)
        <div class="alert" role="status">
            Program studi nonaktif. Penambahan mata kuliah belum tersedia.
        </div>
    @endif

    <p class="help">
        Total seluruh kurikulum:
        <strong>{{ $ringkasan->jumlah }} mata kuliah</strong>
        ·
        <strong>{{ number_format((float) $ringkasan->total_sks, 1, ',', '.') }} SKS</strong>.
    </p>

    <div class="card">
        <form class="filters filters-kmk" method="GET"
            action="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}">
            <div class="field">
                <label for="q">Pencarian</label>

                <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" maxlength="150"
                    placeholder="Kode atau nama mata kuliah">
            </div>

            <div class="field">
                <label for="semester">Semester rekomendasi</label>

                <input id="semester" name="semester" type="number" value="{{ $filters['semester'] ?? '' }}" min="1"
                    max="32767" step="1" placeholder="Semua semester">
            </div>

            <div class="field">
                <label for="filter_sifat">Sifat</label>

                <select id="filter_sifat" name="sifat">
                    <option value="">Semua sifat</option>

                    @foreach ($sifatOptions as $kodeSifat => $labelSifat)
                        <option value="{{ $kodeSifat }}" @selected(($filters['sifat'] ?? '') === $kodeSifat)>
                            {{ $labelSifat }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="actions">
                <button class="button" type="submit">Cari</button>

                <a class="button button-secondary" href="{{ route('admin.kurikulum.mata-kuliah.index', $kurikulum) }}">
                    Reset
                </a>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Mata kuliah</th>
                        <th scope="col">SKS</th>
                        <th scope="col">Semester</th>
                        <th scope="col">Sifat</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftarDetail as $item)
                        <tr>
                            <td>{{ $daftarDetail->firstItem() + $loop->index }}</td>
                            <td>{{ $item->mataKuliah->kode }}</td>

                            <td>
                                {{ $item->mataKuliah->nama }}

                                @if (!$item->mataKuliah->aktif)
                                    <span class="badge badge-inactive">Nonaktif</span>
                                @endif
                            </td>

                            <td>{{ str_replace('.', ',', $item->sks) }}</td>
                            <td>{{ $item->semester_rekomendasi }}</td>
                            <td>{{ $sifatOptions[$item->sifat] }}</td>

                            <td>
                                <div class="actions">
                                    <a class="button button-small button-secondary"
                                        href="{{ route('admin.kurikulum.mata-kuliah.show', [$kurikulum, $item]) }}"
                                        aria-label="Detail {{ $item->mataKuliah->kode }}">
                                        Detail
                                    </a>

                                    @if ($bisaUbah)
                                        <a class="button button-small"
                                            href="{{ route('admin.kurikulum.mata-kuliah.edit', [$kurikulum, $item]) }}"
                                            aria-label="Edit {{ $item->mataKuliah->kode }}">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="7">
                                Tidak ada mata kuliah yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$daftarDetail" />
    </div>
@endsection
