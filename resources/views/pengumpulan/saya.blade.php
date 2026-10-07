@extends('layouts.admin')

@section('title', 'Pengumpulan Jawaban')

@section('content')
    @include('pengumpulan._pesan')

    @php
        $format = strtoupper(implode(', ', $kegiatan->ekstensi_diizinkan));
        $aktif = $pengumpulan && $pengumpulan->status === \App\Models\Pengumpulan::TERKIRIM;
        $dibatalkan = $pengumpulan && $pengumpulan->status === \App\Models\Pengumpulan::DIBATALKAN;

        $input =
            'w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white';
        $label = 'mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700';
        $utama =
            'rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800';
        $sekunder =
            'rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50';
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengumpulan jawaban</p>
            <h1 class="text-xl font-bold text-slate-800">{{ $kegiatan->judul }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $kegiatan->kelasKuliah?->kode ?? 'Kelas' }} &mdash;
                {{ $kegiatan->kelasKuliah?->nama_mk_snapshot ?? 'Mata kuliah' }}
            </p>
        </div>

        <a href="{{ route('kegiatan.show', $kegiatan) }}" class="{{ $sekunder }} self-start">Lihat instruksi</a>
    </div>

    @include('pengumpulan._jadwal', ['kegiatan' => $kegiatan])

    @if ($aktif)
        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-800">Jawaban sudah dikumpulkan</h2>

            <p class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                Jawaban dikumpulkan pada {{ $pengumpulan->dikirim_at->setTimezone($zona)->format('d-m-Y H:i') }}.
                @if ($pengumpulan->diubah_at)
                    Terakhir diubah pada {{ $pengumpulan->diubah_at->setTimezone($zona)->format('d-m-Y H:i') }}.
                @endif
            </p>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('pengumpulan.show', $pengumpulan) }}" class="{{ $utama }}">Lihat jawaban</a>

                @if ($bolehTulis)
                    <a href="{{ route('pengumpulan.edit', $pengumpulan) }}" class="{{ $sekunder }}">Edit jawaban</a>

                    <form method="post" action="{{ route('pengumpulan.destroy', $pengumpulan) }}"
                        onsubmit="return confirm('Hapus jawaban ini? Tindakan akan dicatat oleh sistem.')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="versi_form" value="{{ $pengumpulan->versiForm() }}">
                        <button type="submit"
                            class="rounded-lg border border-rose-200 bg-white px-5 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50">Hapus
                            jawaban</button>
                    </form>
                @endif
            </div>

            @unless ($bolehTulis)
                <p class="text-sm italic text-slate-500">
                    Jawaban tidak dapat diubah karena waktu pengumpulan telah berakhir atau status akademik tidak aktif.
                </p>
            @endunless
        </section>
    @elseif ($dibatalkan)
        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-800">Jawaban telah dihapus</h2>

            <p class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                Jawaban ini dibatalkan pada
                {{ $pengumpulan->dibatalkan_at ? $pengumpulan->dibatalkan_at->setTimezone($zona)->format('d-m-Y H:i') : 'waktu yang tidak tersedia' }}.
            </p>

            <a href="{{ route('pengumpulan.show', $pengumpulan) }}"
                class="text-sm font-semibold text-siakad-active hover:underline">Lihat riwayat jawaban &rarr;</a>
        </section>
    @elseif ($bolehTulis)
        <form method="post" action="{{ route('pengumpulan.store', $kegiatan) }}" enctype="multipart/form-data"
            class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <h2 class="text-base font-bold text-slate-800">Kirim jawaban</h2>

            <div>
                <label for="jawaban_teks" class="{{ $label }}">Pesan untuk dosen</label>
                <textarea id="jawaban_teks" name="jawaban_teks" rows="5"
                    maxlength="{{ config('pengumpulan.maks_karakter_jawaban', 10000) }}"
                    placeholder="Opsional. Contoh: Izin mengumpulkan tugas." class="{{ $input }}">{{ old('jawaban_teks') }}</textarea>
                @error('jawaban_teks')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-slate-500">Pesan boleh dikosongkan jika Anda mengunggah berkas.</p>
            </div>

            <div>
                <label for="berkas_baru" class="{{ $label }}">Pilih berkas jawaban</label>
                <input id="berkas_baru" name="berkas_baru[]" type="file" multiple
                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"
                    class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-siakad-active hover:file:bg-emerald-100">
                @error('berkas_baru')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
                @error('berkas_baru.*')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-slate-500">
                    Format: {{ $format }}. Maksimal {{ $kegiatan->maks_berkas }} berkas,
                    masing-masing maksimal {{ (int) ($kegiatan->maks_ukuran_byte / 1048576) }} MB.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="{{ $utama }}">Kirim jawaban</button>
                <a href="{{ route('kegiatan.show', $kegiatan) }}" class="{{ $sekunder }}">Batal</a>
            </div>

            <p class="rounded-lg border border-sky-200 bg-sky-50 p-3 text-sm text-sky-800">
                Setelah dikirim, jawaban masih dapat diedit atau dihapus selama tenggat belum berakhir.
            </p>
        </form>
    @else
        <section class="space-y-2 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-800">Pengumpulan tidak tersedia</h2>
            <p class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                Waktu pengumpulan belum dimulai, telah berakhir, atau status keikutsertaan kelas Anda tidak aktif.
            </p>
        </section>
    @endif
@endsection
