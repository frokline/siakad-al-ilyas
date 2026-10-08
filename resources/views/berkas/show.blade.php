@extends('layouts.admin')

@section('title', $file->label)

@section('content')
    @include('berkas._pesan')

    @php
        $teks = static function (string $key, string $fallback = ''): string {
            if (!session()->hasOldInput($key)) {
                return $fallback;
            }
            return is_string(old($key)) ? old($key) : '';
        };

        $warnaStatus = match ($file->status) {
            \App\Models\Berkas::TERSEDIA => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            \App\Models\Berkas::MENUNGGU => 'border-amber-200 bg-amber-50 text-amber-700',
            \App\Models\Berkas::DITOLAK => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-slate-200 bg-slate-100 text-slate-600',
        };

        $input =
            'w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white';
        $label = 'mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700';
        $kartu = 'rounded-xl border border-slate-200 bg-white p-6 shadow-sm';
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-slate-800">{{ $file->label }}</h1>
            <p class="mt-1 truncate text-sm text-slate-500">{{ $file->nama_asli }}</p>
        </div>

        <a href="{{ route('berkas.index') }}"
            class="self-start rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">&larr;
            Daftar berkas</a>
    </div>

    <div class="space-y-6">
        <section class="{{ $kartu }}">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-slate-800">Informasi berkas</h2>
                <span
                    class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $warnaStatus }}">{{ $file->labelStatus() }}</span>
            </div>

            <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-slate-400">Ukuran</dt>
                    <dd class="font-medium text-slate-800">{{ $file->ukuranLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Jenis</dt>
                    <dd class="font-medium text-slate-800">{{ strtoupper($file->ekstensi) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Diunggah</dt>
                    <dd class="font-medium text-slate-800">
                        {{ $file->created_at->setTimezone($zona)->format('d-m-Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Pemeriksaan</dt>
                    <dd class="font-medium text-slate-800">
                        {{ $file->pemeriksaan === 'clamav' ? 'Format dan pemindaian antivirus' : 'Format dan ukuran' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-slate-400">Keterangan</dt>
                    <dd class="whitespace-pre-line font-medium text-slate-800">{{ $file->keterangan ?? '—' }}</dd>
                </div>
            </dl>

            @if ($file->pesan_status)
                <p class="mt-4 rounded-lg border border-sky-200 bg-sky-50 p-3 text-sm text-sky-800">
                    {{ $file->pesan_status }}</p>
            @endif

            <div class="mt-5 flex flex-wrap items-center gap-3">
                @can('download', $file)
                    <form action="{{ route('berkas.tautan', $file) }}" method="post">
                        @csrf
                        <button type="submit"
                            class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">Unduh</button>
                    </form>
                @endcan

                @can('update', $file)
                    <a href="{{ route('berkas.edit', $file) }}"
                        class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Edit
                        keterangan</a>
                @endcan

                <a href="{{ route('berkas.show', $file) }}"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Muat
                    ulang</a>
            </div>

            @if ($file->status === \App\Models\Berkas::MENUNGGU)
                <p class="mt-4 text-sm text-slate-500">Proses belum selesai. Muat ulang halaman beberapa saat lagi; hubungi
                    pengelola jika status tidak berubah.</p>
            @elseif ($file->status === \App\Models\Berkas::DITOLAK)
                <p class="mt-4 text-sm"><a class="font-semibold text-siakad-active hover:underline"
                        href="{{ route('berkas.create') }}">Unggah kembali melalui formulir baru &rarr;</a></p>
            @endif
        </section>

        @if (in_array($file->status, [\App\Models\Berkas::TERSEDIA, \App\Models\Berkas::DIHAPUS], true))
            @php($nonaktif = $file->status === \App\Models\Berkas::TERSEDIA)

            <section class="{{ $kartu }}">
                <h2 class="text-sm font-bold text-slate-800">{{ $nonaktif ? 'Nonaktifkan berkas' : 'Pulihkan berkas' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $nonaktif ? 'Berkas yang sudah dipakai di modul lain tidak dapat dinonaktifkan. Riwayat dan isi file tetap disimpan.' : 'Aktifkan kembali berkas agar dapat diunduh.' }}
                </p>

                <form action="{{ $nonaktif ? route('berkas.nonaktifkan', $file) : route('berkas.pulihkan', $file) }}"
                    method="post" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="versi" value="{{ $teks('versi', $file->versiForm()) }}">

                    <div>
                        <label for="alasan" class="{{ $label }}">Alasan</label>
                        <textarea id="alasan" name="alasan" rows="3" required minlength="10" maxlength="2000"
                            class="{{ $input }}">{{ $teks('alasan') }}</textarea>
                        @error('alasan')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-slate-500">Minimal 10 karakter.</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="konfirmasi" value="1" required
                            class="rounded border-slate-300 text-siakad-active">
                        Saya mengonfirmasi tindakan ini.
                    </label>
                    @error('konfirmasi')
                        <p class="text-xs text-rose-600">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                        class="rounded-lg border px-5 py-2.5 text-sm font-semibold {{ $nonaktif ? 'border-rose-200 bg-white text-rose-700 hover:bg-rose-50' : 'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50' }}">{{ $nonaktif ? 'Nonaktifkan' : 'Pulihkan' }}</button>
                </form>
            </section>
        @endif

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/60 px-6 py-4">
                <h2 class="text-sm font-bold text-slate-800">Riwayat perubahan</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm text-slate-600">
                    <caption class="sr-only">Audit perubahan berkas</caption>
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-semibold">Revisi / waktu</th>
                            <th scope="col" class="px-6 py-3 font-semibold">Pelaku / tindakan</th>
                            <th scope="col" class="px-6 py-3 font-semibold">Perubahan</th>
                            <th scope="col" class="px-6 py-3 font-semibold">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($audit as $log)
                            <tr class="align-top hover:bg-slate-50/80">
                                <td class="px-6 py-3">
                                    <strong class="block text-slate-800">V{{ $log->versi_entitas }}</strong>
                                    <span
                                        class="font-mono text-xs text-slate-500">{{ $log->waktu->setTimezone($zona)->format('d-m-Y H:i:s') }}</span>
                                </td>
                                <td class="px-6 py-3">
                                    <span
                                        class="block font-medium text-slate-800">{{ $log->pelaku?->nama ?? 'Sistem' }}</span>
                                    <span
                                        class="mt-1 inline-flex rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">{{ str_replace('_', ' ', $log->aksi) }}</span>
                                </td>
                                <td class="px-6 py-3 text-xs">
                                    {{ \App\Models\Berkas::STATUS[$log->sebelum['status'] ?? ''] ?? '—' }}
                                    &rarr;
                                    {{ \App\Models\Berkas::STATUS[$log->sesudah['status'] ?? ''] ?? '—' }}
                                    @if (($log->sebelum['label'] ?? null) !== ($log->sesudah['label'] ?? null))
                                        <span class="mt-1 block text-slate-500">Label: {{ $log->sebelum['label'] ?? '—' }}
                                            &rarr; {{ $log->sesudah['label'] ?? '—' }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-pre-line px-6 py-3 text-xs">{{ $log->alasan ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center italic text-slate-500">Belum ada riwayat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('berkas._pagination', ['paginator' => $audit])
        </section>
    </div>
@endsection
