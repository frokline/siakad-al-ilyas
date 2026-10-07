@extends('layouts.admin')

@section('title', 'Edit Jawaban')

@section('content')
    @php
        $kegiatan = $pengumpulan->kegiatan;
        $lampiran = $pengumpulan->lampiran;
        $format = strtoupper(implode(', ', $kegiatan->ekstensi_diizinkan));
        $dilepas = array_map('strval', (array) old('lampiran_dihapus', []));

        $input =
            'w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white';
        $label = 'mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700';
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengumpulan jawaban</p>
            <h1 class="text-xl font-bold text-slate-800">Edit jawaban</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $kegiatan->judul }}</p>
        </div>

        <a href="{{ route('pengumpulan.show', $pengumpulan) }}"
            class="self-start rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">&larr;
            Kembali</a>
    </div>

    @include('pengumpulan._jadwal', ['kegiatan' => $kegiatan])

    <form method="post" action="{{ route('pengumpulan.update', $pengumpulan) }}" enctype="multipart/form-data"
        class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PATCH')

        <input type="hidden" name="versi_form" value="{{ old('versi_form', $pengumpulan->versiForm()) }}">

        <div>
            <label for="jawaban_teks" class="{{ $label }}">Pesan untuk dosen</label>
            <textarea id="jawaban_teks" name="jawaban_teks" rows="5"
                maxlength="{{ config('pengumpulan.maks_karakter_jawaban', 10000) }}" placeholder="Pesan bersifat opsional."
                class="{{ $input }}">{{ old('jawaban_teks', $pengumpulan->jawaban_teks) }}</textarea>
            @error('jawaban_teks')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-slate-500">Pesan boleh dikosongkan selama masih ada berkas jawaban.</p>
        </div>

        <div>
            <label for="berkas_baru" class="{{ $label }}">Tambahkan berkas</label>
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

        @if ($lampiran->isNotEmpty())
            <div>
                <h2 class="text-sm font-bold text-slate-800">Berkas saat ini</h2>
                <p class="mb-2 text-xs text-slate-500">Centang berkas yang ingin dilepas dari jawaban.</p>

                <ul class="divide-y divide-slate-100 rounded-lg border border-slate-200">
                    @foreach ($lampiran as $item)
                        <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                            <span class="min-w-0">
                                <strong class="block truncate text-slate-800">{{ $item->nama_asli }}</strong>
                                <span
                                    class="text-xs text-slate-500">{{ number_format($item->ukuran_byte / 1048576, 2, ',', '.') }}
                                    MB</span>
                            </span>

                            <label class="flex shrink-0 items-center gap-2 text-xs font-semibold text-rose-600">
                                <input type="checkbox" name="lampiran_dihapus[]" value="{{ $item->id }}"
                                    class="rounded border-slate-300 text-rose-600" @checked(in_array((string) $item->id, $dilepas, true))>
                                Lepas
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex items-center gap-3">
            <button type="submit"
                class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">Simpan
                perubahan</button>
            <a href="{{ route('pengumpulan.show', $pengumpulan) }}"
                class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Batal</a>
        </div>

        <p class="rounded-lg border border-sky-200 bg-sky-50 p-3 text-sm text-sky-800">
            Perubahan langsung menjadi jawaban terbaru. Tidak ada proses simpan draf atau kirim final kedua.
        </p>
    </form>
@endsection
