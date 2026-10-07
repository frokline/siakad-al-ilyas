@php
    $sekarang = now('UTC');
    $belumDibuka = $sekarang->lt($kegiatan->buka_at);
    $tenggatLewat = $sekarang->gte($kegiatan->tenggat_at);
    $ditutup = $kegiatan->status !== \App\Models\Kegiatan::TERBIT;
@endphp

<section class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-800">{{ $kegiatan->judul }}</h2>
            <p class="text-sm text-slate-500">
                {{ $kegiatan->kelasKuliah->kode }} &mdash; {{ $kegiatan->kelasKuliah->nama_mk_snapshot }}
            </p>
        </div>

        @can('view', $kegiatan)
            <a href="{{ route('kegiatan.show', $kegiatan) }}"
                class="text-sm font-semibold text-siakad-active hover:underline">Baca instruksi tugas &rarr;</a>
        @endcan
    </div>

    <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
        <div>
            <dt class="text-xs text-slate-400">Mulai dikerjakan</dt>
            <dd class="font-medium text-slate-800">{{ $kegiatan->buka_at->setTimezone($zona)->format('d-m-Y H:i') }}
                ({{ $zona }})</dd>
        </div>
        <div>
            <dt class="text-xs text-slate-400">Batas pengumpulan</dt>
            <dd class="font-semibold text-slate-800">
                {{ $kegiatan->tenggat_at->setTimezone($zona)->format('d-m-Y H:i') }} ({{ $zona }})</dd>
        </div>
        <div>
            <dt class="text-xs text-slate-400">Format berkas</dt>
            <dd class="font-medium text-slate-800">{{ strtoupper(implode(', ', $kegiatan->ekstensi_diizinkan)) }}</dd>
        </div>
        <div>
            <dt class="text-xs text-slate-400">Batas lampiran</dt>
            <dd class="font-medium text-slate-800">Maksimal {{ $kegiatan->maks_berkas }} berkas,
                {{ (int) ($kegiatan->maks_ukuran_byte / 1048576) }} MB per berkas</dd>
        </div>
    </dl>

    @if ($belumDibuka)
        <p class="mt-4 rounded-lg border border-sky-200 bg-sky-50 p-3 text-sm text-sky-800">
            Tugas belum dibuka. Anda dapat mulai mengerjakan pada waktu yang tercantum di atas.
        </p>
    @elseif ($ditutup)
        <p class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
            Tugas sudah ditutup oleh pengajar.
        </p>
    @elseif ($tenggatLewat)
        <p class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
            Batas pengumpulan telah berakhir. Jawaban tidak dapat diubah atau dikumpulkan lagi.
        </p>
    @else
        <p class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
            Tugas masih dapat dikerjakan dan dikumpulkan sebelum batas waktu berakhir.
        </p>
    @endif
</section>
