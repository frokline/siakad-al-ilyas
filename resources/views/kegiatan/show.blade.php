@extends('layouts.admin')

@section('title', $kegiatan->judul)

@section('content')
    @include('kegiatan._pesan')

    @php
        $item = $kegiatan;
        $tombolKecil =
            'rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50';
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

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            @include('kegiatan._lencana')
            <h1 class="mt-2 text-xl font-bold text-slate-800">{{ $kegiatan->judul }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $kegiatan->kelasKuliah->kode }} &middot; {{ $kegiatan->kelasKuliah->nama_mk_snapshot }}
            </p>
        </div>

        <a href="{{ route('kegiatan.index', ['kelas' => $kegiatan->kelas_kuliah_id]) }}"
            class="{{ $tombolKecil }} self-start">&larr; Kembali</a>
    </div>

    @if ($kegiatan->pertemuan?->status === 'batal')
        <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
            Pertemuan dibatalkan. Pembelajaran ini disembunyikan dari mahasiswa.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- KOLOM UTAMA --}}
        <div class="space-y-6 lg:col-span-2">
            @if ($bacaIsi)
                <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-3 text-sm font-bold text-slate-800">
                        {{ $kegiatan->berupaMateri() ? 'Isi materi' : 'Instruksi pengerjaan' }}
                    </h2>

                    @if ($kegiatan->instruksi)
                        <div class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ $kegiatan->instruksi }}
                        </div>
                    @else
                        <p class="text-sm italic text-slate-500">Tidak ada pesan atau instruksi tambahan.</p>
                    @endif

                    @if ($kegiatan->tautan_eksternal)
                        <p class="mt-4">
                            <a href="{{ $kegiatan->tautan_eksternal }}" target="_blank" rel="noopener noreferrer"
                                class="{{ $tombolKecil }} inline-block">Buka tautan tambahan &#8599;</a>
                        </p>
                    @endif
                </article>

                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-3 text-sm font-bold text-slate-800">Lampiran</h2>

                    @forelse ($kegiatan->lampiran as $lampiran)
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-3 last:border-0">
                            <div class="min-w-0 text-sm">
                                <strong class="block truncate text-slate-800">{{ $lampiran->berkas->label }}</strong>
                                <span class="block truncate text-xs text-slate-500">
                                    {{ $lampiran->berkas->nama_asli }} &middot; {{ $lampiran->berkas->ukuranLabel() }}
                                </span>
                            </div>

                            @if ($lampiran->berkas->status === \App\Models\Berkas::TERSEDIA)
                                <form method="post"
                                    action="{{ route('kegiatan.tautan', ['kegiatan' => $kegiatan->id, 'lampiran' => $lampiran->id]) }}">
                                    @csrf
                                    <button type="submit"
                                        class="shrink-0 rounded-lg bg-siakad-active px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">Unduh</button>
                                </form>
                            @else
                                <span class="shrink-0 text-xs italic text-slate-400">Tidak tersedia</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm italic text-slate-500">Tidak ada lampiran tambahan.</p>
                    @endforelse
                </section>
            @else
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Isi pembelajaran belum dapat dibuka.
                </div>
            @endif

            {{-- RIWAYAT PERUBAHAN (hanya pengelola) --}}
            @if ($audit !== null)
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50/60 px-6 py-4">
                        <h2 class="text-sm font-bold text-slate-800">Riwayat perubahan</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left text-sm text-slate-600">
                            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
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
                                    <tr class="align-top hover:bg-slate-50/80">
                                        <td class="px-6 py-3">
                                            <strong class="block text-slate-800">V{{ $log->versi_entitas }}</strong>
                                            <span
                                                class="block font-mono text-xs text-slate-500">{{ $log->waktu->setTimezone($zona)->format('d-m-Y H:i') }}</span>
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="font-medium text-slate-800">{{ $log->pelaku?->nama ?? 'Sistem' }}
                                            </div>
                                            <span
                                                class="mt-1 inline-flex rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">{{ $log->aksi }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-xs">{{ $berubah !== '' ? ucfirst($berubah) : '—' }}</td>
                                        <td class="whitespace-pre-line px-6 py-3 text-xs">{{ $log->alasan ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center italic text-slate-500">Belum ada
                                            riwayat perubahan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 pb-4">
                        {{ $audit->links('kegiatan._pagination') }}
                    </div>
                </section>
            @endif
        </div>

        {{-- KOLOM SAMPING --}}
        <aside class="space-y-6">
            @if ($bacaIsi && $kegiatan->memerlukanPengumpulan())
                @include('pengumpulan._tombol_kegiatan')
            @endif

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-3 text-sm font-bold text-slate-800">Informasi</h2>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-slate-400">Jenis</dt>
                        <dd class="font-medium text-slate-800">
                            {{ \App\Models\Kegiatan::JENIS[$kegiatan->jenis] ?? $kegiatan->jenis }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Pembuat</dt>
                        <dd class="font-medium text-slate-800">{{ $kegiatan->pembuat?->nama ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Pertemuan</dt>
                        <dd class="font-medium text-slate-800">
                            {{ $kegiatan->pertemuan ? 'Ke-' . $kegiatan->pertemuan->nomor . ' — ' . $kegiatan->pertemuan->topik : 'Umum kelas' }}
                        </dd>
                    </div>

                    @if ($kegiatan->memerlukanPengumpulan())
                        <div>
                            <dt class="text-xs text-slate-400">Mulai dikerjakan</dt>
                            <dd class="font-medium text-slate-800">
                                {{ $kegiatan->buka_at ? $kegiatan->buka_at->setTimezone($zona)->format('d-m-Y H:i') : '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Batas pengumpulan</dt>
                            <dd class="font-semibold text-slate-800">
                                {{ $kegiatan->tenggat_at ? $kegiatan->tenggat_at->setTimezone($zona)->format('d-m-Y H:i') : '—' }}
                            </dd>
                            <dd class="text-[11px] text-slate-400">Zona waktu: {{ $zona }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Berkas jawaban</dt>
                            <dd class="font-medium text-slate-800">
                                Maks. {{ $kegiatan->maks_berkas }} berkas,
                                {{ $kegiatan->maks_ukuran_byte ? (int) ($kegiatan->maks_ukuran_byte / 1048576) : 0 }} MB
                                per berkas
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Format jawaban</dt>
                            <dd class="font-medium text-slate-800">
                                {{ is_array($kegiatan->ekstensi_diizinkan) ? strtoupper(implode(', ', $kegiatan->ekstensi_diizinkan)) : '—' }}
                            </dd>
                        </div>
                    @else
                        <div>
                            <dt class="text-xs text-slate-400">Ketersediaan</dt>
                            <dd class="font-medium text-slate-800">Materi tersedia</dd>
                        </div>
                    @endif
                </dl>
            </section>

            @if ($audit !== null)
                <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Pengelolaan</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Status: <strong
                                class="text-slate-800">{{ \App\Models\Kegiatan::STATUS[$kegiatan->status] ?? $kegiatan->status }}</strong>
                        </p>
                    </div>

                    @can('update', $kegiatan)
                        <a href="{{ route('kegiatan.edit', $kegiatan) }}"
                            class="block rounded-lg bg-siakad-active px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">Edit
                            pembelajaran</a>
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
                                    class="w-full rounded-lg border bg-white px-4 py-2 text-sm font-semibold transition-colors {{ $gaya }}">{{ $teks }}</button>
                            </form>
                        @endcan
                    @endforeach

                    @can('perpanjang', $kegiatan)
                        <form method="post" action="{{ route('kegiatan.perpanjang', $kegiatan) }}"
                            class="space-y-2 border-t border-slate-100 pt-4">
                            @csrf
                            <input type="hidden" name="versi" value="{{ $kegiatan->versiForm() }}">
                            <input type="hidden" name="konfirmasi" value="1">

                            <label for="tenggat_baru" class="block text-xs font-semibold text-slate-600">Perpanjang tenggat
                                ({{ $zona }})</label>
                            <input id="tenggat_baru" name="tenggat_baru" type="datetime-local" step="60" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-siakad-active focus:outline-none focus:ring-1 focus:ring-siakad-active">
                            <button type="submit"
                                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Perpanjang</button>
                        </form>
                    @endcan
                </section>
            @endif
        </aside>
    </div>
@endsection
