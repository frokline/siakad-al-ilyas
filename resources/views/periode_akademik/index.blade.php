@extends('layouts.siakad')

@section('title', 'Periode Akademik')

@section('breadcrumb')
    <span>Periode Akademik</span>
@endsection

@section('content')
    <div class="page-heading">
        <div>
            <h1>Periode Akademik</h1>
            <p class="subtitle">Kelola semester, tanggal perkuliahan, dan jadwal KRS.</p>
        </div>

        <a href="{{ route('admin.periode-akademik.create') }}" class="button">
            Tambah Periode
        </a>
    </div>

    <form method="GET" action="{{ route('admin.periode-akademik.index') }}" class="card filters">
        <div class="field">
            <label for="q">Cari periode</label>

            <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                placeholder="2026 atau GANJIL" maxlength="30">
        </div>

        <div class="field">
            <label for="filter-status">Status</label>

            <select id="filter-status" name="status">
                <option value="">Semua status</option>

                @foreach ($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="actions">
            <button type="submit" class="button">Cari</button>

            <a href="{{ route('admin.periode-akademik.index') }}" class="button button-secondary">
                Reset
            </a>
        </div>
    </form>

    <section class="card">
        <div class="card-header">
            <strong>
                {{ number_format($daftarPeriode->total(), 0, ',', '.') }}
                periode akademik
            </strong>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Tahun ajaran</th>
                        <th scope="col">Perkuliahan</th>
                        <th scope="col">Status</th>
                        <th scope="col">KRS saat ini</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftarPeriode as $periode)
                        <tr>
                            <td>{{ $daftarPeriode->firstItem() + $loop->index }}</td>
                            <td>{{ $periode->kode }}</td>
                            <td>{{ $periode->tahunAjaran() }}</td>
                            <td>
                                {{ $periode->mulai->format('d/m/Y') }}
                                —
                                {{ $periode->selesai->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge" data-periode-status="{{ $periode->status }}">
                                    {{ $statusOptions[$periode->status] }}
                                </span>
                            </td>
                            <td>
                                @if ($periode->isKrsOpen())
                                    <span class="badge badge-active">Terbuka</span>
                                @else
                                    <span class="badge badge-inactive">Tertutup</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.periode-akademik.show', $periode) }}"
                                        class="button button-secondary button-small"
                                        aria-label="Detail {{ $periode->kode }}">
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.periode-akademik.edit', $periode) }}"
                                        class="button button-small" aria-label="Edit {{ $periode->kode }}">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">
                                Tidak ada periode akademik yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-pagination :paginator="$daftarPeriode" />
@endsection
