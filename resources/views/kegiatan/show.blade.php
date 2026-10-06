@extends('layouts.admin')

@section('title', $kegiatan->judul)

@section('content')
    @include('kegiatan._pesan')

    @php
        $item = $kegiatan;
        $tombolKecil =
            'inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50';
        $namaKolom = [
            'jenis' => 'jenis',
            'judul' => 'judul',
            'instruksi' => 'isi',
            'tautan_eksternal' => 'tautan',
            'pertemuan_id' => 'pertemuan',
            'buka_at' => 'waktu mulai',
            'tenggat_at' => 'tenggat',
            'maks_ukuran_byte' => 'ukuran maks',
            'maks_berkas' => 'jumlah berkas',
            'ekstensi_diizinkan' => 'format jawaban',
            'status' => 'status',
            'berkas_ids' => 'lampiran',
        ];
    @endphp

    <div class="w-full space-y-6">
        {{-- HEADER --}}
        <header class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="mb-3">
                        @include('kegiatan._lencana')
                    </div>

                    <h1 class="break-words text-2xl font-bold tracking-tight text-slate-800 sm:text-3xl">
                        {{ $kegiatan->judul }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500 sm:text-base">
                        <span class="font-semibold text-slate-700">{{ $kegiatan->kelasKuliah->kode }}</span>
                        <span class="mx-1.5 text-slate-300">&middot;</span>
                        {{ $kegiatan->kelasKuliah->nama_mk_snapshot }}
                    </p>
                </div>

                <div class="shrink-0">
                    <a href="{{ route('kegiatan.index', ['kelas' => $kegiatan->kelas_kuliah_id]) }}"
                        class="{{ $tombolKecil }} w-full sm:w-auto">
                        <span class="mr-1.5 text-base leading-none">&larr;</span>
                        Kembali
                    </a>
                </div>
            </div>
        </header>

        {{-- PERINGATAN PERTEMUAN --}}
        @if ($kegiatan->pertemuan?->status === 'batal')
            <div class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 shadow-sm"
                role="alert">
                <span class="mt-0.5 font-bold">!</span>
                <div>
                    <p class="font-semibold">Pertemuan dibatalkan</p>
                    <p class="mt-1">Pembelajaran ini disembunyikan dari mahasiswa.</p>
                </div>
            </div>
        @endif

        {{-- KONTEN --}}
        <div class="grid w-full grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            {{-- KOLOM UTAMA --}}
            <div class="min-w-0 space-y-6">
                @if ($bacaIsi)
                    {{-- ISI / INSTRUKSI --}}
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                            <h2 class="text-sm font-bold text-slate-800">
                                {{ $kegiatan->berupaMateri() ? 'Isi materi' : 'Instruksi pengerjaan' }}
                            </h2>
                        </div>

                        <div class="p-6">
                            @if ($kegiatan->instruksi)
                                <div class="whitespace-pre-wrap break-words text-sm leading-7 text-slate-700">
                                    {{ $kegiatan->instruksi }}
                                </div>
                            @else
                                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5">
                                    <p class="text-sm italic text-slate-500">
                                        Tidak ada pesan atau instruksi tambahan.
                                    </p>
                                </div>
                            @endif

                            @if ($kegiatan->tautan_eksternal)
                                <div class="mt-6 border-t border-slate-100 pt-5">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Tautan tambahan
                                    </p>
                                    <a href="{{ $kegiatan->tautan_eksternal }}" target="_blank" rel="noopener noreferrer"
                                        class="{{ $tombolKecil }} gap-2">
                                        Buka tautan tambahan
                                        <span aria-hidden="true">&#8599;</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </article>

                    {{-- LAMPIRAN --}}
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div
                            class="flex items-center justify-between gap-4 border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                            <div>
                                <h2 class="text-sm font-bold text-slate-800">Lampiran</h2>
                                <p class="mt-1 text-xs text-slate-500">Berkas yang disertakan pada pembelajaran ini.</p>
                            </div>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @forelse ($kegiatan->lampiran as $lampiran)
                                <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <strong class="block truncate text-sm font-semibold text-slate-800">
                                            {{ $lampiran->berkas->label }}
                                        </strong>
                                        <span class="mt-1 block truncate text-xs text-slate-500">
                                            {{ $lampiran->berkas->nama_asli }}
                                            <span class="mx-1 text-slate-300">&middot;</span>
                                            {{ $lampiran->berkas->ukuranLabel() }}
                                        </span>
                                    </div>

                                    @if ($lampiran->berkas->status === \App\Models\Berkas::TERSEDIA)
                                        <form method="post"
                                            action="{{ route('kegiatan.tautan', ['kegiatan' => $kegiatan->id, 'lampiran' => $lampiran->id]) }}"
                                            class="shrink-0">
                                            @csrf
                                            <button type="submit"
                                                class="w-full rounded-lg bg-siakad-active px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-800 sm:w-auto">
                                                Unduh
                                            </button>
                                        </form>
                                    @else
                                        <span
                                            class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs italic text-slate-400">
                                            Tidak tersedia
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <div class="px-6 py-8">
                                    <p class="text-sm italic text-slate-500">Tidak ada lampiran tambahan.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 shadow-sm">
                        <p class="font-semibold">Isi pembelajaran belum dapat dibuka.</p>
                    </div>
                @endif

                {{-- RIWAYAT PERUBAHAN (hanya pengelola) --}}
                @if ($audit !== null)
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-4">
                            <h2 class="text-sm font-bold text-slate-800">Riwayat perubahan</h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Rekam perubahan pada pembelajaran.
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] border-collapse text-left text-sm text-slate-600">
                                <thead
                                    class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 font-semibold">Versi / Waktu</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Pelaku</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Yang berubah</th>
                                        <th scope="col" class="px-6 py-3 font-semibold">Alasan</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($audit as $log)
                                        @php
                                            $sebelum = is_array($log->sebelum) ? $log->sebelum : [];
                                            $sesudah = is_array($log->sesudah) ? $log->sesudah : [];
                                            $berubah = collect($sesudah)
                                                ->filter(fn($nilai, $kunci) => ($sebelum[$kunci] ?? null) !== $nilai)
                                                ->keys()
                                                ->map(fn($kunci) => $namaKolom[$kunci] ?? null)
                                                ->filter()
                                                ->implode(', ');
                                        @endphp

                                        <tr class="align-top transition hover:bg-slate-50/80">
                                            <td class="px-6 py-4">
                                                <strong class="block text-slate-800">V{{ $log->versi_entitas }}</strong>
                                                <span class="mt-1 block font-mono text-xs text-slate-500">
                                                    {{ $log->waktu->setTimezone($zona)->format('d-m-Y H:i') }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4">
                                                <div class="font-medium text-slate-800">
                                                    {{ $log->pelaku?->nama ?? 'Sistem' }}
                                                </div>
                                                <span
                                                    class="mt-1 inline-flex rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                    {{ $log->aksi }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 text-xs">
                                                {{ $berubah !== '' ? ucfirst($berubah) : '—' }}
                                            </td>

                                            <td class="whitespace-pre-line px-6 py-4 text-xs">
                                                {{ $log->alasan ?? '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center italic text-slate-500">
                                                Belum ada riwayat perubahan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-slate-100 px-6 py-4">
                            {{ $audit->links('kegiatan._pagination') }}
                        </div>
                    </section>
                @endif
            </div>

            {{-- KOLOM SAMPING --}}
            <aside class="min-w-0 space-y-6">
                @if ($bacaIsi && $kegiatan->memerlukanPengumpulan())
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        @include('pengumpulan._tombol_kegiatan')
                    </div>
                @endif

                {{-- INFORMASI --}}
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                        <h2 class="text-sm font-bold text-slate-800">Informasi</h2>
                    </div>

                    <dl class="divide-y divide-slate-100">
                        <div class="px-6 py-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Jenis</dt>
                            <dd class="mt-1 font-semibold text-slate-800">
                                {{ \App\Models\Kegiatan::JENIS[$kegiatan->jenis] ?? $kegiatan->jenis }}
                            </dd>
                        </div>

                        <div class="px-6 py-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Pembuat</dt>
                            <dd class="mt-1 font-semibold text-slate-800">
                                {{ $kegiatan->pembuat?->nama ?? '—' }}
                            </dd>
                        </div>

                        <div class="px-6 py-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Pertemuan</dt>
                            <dd class="mt-1 font-semibold leading-6 text-slate-800">
                                {{ $kegiatan->pertemuan ? 'Ke-' . $kegiatan->pertemuan->nomor . ' — ' . $kegiatan->pertemuan->topik : 'Umum kelas' }}
                            </dd>
                        </div>

                        @if ($kegiatan->memerlukanPengumpulan())
                            <div class="px-6 py-4">
                                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Mulai dikerjakan</dt>
                                <dd class="mt-1 font-semibold text-slate-800">
                                    {{ $kegiatan->buka_at ? $kegiatan->buka_at->setTimezone($zona)->format('d-m-Y H:i') : '—' }}
                                </dd>
                            </div>

                            <div class="px-6 py-4">
                                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Batas pengumpulan
                                </dt>
                                <dd class="mt-1 font-semibold text-slate-800">
                                    {{ $kegiatan->tenggat_at ? $kegiatan->tenggat_at->setTimezone($zona)->format('d-m-Y H:i') : '—' }}
                                </dd>
                                <dd class="mt-1 text-[11px] text-slate-400">Zona waktu: {{ $zona }}</dd>
                            </div>

                            <div class="px-6 py-4">
                                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Berkas jawaban</dt>
                                <dd class="mt-1 font-semibold leading-6 text-slate-800">
                                    Maks. {{ $kegiatan->maks_berkas }} berkas,
                                    {{ $kegiatan->maks_ukuran_byte ? (int) ($kegiatan->maks_ukuran_byte / 1048576) : 0 }}
                                    MB
                                    per berkas
                                </dd>
                            </div>

                            <div class="px-6 py-4">
                                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Format jawaban</dt>
                                <dd class="mt-1 font-semibold leading-6 text-slate-800">
                                    {{ is_array($kegiatan->ekstensi_diizinkan) ? strtoupper(implode(', ', $kegiatan->ekstensi_diizinkan)) : '—' }}
                                </dd>
                            </div>
                        @else
                            <div class="px-6 py-4">
                                <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Ketersediaan</dt>
                                <dd class="mt-1 font-semibold text-slate-800">Materi tersedia</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                {{-- PENGELOLAAN --}}
                @if ($audit !== null)
                    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                            <h2 class="text-sm font-bold text-slate-800">Pengelolaan</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Status:
                                <strong class="text-slate-800">
                                    {{ \App\Models\Kegiatan::STATUS[$kegiatan->status] ?? $kegiatan->status }}
                                </strong>
                            </p>
                        </div>

                        <div class="space-y-3 p-5">
                            @can('update', $kegiatan)
                                <a href="{{ route('kegiatan.edit', $kegiatan) }}"
                                    class="flex w-full items-center justify-center rounded-lg bg-siakad-active px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">
                                    Edit pembelajaran
                                </a>
                            @endcan

                            @foreach ([
            'tutup' => ['kegiatan.tutup', 'Tutup pengumpulan', 'border-amber-300 text-amber-700 hover:bg-amber-50'],
            'bukaKembali' => ['kegiatan.bukaKembali', 'Buka kembali', 'border-slate-300 text-slate-700 hover:bg-slate-50'],
            'arsipkan' => ['kegiatan.arsipkan', 'Arsipkan', 'border-rose-300 text-rose-700 hover:bg-rose-50'],
            'pulihkan' => ['kegiatan.pulihkan', 'Pulihkan', 'border-slate-300 text-slate-700 hover:bg-slate-50'],
        ] as $kemampuan => [$rute, $teks, $gaya])
                                @can($kemampuan, $kegiatan)
                                    <form method="post" action="{{ route($rute, $kegiatan) }}">
                                        @csrf
                                        <input type="hidden" name="versi" value="{{ $kegiatan->versiForm() }}">
                                        <input type="hidden" name="konfirmasi" value="1">
                                        <button type="submit"
                                            class="w-full rounded-lg border bg-white px-4 py-2 text-sm font-semibold transition {{ $gaya }}">
                                            {{ $teks }}
                                        </button>
                                    </form>
                                @endcan
                            @endforeach

                            @can('perpanjang', $kegiatan)
                                <form method="post" action="{{ route('kegiatan.perpanjang', $kegiatan) }}"
                                    class="mt-2 space-y-3 border-t border-slate-100 pt-5">
                                    @csrf
                                    <input type="hidden" name="versi" value="{{ $kegiatan->versiForm() }}">
                                    <input type="hidden" name="konfirmasi" value="1">

                                    <div>
                                        <label for="tenggat_baru" class="mb-1 block text-xs font-semibold text-slate-600">
                                            Perpanjang tenggat ({{ $zona }})
                                        </label>
                                        <input id="tenggat_baru" name="tenggat_baru" type="datetime-local" step="60"
                                            required
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-siakad-active focus:outline-none focus:ring-1 focus:ring-siakad-active">
                                    </div>

                                    <button type="submit"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Perpanjang
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
@endsection
