@extends('layouts.admin')

@section('title', $agenda->judul)

@section('content')
    @php
        $zona = \App\Models\KalenderAkademik::ZONA;
        $mulai = $agenda->mulai_at->setTimezone($zona);
        $selesai = $agenda->selesai_at->setTimezone($zona);
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-siakad-dark focus:ring-2 focus:ring-siakad-dark/20';
    @endphp

    @include('kalender_akademik._pesan')

    {{-- Kepala halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('kalender.index', ['bulan' => $mulai->format('Y-m')]) }}"
                    class="hover:text-siakad-active">Kalender akademik</a>
                <span>/</span>
                <span class="font-medium text-slate-700">Detail agenda</span>
            </nav>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                @include('kalender_akademik._lencana', ['tipe' => 'status', 'nilai' => $agenda->status])
                @include('kalender_akademik._lencana', ['tipe' => 'jenis', 'nilai' => $agenda->jenis])
            </div>
            <h1 class="break-words text-2xl font-bold text-slate-800">{{ $agenda->judul }}</h1>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ route('kalender.index', ['bulan' => $mulai->format('Y-m')]) }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">&larr;
                Kembali ke kalender</a>
            @can('update', $agenda)
                <a href="{{ route('kalender.edit', $agenda) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Edit
                    agenda</a>
            @endcan
        </div>
    </div>

    @if ($agenda->status === 'batal')
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
            <strong>Agenda dibatalkan.</strong> Waktu di bawah merupakan jadwal yang sudah tidak berlaku.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Isi agenda --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-6 py-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-siakad-dark">Keterangan</h2>
                </div>
                <div class="space-y-5 p-6">
                    <div class="whitespace-pre-line break-words text-sm leading-relaxed text-slate-700">
                        {{ $agenda->keterangan ?? 'Tidak ada keterangan tambahan.' }}</div>

                    @if ($agenda->catatan_perubahan)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-800">Catatan perubahan
                                terakhir</h3>
                            <p class="mt-1.5 whitespace-pre-line break-words text-sm text-amber-900">
                                {{ $agenda->catatan_perubahan }}</p>
                        </div>
                    @endif

                    @if ($agenda->jenis === 'krs')
                        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
                            Agenda KRS ini bersifat informatif. Batas pengisian/pengesahan KRS mengikuti aturan Periode
                            Akademik.
                        </div>
                    @endif
                </div>
            </section>

            {{-- Tindakan status --}}
            @foreach (['terbitkan' => 'Terbitkan agenda', 'batalkan' => 'Batalkan agenda'] as $aksi => $judulAksi)
                @can($aksi, $agenda)
                    @php $bahaya = $aksi === 'batalkan'; @endphp
                    <form method="post" action="{{ route('kalender.tindakan', $agenda) }}"
                        class="overflow-hidden rounded-2xl border bg-white shadow-sm {{ $bahaya ? 'border-rose-200' : 'border-emerald-200' }}">
                        @csrf
                        <div
                            class="border-b px-6 py-4 {{ $bahaya ? 'border-rose-100 bg-rose-50' : 'border-emerald-100 bg-emerald-50' }}">
                            <h2
                                class="text-sm font-bold uppercase tracking-wider {{ $bahaya ? 'text-rose-700' : 'text-siakad-dark' }}">
                                {{ $judulAksi }}</h2>
                        </div>
                        <div class="space-y-4 p-6">
                            <input type="hidden" name="aksi" value="{{ $aksi }}">
                            <input type="hidden" name="versi" value="{{ old('versi', $agenda->versiForm()) }}">

                            <div>
                                <label for="alasan-{{ $aksi }}"
                                    class="mb-1.5 block text-sm font-semibold text-slate-700">Alasan <span
                                        class="text-rose-500">*</span></label>
                                <textarea id="alasan-{{ $aksi }}" name="alasan" minlength="10" maxlength="1000" rows="3" required
                                    class="{{ $input }}" placeholder="Tulis alasan, minimal 10 karakter">{{ old('alasan') }}</textarea>
                                <p class="mt-1.5 text-xs text-slate-500">Alasan tampil pada catatan agenda. Agenda
                                    terbit yang dibatalkan tetap terlihat dengan penanda pembatalan.</p>
                            </div>

                            <label class="flex cursor-pointer items-start gap-2.5 text-sm text-slate-700">
                                <input type="checkbox" name="konfirmasi" value="1" required
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-siakad-active focus:ring-siakad-active">
                                Saya sudah memeriksa tindakan dan informasi agenda.
                            </label>

                            <button type="submit"
                                class="rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-sm transition {{ $bahaya ? 'bg-rose-600 hover:bg-rose-700' : 'bg-siakad-dark hover:bg-emerald-800' }}">{{ $judulAksi }}</button>
                        </div>
                    </form>
                @endcan
            @endforeach

            {{-- Audit --}}
            @if ($audit)
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Audit admin</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($audit as $a)
                            <details class="group px-6 py-4">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm">
                                    <span class="min-w-0">
                                        <span class="font-semibold text-slate-800">Revisi
                                            {{ $a->versi_entitas }}</span>
                                        <span class="text-slate-500">&middot; {{ $a->aksi }} &middot;
                                            {{ $a->pelaku?->nama ?? 'Petugas' }}</span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <p class="mt-3 text-xs text-slate-500">
                                    {{ $a->waktu->setTimezone($zona)->format('d-m-Y H:i') }} WITA &middot;
                                    {{ $a->alasan }}</p>
                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    <div>
                                        <h3 class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">
                                            Sebelum</h3>
                                        <pre class="max-h-64 overflow-auto rounded-lg bg-slate-50 p-3 text-xs text-slate-700">{{ json_encode($a->sebelum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                    <div>
                                        <h3 class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">
                                            Sesudah</h3>
                                        <pre class="max-h-64 overflow-auto rounded-lg bg-emerald-50 p-3 text-xs text-slate-700">{{ json_encode($a->sesudah, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </div>
                            </details>
                        @empty
                            <p class="px-6 py-8 text-center text-sm text-slate-500">Belum ada catatan audit.</p>
                        @endforelse
                    </div>
                    {{ $audit->links('kalender_akademik._pagination') }}
                </section>
            @endif
        </div>

        {{-- Panel samping --}}
        <aside class="space-y-4">
            <div class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-200">Waktu pelaksanaan</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <p class="text-xs text-emerald-200">Mulai</p>
                        <p class="text-lg font-bold">{{ $mulai->locale('id')->translatedFormat('d F Y') }}</p>
                        <p class="text-sm text-emerald-100">{{ $mulai->format('H:i') }} WITA</p>
                    </div>
                    <div class="border-t border-white/10 pt-4">
                        <p class="text-xs text-emerald-200">Selesai</p>
                        <p class="text-lg font-bold">{{ $selesai->locale('id')->translatedFormat('d F Y') }}</p>
                        <p class="text-sm text-emerald-100">{{ $selesai->format('H:i') }} WITA</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Informasi</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">Jenis</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ \App\Models\KalenderAkademik::JENIS[$agenda->jenis] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Periode</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">{{ $agenda->periodeAkademik->kode }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Sasaran</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ $agenda->programStudi?->nama ?? 'Seluruh kampus' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Terakhir diperbarui</dt>
                        <dd class="mt-0.5 font-semibold text-slate-800">
                            {{ $agenda->updated_at->setTimezone($zona)->format('d-m-Y H:i') }} WITA</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
@endsection
