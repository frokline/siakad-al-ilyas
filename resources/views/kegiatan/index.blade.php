@extends('layouts.admin')

@section('title', 'Tugas & Kegiatan')

@section('content')
    @include('kegiatan._pesan')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Tugas &amp; Kegiatan</h1>
            <p class="mt-1 text-sm text-slate-500">Materi, tugas, latihan, UTS, dan UAS dalam satu tempat.</p>
        </div>

        @if ($pengelola)
            <a href="{{ route('kegiatan.kelas') }}"
                class="inline-flex items-center gap-2 self-start rounded-lg bg-siakad-active px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition-colors">
                + Bagikan pembelajaran
            </a>
        @endif
    </div>

    <form method="get" action="{{ route('kegiatan.index') }}"
        class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ $pengelola ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
            <div class="{{ $pengelola ? 'lg:col-span-1' : 'lg:col-span-2' }}">
                <label for="q" class="mb-1 block text-xs font-semibold text-slate-600">Judul</label>
                <input id="q" name="q" maxlength="100" value="{{ $filter['q'] ?? '' }}"
                    placeholder="Cari judul..."
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-siakad-active focus:outline-none focus:ring-1 focus:ring-siakad-active">
            </div>

            <div>
                <label for="jenis" class="mb-1 block text-xs font-semibold text-slate-600">Jenis</label>
                <select id="jenis" name="jenis"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-siakad-active focus:outline-none focus:ring-1 focus:ring-siakad-active">
                    <option value="">Semua jenis</option>
                    @foreach ($pilihanJenis as $kode => $label)
                        <option value="{{ $kode }}" @selected(($filter['jenis'] ?? '') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if ($pengelola)
                <div>
                    <label for="status" class="mb-1 block text-xs font-semibold text-slate-600">Status</label>
                    <select id="status" name="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-siakad-active focus:outline-none focus:ring-1 focus:ring-siakad-active">
                        <option value="">Semua status</option>
                        @foreach ($pilihanStatus as $kode => $label)
                            <option value="{{ $kode }}" @selected(($filter['status'] ?? '') === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if (!empty($filter['kelas']))
                <input type="hidden" name="kelas" value="{{ $filter['kelas'] }}">
            @endif

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="rounded-lg bg-siakad-active px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800 transition-colors">Cari</button>
                <a href="{{ route('kegiatan.index') }}"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            </div>
        </div>

        @if (!empty($filter['kelas']))
            <p class="mt-3 text-xs text-slate-500">
                Menampilkan satu kelas saja.
                <a class="font-semibold text-siakad-active hover:underline"
                    href="{{ route('kegiatan.index', array_filter(['q' => $filter['q'] ?? null, 'jenis' => $filter['jenis'] ?? null, 'status' => $filter['status'] ?? null])) }}">Tampilkan
                    semua kelas</a>
            </p>
        @endif
    </form>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($daftar as $item)
            <article
                class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md">
                @include('kegiatan._lencana')

                <h2 class="mt-3 text-base font-bold leading-snug text-slate-800">
                    <a href="{{ route('kegiatan.show', $item) }}" class="hover:text-siakad-active">{{ $item->judul }}</a>
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $item->kelasKuliah->kode }} &middot; {{ $item->kelasKuliah->nama_mk_snapshot }}
                </p>

                <div class="mt-4 flex-1 text-sm text-slate-600">
                    @if ($item->berupaMateri())
                        <p>Materi tersedia untuk dibaca dan diunduh.</p>
                    @else
                        <dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1">
                            <dt class="text-slate-400">Mulai</dt>
                            <dd>{{ $item->buka_at ? $item->buka_at->setTimezone($zona)->format('d-m-Y H:i') : '—' }}</dd>
                            <dt class="text-slate-400">Tenggat</dt>
                            <dd class="font-semibold text-slate-800">
                                {{ $item->tenggat_at ? $item->tenggat_at->setTimezone($zona)->format('d-m-Y H:i') : '—' }}
                            </dd>
                        </dl>
                        <p class="mt-1 text-[11px] text-slate-400">Zona waktu: {{ $zona }}</p>
                    @endif
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                    @if ($pengelola)
                        <span class="text-xs text-slate-500">
                            Status: <strong
                                class="text-slate-700">{{ \App\Models\Kegiatan::STATUS[$item->status] ?? $item->status }}</strong>
                        </span>
                    @else
                        <span></span>
                    @endif
                    <a href="{{ route('kegiatan.show', $item) }}"
                        class="text-sm font-semibold text-siakad-active hover:underline">Buka &rarr;</a>
                </div>
            </article>
        @empty
            <div
                class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm italic text-slate-500">
                Belum ada pembelajaran yang dapat ditampilkan untuk akun Anda.
            </div>
        @endforelse
    </div>

    {{ $daftar->links('kegiatan._pagination') }}
@endsection
