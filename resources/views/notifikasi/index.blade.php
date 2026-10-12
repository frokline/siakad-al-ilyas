@extends('layouts.admin')

@section('title', 'Notifikasi')

@section('content')
    @php
        $zona = config('siakad.timezone', 'Asia/Makassar');
        $statusAktif = $filter['status'] ?? 'semua';
        $jenisDipilih = $filter['jenis'] ?? '';
        $adaFilter = $statusAktif !== 'semua' || $jenisDipilih !== '';
        $idsBelum = $daftar->getCollection()->whereNull('dibaca_at')->pluck('id');

        $tab = ['semua' => 'Semua', 'belum' => 'Belum dibaca', 'sudah' => 'Sudah dibaca'];

        // Tampilan per jenis sumber. Jenis yang tidak dikenal memakai tampilan bawaan.
        $ikonLonceng =
            'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
        $tampilan = [
            'pengumuman' => [
                'Pengumuman',
                'bg-amber-50 text-amber-700 ring-amber-600/20',
                'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
            ],
            'materi' => [
                'Materi',
                'bg-sky-50 text-sky-700 ring-sky-600/20',
                'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            ],
            'kegiatan' => [
                'Tugas & Kegiatan',
                'bg-violet-50 text-violet-700 ring-violet-600/20',
                'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            ],
            'tagihan' => [
                'Tagihan',
                'bg-rose-50 text-rose-700 ring-rose-600/20',
                'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
            ],
            'pembayaran' => [
                'Pembayaran',
                'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
            ],
            'surat' => [
                'Surat',
                'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            ],
            'krs' => [
                'KRS',
                'bg-teal-50 text-teal-700 ring-teal-600/20',
                'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            ],
            'kalender' => [
                'Kalender Akademik',
                'bg-orange-50 text-orange-700 ring-orange-600/20',
                'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            ],
        ];
        $lihat = fn(string $jenis) => $tampilan[$jenis] ?? [
            ucfirst($jenis),
            'bg-slate-100 text-slate-600 ring-slate-500/20',
            $ikonLonceng,
        ];
        $input =
            'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm focus:border-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/20';
    @endphp

    {{-- Judul halaman --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs text-slate-500" aria-label="Breadcrumb">
                <span>Utama</span>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-slate-700">Notifikasi</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900">Notifikasi Saya</h1>
            <p class="mt-1 text-sm text-slate-500">Pemberitahuan dari pengumuman, tugas, tagihan, surat, dan layanan lain.
            </p>
        </div>

        @if ($idsBelum->isNotEmpty())
            <form method="post" action="{{ route('notifikasi.baca-halaman') }}">
                @csrf
                @foreach ($idsBelum as $id)
                    <input type="hidden" name="ids[]" value="{{ $id }}">
                @endforeach
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-siakad-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-siakad-active focus:outline-none focus:ring-2 focus:ring-siakad-dark/40">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Tandai {{ $idsBelum->count() }} di halaman ini sudah dibaca
                </button>
            </form>
        @endif
    </div>

    {{-- Pesan hasil aksi (flash "info" dari controller) --}}
    @if (is_string(session('info')) && session('info') !== '')
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 shadow-sm"
            role="status">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white/80 font-bold"
                aria-hidden="true">✓</span>
            <p class="pt-0.5 leading-6">{{ session('info') }}</p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-4">
        {{-- Daftar notifikasi --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-3">
            {{-- Tab status --}}
            <div
                class="flex flex-col gap-3 border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <nav class="flex flex-wrap gap-2" aria-label="Filter status baca">
                    @foreach ($tab as $kode => $nama)
                        <a href="{{ route('notifikasi.index', array_filter(['status' => $kode === 'semua' ? null : $kode, 'jenis' => $jenisDipilih ?: null])) }}"
                            @if ($statusAktif === $kode) aria-current="page" @endif
                            class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $statusAktif === $kode ? 'bg-siakad-dark text-white shadow-sm' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:text-siakad-dark hover:ring-siakad-dark/40' }}">
                            {{ $nama }}
                            @if ($kode === 'belum' && $belum > 0)
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs {{ $statusAktif === $kode ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' }}">{{ $belum }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <form method="get" action="{{ route('notifikasi.index') }}" class="flex items-center gap-2" onsubmit="const j=this.querySelector('#jenis'); if(j && !j.value){ j.removeAttribute('name'); }">
                    @if ($statusAktif !== 'semua')
                        <input type="hidden" name="status" value="{{ $statusAktif }}">
                    @endif
                    <label for="jenis" class="sr-only">Jenis sumber</label>
                    <select id="jenis" name="jenis" onchange="const s=this; if(!s.value){ s.removeAttribute('name'); } s.form.submit();"
                        class="{{ $input }} !py-1.5 sm:w-52">
                        <option value="">Semua jenis</option>
                        @foreach ($jenisAktif as $jenis)
                            <option value="{{ $jenis }}" @selected($jenisDipilih === $jenis)>{{ $lihat($jenis)[0] }}
                            </option>
                        @endforeach
                    </select>
                    <noscript><button type="submit"
                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">Terapkan</button></noscript>
                </form>
            </div>

            @error('status')
                <p class="px-6 pt-3 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            @error('jenis')
                <p class="px-6 pt-3 text-xs text-rose-600">{{ $message }}</p>
            @enderror

            @if ($daftar->isEmpty())
                <div class="px-6 py-16 text-center">
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonLonceng }}" />
                        </svg>
                    </div>
                    <p class="mt-4 font-semibold text-slate-700">
                        {{ $adaFilter ? 'Tidak ada notifikasi yang sesuai filter.' : 'Belum ada notifikasi.' }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($adaFilter)
                            <a href="{{ route('notifikasi.index') }}"
                                class="font-semibold text-siakad-active hover:underline">Tampilkan semua notifikasi</a>
                        @else
                            Notifikasi baru muncul setelah sinkronisasi berkala berjalan (bila penjadwal aktif).
                        @endif
                    </p>
                </div>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($daftar as $n)
                        @php
                            [$namaJenis, $kelasJenis, $ikonJenis] = $lihat((string) $n->jenis);
                            $baru = $n->dibaca_at === null;
                        @endphp
                        <li class="relative flex items-stretch {{ $baru ? 'bg-emerald-50/40' : '' }}">
                            @if ($baru)
                                <span class="absolute inset-y-0 left-0 w-1 bg-siakad-dark" aria-hidden="true"></span>
                            @endif

                            {{-- Buka sumber (sekaligus menandai sudah dibaca) --}}
                            <form method="post" action="{{ route('notifikasi.buka', $n) }}" class="min-w-0 flex-1">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-start gap-4 px-4 py-4 text-left transition hover:bg-emerald-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-siakad-dark/50 sm:px-6">
                                    <span
                                        class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset {{ $kelasJenis }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonJenis }}" />
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="text-sm {{ $baru ? 'font-bold text-slate-900' : 'font-medium text-slate-700' }}">{{ $n->judul }}</span>
                                            @if ($baru)
                                                <span
                                                    class="rounded-full bg-siakad-dark px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">Baru</span>
                                            @endif
                                        </span>
                                        <span
                                            class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                            <span
                                                class="rounded-md px-2 py-0.5 font-semibold ring-1 ring-inset {{ $kelasJenis }}">{{ $namaJenis }}</span>
                                            <span>Sumber #{{ $n->sumber_id }} · Revisi {{ $n->sumber_revisi }}</span>
                                        </span>
                                        <span class="mt-1.5 block text-xs text-slate-500">
                                            Diterima {{ $n->created_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                            @if ($n->dibaca_at)
                                                · Dibaca {{ $n->dibaca_at->setTimezone($zona)->format('d-m-Y H:i') }}
                                            @endif
                                        </span>
                                    </span>
                                    <svg class="mt-3 hidden h-4 w-4 shrink-0 text-slate-400 sm:block" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </form>

                            {{-- Tandai dibaca tanpa membuka sumber --}}
                            @if ($baru)
                                <form method="post" action="{{ route('notifikasi.baca', $n) }}"
                                    class="flex items-center pr-4 sm:pr-6">
                                    @csrf
                                    <button type="submit" title="Tandai sudah dibaca"
                                        aria-label="Tandai sudah dibaca: {{ $n->judul }}"
                                        class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-500 transition hover:border-siakad-dark hover:bg-emerald-50 hover:text-siakad-dark focus:outline-none focus:ring-2 focus:ring-siakad-dark/40">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($daftar->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $daftar->links('notifikasi._pagination') }}
                </div>
            @endif
        </section>

        {{-- Panel samping --}}
        <aside class="space-y-6">
            <section class="rounded-2xl bg-siakad-dark p-6 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-200">Belum dibaca</p>
                <p class="mt-2 text-4xl font-bold">{{ number_format($belum, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-emerald-200">yang masih dapat Anda akses</p>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 text-sm leading-6 text-slate-600 shadow-sm">
                <p class="font-semibold text-slate-800">Perlu diketahui</p>
                <ul class="mt-2 space-y-2">
                    <li>• Pilih <strong>Buka</strong> untuk melihat keadaan terbaru sumber; notifikasi otomatis ditandai
                        dibaca.</li>
                    <li>• Notifikasi lama dapat merujuk revisi sebelumnya.</li>
                    <li>• Sumber yang sudah tidak boleh Anda akses tidak ditampilkan.</li>
                    <li>• Notifikasi tidak dihapus; hanya ditandai dibaca.</li>
                </ul>
            </section>
        </aside>
    </div>
@endsection
