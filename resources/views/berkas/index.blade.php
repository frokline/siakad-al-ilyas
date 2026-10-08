@extends('layouts.admin')

@section('title', 'Berkas Saya')

@section('content')
    @include('berkas._pesan')

    @php
        $persen = $kuota > 0 ? min(100, ($terpakai / $kuota) * 100) : 0;
        $warnaBar = $persen >= 90 ? 'bg-rose-500' : ($persen >= 70 ? 'bg-amber-500' : 'bg-siakad-active');
        $input =
            'w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition-all focus:border-siakad-dark focus:ring-1 focus:ring-siakad-dark focus:bg-white';
        $label = 'mb-2 block text-xs font-bold uppercase tracking-wider text-slate-700';
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Berkas saya</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola dokumen yang Anda unggah.</p>
        </div>

        <a href="{{ route('berkas.create') }}"
            class="self-start rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">+
            Unggah berkas</a>
    </div>

    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-end justify-between gap-3">
            <h2 class="text-sm font-bold text-slate-800">Alokasi penyimpanan</h2>
            <p class="text-sm font-semibold text-slate-700">
                {{ number_format($terpakai / 1048576, 2, ',', '.') }} MB /
                {{ number_format($kuota / 1048576, 0, ',', '.') }} MB
            </p>
        </div>

        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar"
            aria-valuenow="{{ round($persen) }}" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full rounded-full {{ $warnaBar }}" style="width: {{ round($persen, 1) }}%"></div>
        </div>

        <p class="mt-3 text-xs text-slate-500">Menonaktifkan berkas tidak membebaskan alokasi. Hubungi pengelola jika
            alokasi sudah penuh.</p>
    </section>

    <form method="get" action="{{ route('berkas.index') }}"
        class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="q" class="{{ $label }}">Cari label</label>
                <input id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                    class="{{ $input }}">
            </div>

            <div>
                <label for="status" class="{{ $label }}">Status</label>
                <select id="status" name="status" class="{{ $input }}">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Berkas::STATUS as $kode => $nama)
                        <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="rounded-lg bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">Cari</button>
                <a href="{{ route('berkas.index') }}"
                    class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </div>
    </form>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-slate-600">
                <caption class="sr-only">Berkas yang diunggah oleh akun Anda</caption>
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Berkas</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Ukuran</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($daftar as $row)
                        @php
                            $warnaStatus = match ($row->status) {
                                \App\Models\Berkas::TERSEDIA => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                \App\Models\Berkas::MENUNGGU => 'border-amber-200 bg-amber-50 text-amber-700',
                                \App\Models\Berkas::DITOLAK => 'border-rose-200 bg-rose-50 text-rose-700',
                                default => 'border-slate-200 bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <tr class="align-top hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <strong class="block text-slate-800">{{ $row->label }}</strong>
                                <span class="block max-w-xs truncate text-xs text-slate-500">{{ $row->nama_asli }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs">{{ $row->ukuranLabel() }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $warnaStatus }}">{{ $row->labelStatus() }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('berkas.show', $row) }}"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center italic text-slate-500">Belum ada berkas yang
                                sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('berkas._pagination', ['paginator' => $daftar])
    </section>
@endsection
