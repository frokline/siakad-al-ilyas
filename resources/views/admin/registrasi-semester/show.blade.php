@extends('layouts.siakad')

@section('title', 'Detail Registrasi Semester')

@section('content')
    <div class="page-heading">
        <div>
            <h1>Detail Registrasi Semester</h1>
            <p class="subtitle">Periode {{ $registrasi->periodeAkademik->kode }}</p>
        </div>
        <div class="actions">
            @if ($registrasi->dapatDiubah())
                <a class="button" href="{{ route('admin.registrasi-semester.edit', $registrasi) }}">Edit registrasi</a>
            @endif
            <a class="button secondary" href="{{ route('admin.registrasi-semester.index') }}">Kembali</a>
        </div>
    </div>

    <section class="card">
        <div class="card-header">
            <h2>Identitas mahasiswa</h2>
        </div>
        <div class="panel-body">
            @include('admin.registrasi-semester._identitas')
        </div>
    </section>

    <section class="card registrasi-section">
        <div class="card-header">
            <h2>Data semester</h2>
            <span class="badge" data-registrasi-status="{{ $registrasi->status }}">
                {{ \App\Models\RegistrasiSemester::STATUS[$registrasi->status] }}
            </span>
        </div>
        <div class="panel-body">
            <dl class="detail-grid">
                <div>
                    <dt>Periode akademik</dt>
                    <dd>
                        {{ $registrasi->periodeAkademik->kode }}
                        ({{ \App\Models\PeriodeAkademik::STATUS[$registrasi->periodeAkademik->status] ?? $registrasi->periodeAkademik->status }})
                    </dd>
                </div>
                <div>
                    <dt>Semester studi</dt>
                    <dd>{{ $registrasi->semester_studi }}</dd>
                </div>
                <div>
                    <dt>Rombel</dt>
                    <dd>
                        <a href="{{ route('admin.rombel.show', $registrasi->rombel) }}">
                            {{ $registrasi->rombel->kode }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt>Paket semester</dt>
                    <dd>
                        <a href="{{ route('admin.paket-semester.show', $registrasi->rombel->paketSemester) }}">
                            {{ $registrasi->rombel->paketSemester->nama }}
                        </a>
                        — versi {{ $registrasi->rombel->paketSemester->versi }}
                    </dd>
                </div>
                <div>
                    <dt>Kursi rombel</dt>
                    <dd>
                        {{ $registrasi->rombel->kursi_terpakai }} terisi
                        / {{ $registrasi->rombel->kapasitas ?? 'tanpa batas kapasitas' }}
                    </dd>
                </div>
                <div>
                    <dt>Penempatan dikunci</dt>
                    <dd>
                        @if ($registrasi->penempatan_dikunci_at)
                            {{ $registrasi->penempatan_dikunci_at->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i') }}
                        @else
                            Belum; penempatan dapat dikoreksi sebelum aktivasi pertama.
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Dibuat</dt>
                    <dd>{{ $registrasi->created_at->copy()->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i') }}
                    </dd>
                </div>
                <div>
                    <dt>Revisi data</dt>
                    <dd>{{ $registrasi->revisi }}</dd>
                </div>
            </dl>

            @if ($registrasi->alasan_status !== null)
                <h3>Alasan {{ strtolower(\App\Models\RegistrasiSemester::STATUS[$registrasi->status]) }}</h3>
                <p class="detail-multiline">{{ $registrasi->alasan_status }}</p>
            @endif

            @unless ($registrasi->dapatDiubah())
                <p class="help">Data hanya dapat dibaca karena periode telah diarsipkan atau riwayat studi telah ditutup.</p>
            @endunless

            <p class="help">
                Registrasi semester belum membentuk peserta kelas. Keanggotaan kelas akan mengikuti pengesahan KRS.
            </p>
        </div>
    </section>
@endsection
