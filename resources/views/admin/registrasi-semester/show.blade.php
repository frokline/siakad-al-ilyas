@extends('layouts.admin')

@section('title', 'Detail Registrasi Semester')

@section('content')
    @php
        $zona = config('siakad.timezone', 'Asia/Makassar');
        $rombel = $registrasi->rombel;
        $kapasitas = $rombel->kapasitas;
        $terpakai = (int) $rombel->kursi_terpakai;
        $persen = $kapasitas ? min(100, (int) round(($terpakai / max(1, $kapasitas)) * 100)) : null;
        $kartu = 'rounded-2xl border border-slate-200 bg-white shadow-sm';
        $kepala = 'rounded-t-2xl border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4';
        $judul = 'text-sm font-bold uppercase tracking-wider text-siakad-dark';
        $label = 'text-xs font-bold uppercase tracking-wider text-slate-500';
        $tautan = 'font-medium text-siakad-active underline-offset-2 hover:underline';
        $bisaUbah = $registrasi->dapatDiubah();
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('admin.registrasi-semester.index') }}" class="hover:text-siakad-dark">Registrasi
                    Semester</a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-slate-700">Detail</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900">Detail Registrasi Semester</h1>
            <p class="mt-1 text-sm text-slate-500">Periode {{ $registrasi->periodeAkademik->kode }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.registrasi-semester.index') }}"
                class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Kembali</a>
            @if ($bisaUbah)
                <a href="{{ route('admin.registrasi-semester.edit', $registrasi) }}"
                    class="rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-siakad-active">Edit
                    Registrasi</a>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">

            <section class="{{ $kartu }}">
                <div class="{{ $kepala }}">
                    <h2 class="{{ $judul }}">Identitas mahasiswa</h2>
                </div>
                <div class="p-6">
                    @include('admin.registrasi-semester._identitas')
                </div>
            </section>

            <section class="{{ $kartu }}">
                <div class="{{ $kepala }} flex items-center justify-between">
                    <h2 class="{{ $judul }}">Data semester</h2>
                    @include('admin.registrasi-semester._lencana', ['status' => $registrasi->status])
                </div>
                <dl class="grid gap-x-8 gap-y-5 p-6 sm:grid-cols-2">
                    <div>
                        <dt class="{{ $label }}">Periode akademik</dt>
                        <dd class="mt-1 text-sm text-slate-800">
                            {{ $registrasi->periodeAkademik->kode }}
                            <span
                                class="text-slate-500">({{ \App\Models\PeriodeAkademik::STATUS[$registrasi->periodeAkademik->status] ?? $registrasi->periodeAkademik->status }})</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="{{ $label }}">Semester studi</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">Semester {{ $registrasi->semester_studi }}
                        </dd>
                    </div>
                    <div>
                        <dt class="{{ $label }}">Rombel</dt>
                        <dd class="mt-1 text-sm">
                            @can('kelola-rombel')
                                <a href="{{ route('admin.rombel.show', $rombel) }}"
                                    class="{{ $tautan }}">{{ $rombel->kode }}</a>
                            @else
                                {{ $rombel->kode }}
                            @endcan
                        </dd>
                    </div>
                    <div>
                        <dt class="{{ $label }}">Paket semester</dt>
                        <dd class="mt-1 text-sm">
                            @can('kelola-paket-semester')
                                <a href="{{ route('admin.paket-semester.show', $rombel->paketSemester) }}"
                                    class="{{ $tautan }}">{{ $rombel->paketSemester->nama }}</a>
                            @else
                                {{ $rombel->paketSemester->nama }}
                            @endcan
                            <span class="text-slate-500">· versi {{ $rombel->paketSemester->versi }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="{{ $label }}">Dibuat</dt>
                        <dd class="mt-1 text-sm text-slate-800">
                            {{ $registrasi->created_at->copy()->timezone($zona)->format('d-m-Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $label }}">Revisi data</dt>
                        <dd class="mt-1 text-sm text-slate-800">{{ $registrasi->revisi }}</dd>
                    </div>
                </dl>
            </section>

            @if ($registrasi->alasan_status !== null)
                <section
                    class="rounded-2xl border {{ $registrasi->status === \App\Models\RegistrasiSemester::BATAL ? 'border-rose-200 bg-rose-50' : 'border-amber-200 bg-amber-50' }} p-6">
                    <h2
                        class="text-sm font-bold uppercase tracking-wider {{ $registrasi->status === \App\Models\RegistrasiSemester::BATAL ? 'text-rose-800' : 'text-amber-800' }}">
                        Alasan {{ strtolower(\App\Models\RegistrasiSemester::STATUS[$registrasi->status]) }}
                    </h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-800">{{ $registrasi->alasan_status }}
                    </p>
                </section>
            @endif

            @unless ($bisaUbah)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    Data hanya dapat dibaca karena periode telah diarsipkan atau riwayat studi telah ditutup.
                </div>
            @endunless
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-200">Kursi rombel {{ $rombel->kode }}</p>
                <p class="mt-3 text-3xl font-bold">
                    {{ $terpakai }}<span class="text-lg font-medium text-emerald-200"> /
                        {{ $kapasitas ?? '∞' }}</span>
                </p>
                <p class="text-xs text-emerald-200">{{ $kapasitas === null ? 'Tanpa batas kapasitas' : 'kursi terisi' }}
                </p>
                @if ($persen !== null)
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/15" role="progressbar"
                        aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full rounded-full {{ $persen >= 100 ? 'bg-rose-400' : 'bg-siakad-accent' }}"
                            style="width: {{ $persen }}%"></div>
                    </div>
                @endif
            </section>

            <section class="{{ $kartu }} p-6">
                <p class="{{ $label }}">Penempatan</p>
                @if ($registrasi->penempatan_dikunci_at)
                    <div class="mt-3 flex items-start gap-3">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Terkunci</p>
                            <p class="text-xs text-slate-500">sejak
                                {{ $registrasi->penempatan_dikunci_at->timezone($zona)->format('d-m-Y H:i') }}</p>
                        </div>
                    </div>
                @else
                    <p class="mt-3 text-sm text-slate-600">Belum dikunci. Rombel dapat dikoreksi sebelum aktivasi pertama.
                    </p>
                @endif
            </section>

            <section class="rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sm leading-6 text-sky-900">
                Registrasi semester belum membentuk peserta kelas. Keanggotaan kelas mengikuti pengesahan KRS.
            </section>
        </aside>
    </div>
@endsection
