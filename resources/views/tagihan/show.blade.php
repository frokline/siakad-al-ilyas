@extends('layouts.admin')

@section('title', 'Detail Tagihan')

@section('content')
    @php
        $zona = 'Asia/Makassar';
        $nama = $tagihan->snapshot['nama'] ?? $tagihan->mahasiswa->user->nama;
        $nim = $tagihan->snapshot['nim'] ?? $tagihan->mahasiswa->nim;
        $jenisKode = $tagihan->snapshot['jenis_kode'] ?? $tagihan->jenisBiaya->kode;
        $jenisNama = $tagihan->snapshot['jenis_nama'] ?? $tagihan->jenisBiaya->nama;
        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
        $aksiDaftar = [
            'terbitkan' => ['terbitkan', 'Terbitkan', 'emerald'],
            'batalkan' => ['batalkan', 'Batalkan', 'rose'],
            'buka-draf' => ['bukaDraf', 'Buka kembali sebagai draf', 'amber'],
        ];
        $warnaAksi = [
            'emerald' => [
                'border-emerald-200',
                'border-emerald-100 bg-emerald-50',
                'text-siakad-dark',
                'bg-siakad-dark hover:bg-emerald-800',
            ],
            'rose' => [
                'border-rose-200',
                'border-rose-100 bg-rose-50',
                'text-rose-700',
                'bg-rose-600 hover:bg-rose-700',
            ],
            'amber' => [
                'border-amber-200',
                'border-amber-100 bg-amber-50',
                'text-amber-800',
                'bg-amber-600 hover:bg-amber-700',
            ],
        ];
    @endphp

    @include('tagihan._pesan')

    {{-- Kepala halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('tagihan.index') }}" class="hover:text-siakad-active">Tagihan</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Detail</span>
            </nav>
            <div class="mb-2">@include('tagihan._lencana', ['status' => $tagihan->status])</div>
            <h1 class="break-words text-2xl font-bold text-slate-800">{{ $tagihan->nomor }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $jenisNama }} &middot;
                {{ $namaBulan[(int) $tagihan->bulan_tagihan] ?? sprintf('%02d', $tagihan->bulan_tagihan) }}
                {{ $tagihan->tahun_tagihan }}</p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ route('tagihan.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
                Daftar tagihan</a>
            @can('update', $tagihan)
                <a href="{{ route('tagihan.edit', $tagihan) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Edit
                    draf</a>
            @endcan
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Rincian --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Rincian tagihan</h2>
                </div>
                <dl class="grid gap-x-6 gap-y-5 p-6 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-500">Mahasiswa</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $nama }}</dd>
                        <dd class="text-xs text-slate-500">{{ $nim }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Jenis biaya</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $jenisKode }} &mdash; {{ $jenisNama }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Bulan kewajiban</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ sprintf('%02d/%04d', $tagihan->bulan_tagihan, $tagihan->tahun_tagihan) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Registrasi semester</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">#{{ $tagihan->registrasi_semester_id }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-slate-500">Catatan</dt>
                        <dd class="mt-0.5 whitespace-pre-line break-words text-slate-700">
                            {{ $tagihan->catatan ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Status pembayaran (komponen bawaan, akan dirapikan bersama modul pembayaran) --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Pembayaran</h2>
                </div>
                <div class="p-6">
                    <x-status-pembayaran :tagihan="$tagihan" />
                </div>
            </section>

            {{-- Tindakan status --}}
            @foreach ($aksiDaftar as $rute => $aksi)
                @can($aksi[0], $tagihan)
                    @php $w = $warnaAksi[$aksi[2]]; @endphp
                    <form method="post" action="{{ route('tagihan.' . $rute, $tagihan) }}"
                        class="overflow-hidden rounded-2xl border bg-white shadow-sm {{ $w[0] }}">
                        @csrf
                        <input type="hidden" name="versi" value="{{ $tagihan->versiForm() }}">
                        <div class="border-b px-6 py-4 {{ $w[1] }}">
                            <h2 class="text-sm font-bold uppercase tracking-wider {{ $w[2] }}">{{ $aksi[1] }}
                            </h2>
                        </div>
                        <div class="space-y-4 p-6">
                            <div>
                                <label for="alasan-{{ $rute }}"
                                    class="mb-1.5 block text-sm font-semibold text-slate-700">Alasan (internal) <span
                                        class="text-rose-500">*</span></label>
                                <textarea id="alasan-{{ $rute }}" name="alasan" minlength="10" maxlength="1000" rows="3" required
                                    class="{{ $input }}" placeholder="Tulis alasan, minimal 10 karakter"></textarea>
                            </div>
                            <label class="flex cursor-pointer items-start gap-2.5 text-sm text-slate-700">
                                <input type="checkbox" name="konfirmasi" value="1" required
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-siakad-active focus:ring-siakad-active">
                                Saya sudah memeriksa mahasiswa, bulan, nominal, dan dampak tindakan ini.
                            </label>
                            <button type="submit"
                                class="rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-sm transition {{ $w[3] }}">{{ $aksi[1] }}</button>
                        </div>
                    </form>
                @endcan
            @endforeach

            {{-- Audit --}}
            @can('audit', $tagihan)
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Riwayat perubahan
                            internal</h2>
                        <p class="mt-1 text-xs text-slate-500">Pembatalan atau koreksi dihentikan jika sudah ada
                            riwayat pembayaran. Buka draf memakai nomor dan record yang sama; snapshot terdahulu tetap
                            ada di audit.</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($audit as $a)
                            <details class="group px-6 py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm">
                                    <span class="min-w-0">
                                        <span class="font-semibold text-slate-800">Revisi {{ $a->versi_entitas }}</span>
                                        <span class="text-slate-500">&middot; {{ $a->aksi }} &middot;
                                            {{ $a->pelaku?->nama ?? '—' }}</span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <p class="mt-3 text-xs text-slate-500">{{ $a->alasan }}</p>
                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    <div>
                                        <h3 class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">
                                            Sebelum</h3>
                                        <pre class="max-h-64 overflow-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-700">{{ json_encode($a->sebelum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    <div>
                                        <h3 class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">
                                            Sesudah</h3>
                                        <pre class="max-h-64 overflow-auto rounded-lg bg-emerald-50 p-3 text-xs text-slate-700">{{ json_encode($a->sesudah, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                </div>
                            </details>
                        @empty
                            <p class="px-6 py-8 text-center text-sm text-slate-500">Belum ada catatan audit.</p>
                        @endforelse
                    </div>
                    {{ $audit->links('tagihan._pagination') }}
                </section>
            @endcan
        </div>

        {{-- Panel samping --}}
        <aside class="space-y-4">
            <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-200">Total tagihan</h2>
                <p class="mt-3 break-words text-3xl font-bold">{{ $tagihan->nominalRupiah() }}</p>
                <div class="mt-5 border-t border-white/10 pt-4">
                    <p class="text-xs text-emerald-200">Jatuh tempo</p>
                    <p class="text-lg font-bold">
                        {{ $tagihan->jatuh_tempo->locale('id')->translatedFormat('d F Y') }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Informasi</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">Status</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ \App\Models\Tagihan::STATUS[$tagihan->status] }} &middot; Revisi
                            {{ $tagihan->revisi }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Diterbitkan</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ $tagihan->diterbitkan_at?->setTimezone($zona)->format('d-m-Y H:i') ?? '—' }}
                            @if ($tagihan->diterbitkan_at)
                                WITA
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
@endsection
