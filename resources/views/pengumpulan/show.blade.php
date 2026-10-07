@extends('layouts.admin')

@section('title', 'Detail Jawaban')

@section('content')
    @include('pengumpulan._pesan')

    @php
        $kegiatan = $pengumpulan->kegiatan;
        $status = \App\Models\Pengumpulan::STATUS[$pengumpulan->status] ?? $pengumpulan->status;
        $batal = $pengumpulan->status === \App\Models\Pengumpulan::DIBATALKAN;
        $sekunder =
            'rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50';
        $kartu = 'rounded-xl border border-slate-200 bg-white p-6 shadow-sm';

        // Pemilik kembali ke ruang jawabannya; dosen kembali ke rekap; lainnya ke daftar.
        if ($kegiatan && $pemilik) {
            $tautanKembali = route('pengumpulan.saya', $kegiatan);
        } elseif (
            $kegiatan &&
            \Illuminate\Support\Facades\Gate::allows('rekap', [\App\Models\Pengumpulan::class, $kegiatan])
        ) {
            $tautanKembali = route('pengumpulan.rekap', $kegiatan);
        } else {
            $tautanKembali = route('pengumpulan.index');
        }
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Jawaban mahasiswa</p>
            <h1 class="text-xl font-bold text-slate-800">{{ $kegiatan?->judul ?? 'Detail jawaban' }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $kegiatan?->kelasKuliah?->kode ?? 'Kelas' }} &mdash;
                {{ $kegiatan?->kelasKuliah?->nama_mk_snapshot ?? 'Mata kuliah' }}
            </p>
        </div>

        <a href="{{ $tautanKembali }}" class="{{ $sekunder }} self-start">&larr; Kembali</a>
    </div>

    @if ($kegiatan)
        @include('pengumpulan._jadwal', ['kegiatan' => $kegiatan])
    @endif

    <div class="space-y-6">
        <section class="{{ $kartu }}">
            <h2 class="mb-4 text-sm font-bold text-slate-800">Status jawaban</h2>

            <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-xs text-slate-400">Mahasiswa</dt>
                    <dd class="font-medium text-slate-800">{{ $pengumpulan->pemilik?->nama ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Status</dt>
                    <dd>
                        <span
                            class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $batal ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">{{ $status }}</span>
                        @if ($berlaku)
                            <span class="ml-1 text-[11px] text-slate-400">Jawaban aktif</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Dikirim</dt>
                    <dd class="font-medium text-slate-800">
                        {{ $pengumpulan->dikirim_at ? $pengumpulan->dikirim_at->setTimezone($zona)->format('d-m-Y H:i') : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Terakhir diubah</dt>
                    <dd class="font-medium text-slate-800">
                        {{ $pengumpulan->diubah_at ? $pengumpulan->diubah_at->setTimezone($zona)->format('d-m-Y H:i') : 'Belum pernah diubah' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="{{ $kartu }}">
            <h2 class="mb-3 text-sm font-bold text-slate-800">Pesan untuk dosen</h2>

            @if (filled($pengumpulan->jawaban_teks))
                <div class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ $pengumpulan->jawaban_teks }}
                </div>
            @else
                <p class="text-sm italic text-slate-500">Tidak ada pesan tambahan.</p>
            @endif
        </section>

        <section class="{{ $kartu }}">
            <h2 class="mb-3 text-sm font-bold text-slate-800">Berkas jawaban</h2>

            @forelse ($pengumpulan->lampiran as $lampiran)
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-3 last:border-0">
                    <div class="min-w-0 text-sm">
                        <strong class="block truncate text-slate-800">{{ $lampiran->nama_asli }}</strong>
                        <span
                            class="text-xs text-slate-500">{{ number_format($lampiran->ukuran_byte / 1048576, 2, ',', '.') }}
                            MB</span>
                    </div>

                    <form method="post" action="{{ route('pengumpulan.tautan', [$pengumpulan, $lampiran]) }}">
                        @csrf
                        <button type="submit"
                            class="shrink-0 rounded-lg bg-siakad-dark px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-800">Unduh</button>
                    </form>
                </div>
            @empty
                <p class="text-sm italic text-slate-500">Tidak ada berkas jawaban.</p>
            @endforelse
        </section>

        @if ($bolehTulis)
            <section class="{{ $kartu }}">
                <h2 class="mb-2 text-sm font-bold text-slate-800">Kelola jawaban</h2>
                <p class="mb-4 text-sm text-slate-500">Jawaban masih dapat diubah atau dihapus karena tenggat belum
                    berakhir.</p>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('pengumpulan.edit', $pengumpulan) }}"
                        class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">Edit
                        jawaban</a>

                    <form method="post" action="{{ route('pengumpulan.destroy', $pengumpulan) }}"
                        onsubmit="return confirm('Hapus jawaban ini?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="versi_form" value="{{ $pengumpulan->versiForm() }}">
                        <button type="submit"
                            class="rounded-lg border border-rose-200 bg-white px-5 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50">Hapus
                            jawaban</button>
                    </form>
                </div>
            </section>
        @elseif ($pemilik && $pengumpulan->status === \App\Models\Pengumpulan::TERKIRIM)
            <section class="{{ $kartu }}">
                <p class="text-sm italic text-slate-500">
                    Tenggat telah berakhir. Jawaban hanya dapat dilihat dan tidak dapat diubah lagi.
                </p>
            </section>
        @endif

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/60 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Riwayat jawaban</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-semibold">Versi / Waktu</th>
                            <th scope="col" class="px-6 py-3 font-semibold">Pelaku</th>
                            <th scope="col" class="px-6 py-3 font-semibold">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($audit as $log)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-6 py-3">
                                    <strong class="block text-slate-800">V{{ $log->versi_entitas }}</strong>
                                    <span
                                        class="font-mono text-xs text-slate-500">{{ $log->waktu->setTimezone($zona)->format('d-m-Y H:i') }}</span>
                                </td>
                                <td class="px-6 py-3 font-medium text-slate-800">{{ $log->pelaku?->nama ?? 'Sistem' }}</td>
                                <td class="px-6 py-3">
                                    <span
                                        class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">{{ $log->aksi }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center italic text-slate-500">Belum ada riwayat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $audit->links('pengumpulan._pagination') }}
        </section>
    </div>
@endsection
