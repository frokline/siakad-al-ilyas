@extends('layouts.admin')
@section('title', 'Koreksi Presensi')

@section('content')
    @php
        $teks = static function (string $key, string $fallback = ''): string {
            if (!session()->hasOldInput($key)) {
                return $fallback;
            }
            return is_string(old($key)) ? old($key) : '';
        };
        $posisi = array_filter(
            ['page' => $halaman ?? null, 'status' => $filterStatus ?? null],
            static fn($nilai): bool => $nilai !== null && $nilai !== '',
        );
    @endphp

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Koreksi Presensi</h1>
            <p class="text-sm text-slate-500 mt-1">Lakukan penyesuaian jika terjadi kesalahan pencatatan.</p>
        </div>
        <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start"
            href="{{ route('presensi.show', ['pertemuan' => $sesi] + $posisi) }}">
            &larr; Batal / Kembali ke Pertemuan
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start mb-8">
        <div class="lg:col-span-2 rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-5">
                <h2 class="text-base font-bold text-slate-800">
                    {{ $baris->peserta_snapshot['nama'] ?? 'Nama tidak tersedia' }}
                </h2>
                <div class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                    <span class="font-mono">{{ $baris->peserta_snapshot['nim'] ?? 'NIM tidak tersedia' }}</span>
                    <span>&bull;</span>
                    <span>Pertemuan {{ $sesi->nomor }}</span>
                    <span>&bull;</span>
                    <span>Revisi {{ $baris->revisi }}</span>
                </div>
                <div class="mt-3 text-sm">
                    Status tersimpan saat ini:
                    <span
                        class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700 uppercase tracking-wider border border-slate-200">{{ $baris->labelStatus() }}</span>
                </div>
            </div>

            <div class="p-6">
                <form action="{{ route('presensi.koreksi', ['pertemuan' => $sesi, 'presensi' => $baris]) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="versi_presensi" value="{{ $teks('versi_presensi', $baris->versiForm()) }}">
                    <input type="hidden" name="halaman" value="{{ $halaman ?? '' }}">
                    <input type="hidden" name="filter_status" value="{{ $filterStatus ?? '' }}">

                    <div class="mb-5">
                        <label for="status"
                            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Status yang Benar
                            <span class="text-rose-500">*</span></label>
                        <select id="status" name="status" required
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('status') border-rose-500 bg-rose-50/50 @enderror">
                            @foreach (\App\Models\Presensi::PILIHAN as $nilai => $label)
                                <option value="{{ $nilai }}" @selected($teks('status', $baris->status) === $nilai)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="catatan"
                            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Catatan Presensi
                            (Opsional)</label>
                        <textarea id="catatan" name="catatan" maxlength="1000" rows="3"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('catatan') border-rose-500 bg-rose-50/50 @enderror">{{ $teks('catatan', $baris->catatan ?? '') }}</textarea>
                        @error('catatan')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="alasan"
                            class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wider">Alasan Koreksi
                            <span class="text-rose-500">*</span></label>
                        <textarea id="alasan" name="alasan" required minlength="10" maxlength="2000" rows="3"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white @error('alasan') border-rose-500 bg-rose-50/50 @enderror">{{ $teks('alasan') }}</textarea>
                        <p class="mt-1 text-xs text-slate-500">Wajib diisi (min 10 karakter). Alasan akan masuk ke riwayat
                            audit. Identitas peserta dan keanggotaan kelas tetap.</p>
                        @error('alasan')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-5 border-t border-slate-200">
                        <button type="submit"
                            class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors focus:outline-none focus:ring-2 focus:ring-siakad-dark focus:ring-offset-2">
                            Simpan Koreksi
                        </button>
                        <a href="{{ route('presensi.edit', ['pertemuan' => $sesi, 'presensi' => $baris, 'halaman' => $halaman ?? null, 'status' => $filterStatus ?? null]) }}"
                            class="text-sm font-semibold text-slate-600 hover:text-siakad-dark hover:underline">
                            Muat ulang versi terbaru
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50/50 px-5 py-4">
                    <h2 class="text-sm font-bold text-slate-800">Kondisi Tersimpan</h2>
                </div>
                <dl class="p-5 space-y-4 text-sm">
                    <div>
                        <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dicatat oleh</dt>
                        <dd class="mt-1 font-medium text-slate-700">{{ $baris->pencatat?->nama ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Waktu pencatatan</dt>
                        <dd class="mt-1 font-mono text-xs text-slate-700">
                            {{ $baris->dicatat_at?->setTimezone($zona ?? config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Catatan saat ini</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-600">{{ $baris->catatan ?? 'Tidak ada catatan' }}
                        </dd>
                    </div>
                    <a href="{{ route('presensi.audit', ['pertemuan' => $sesi, 'presensi' => $baris]) }}"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 hover:text-siakad-dark">
                        Lihat riwayat audit
                    </a>
                </dl>
            </div>

            <div class="rounded-xl bg-amber-50 border border-amber-200 p-5 text-sm text-amber-800 shadow-sm">
                <h2 class="text-sm font-bold mb-2">Sebelum menyimpan</h2>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    <li>Setiap koreksi menaikkan revisi dan tercatat di audit.</li>
                    <li>Alasan koreksi tidak dapat diubah setelah disimpan.</li>
                    <li>Jika data berubah saat Anda mengedit, muat ulang versi terbaru.</li>
                </ul>
            </div>
        </aside>
    </div>
@endsection
