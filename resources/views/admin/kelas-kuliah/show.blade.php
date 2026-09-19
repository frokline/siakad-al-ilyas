@extends('layouts.siakad')

@section('title', 'Detail Kelas Kuliah')

@section('content')
    <div class="page-heading">
        <div>
            <h1>{{ $kelas->kode }}</h1>
            <p class="subtitle">{{ $kelas->nama_mk_snapshot }}</p>
        </div>
        <div class="actions">
            @if ($kelas->dapatDiubah())
                <a class="button" href="{{ route('admin.kelas-kuliah.edit', $kelas) }}">Edit kelas</a>
            @endif
            <a class="button secondary" href="{{ route('admin.kelas-kuliah.index', ['rombel_id' => $rombel->id]) }}">
                Kelas rombel ini
            </a>
        </div>
    </div>

    <section class="card">
        <div class="card-header">
            <h2>Rombel dan periode</h2>
        </div>
        <div class="panel-body">
            @include('admin.kelas-kuliah._identitas')
        </div>
    </section>

    <section class="card kelas-section">
        <div class="card-header">
            <h2>Data kelas</h2>
            <span class="badge" data-kelas-status="{{ $kelas->status }}">
                {{ \App\Models\KelasKuliah::STATUS[$kelas->status] }}
            </span>
        </div>
        <div class="panel-body">
            <dl class="detail-grid">
                <div>
                    <dt>Kode kelas</dt>
                    <dd>{{ $kelas->kode }}</dd>
                </div>
                <div>
                    <dt>Mata kuliah</dt>
                    <dd>
                        <a
                            href="{{ route('admin.mata-kuliah.show', $kelas->detailPaket->kurikulumMataKuliah->mataKuliah) }}">
                            {{ $kelas->detailPaket->kurikulumMataKuliah->mataKuliah->kode }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt>Nama pada saat penawaran</dt>
                    <dd>{{ $kelas->nama_mk_snapshot }}</dd>
                </div>
                <div>
                    <dt>SKS penawaran</dt>
                    <dd>{{ str_replace('.', ',', $kelas->sks_snapshot) }}</dd>
                </div>
                <div>
                    <dt>Kode dapat dikoreksi</dt>
                    <dd>{{ $kelas->kodeDapatDiubah() ? 'Sebelum aktivasi pertama, selama periode terbuka.' : 'Terkunci sejak aktivasi pertama.' }}
                    </dd>
                </div>
                <div>
                    <dt>Revisi data</dt>
                    <dd>{{ $kelas->revisi }}</dd>
                </div>
                <div>
                    <dt>Dibuat</dt>
                    <dd>{{ $kelas->created_at->copy()->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i') }}
                    </dd>
                </div>
                @foreach ([
            'diaktifkan_at' => 'Diaktifkan',
            'diselesaikan_at' => 'Diselesaikan',
            'diarsipkan_at' => 'Diarsipkan',
        ] as $kolom => $label)
                    <div>
                        <dt>{{ $label }}</dt>
                        <dd>{{ $kelas->{$kolom}?->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i') ?? 'Belum' }}
                        </dd>
                    </div>
                @endforeach
            </dl>

            <p class="help">
                Nama dan SKS penawaran tetap tersimpan untuk riwayat akademik meskipun master mata kuliah berubah.
            </p>

            @unless ($kelas->dapatDiubah())
                <p class="help">
                    Data hanya dapat dibaca karena periode telah diarsipkan atau kelas telah masuk arsip tetap.
                </p>
            @endunless

            @if (in_array($rombel->periodeAkademik->status, \App\Models\Rombel::STATUS_PERIODE_TERBUKA, true))
                <div class="actions kelas-form-actions">
                    <a class="button secondary"
                        href="{{ route('admin.kelas-kuliah.create', ['rombel_id' => $rombel->id]) }}">
                        Siapkan mata kuliah lain dalam rombel ini
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
