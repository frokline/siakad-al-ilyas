@extends('layouts.admin')
@section('title', 'Detail KRS')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                KRS #{{ $krs->id }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                <span class="font-mono font-bold">{{ $registrasi->riwayatStudi->mahasiswa->nim }}</span> &mdash;
                {{ $registrasi->riwayatStudi->mahasiswa->user->nama }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if ($krs->status === \App\Models\Krs::DRAF && $periodeTerbuka && $jendelaTerbuka)
                <a class="inline-flex items-center gap-2 rounded-lg bg-siakad-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-800"
                    href="{{ route('admin.krs.edit', $krs) }}">Edit catatan draf</a>
            @endif
            @if ($krs->status === \App\Models\Krs::DISAHKAN)
                <a class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700"
                    href="{{ route('admin.krs.cetak', $krs) }}" target="_blank">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak KRS
                </a>
            @endif
            <a class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
                href="{{ route('admin.krs.index') }}">Kembali ke daftar</a>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">Identitas Akademik</h2>
            @php
                $statusBadgeClass = match ($krs->status) {
                    'disahkan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'diajukan' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'draf' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                };
            @endphp
            <span
                class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold tracking-wide uppercase {{ $statusBadgeClass }}">
                {{ \App\Models\Krs::STATUS[$krs->status] }} &bull; Versi {{ $krs->versi }}
            </span>
        </div>
        <div class="p-6">@include('admin.krs._identitas')</div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Paket Mata Kuliah</h2>
        </div>
        @include('admin.krs._mata_kuliah')
        <div class="bg-slate-50/50 p-4 text-xs text-slate-500 italic text-center">
            Pengesahan keikutsertaan berlaku untuk seluruh paket. Kelas yang telah selesai akan tetap tersimpan secara
            permanen dalam riwayat akademik.
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Log Pengajuan dan Pengesahan</h2>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm mb-6 pb-6 border-b border-slate-100">
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Pengajuan</dt>
                    <dd class="mt-1 font-semibold text-slate-800 font-mono text-xs">
                        {{ $krs->diajukan_at?->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tanggal Pengesahan</dt>
                    <dd class="mt-1 font-semibold text-slate-800 font-mono text-xs">
                        {{ $krs->disahkan_at?->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Admin Pengesah</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $krs->pengesah?->nama ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-400 uppercase tracking-wider">Terakhir Diperbarui</dt>
                    <dd class="mt-1 font-semibold text-slate-800 font-mono text-xs">
                        {{ $krs->updated_at->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
                    </dd>
                </div>
            </dl>

            <div>
                <h3 class="text-sm font-bold text-slate-800 mb-2">Catatan KRS Dosen/Admin</h3>
                <div
                    class="bg-slate-50 border border-slate-100 rounded-lg p-4 text-sm text-slate-600 whitespace-pre-line italic">
                    {{ $krs->catatan ?? 'Tidak ada catatan terlampir.' }}
                </div>
            </div>

            @if ($krs->status === \App\Models\Krs::DIBATALKAN)
                <div
                    class="mt-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-xs font-semibold text-rose-700 flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    KRS ini telah berstatus dibatalkan. Tanggal pengajuan dan pengesahan di atas hanya menunjukkan log
                    riwayat proses sebelumnya.
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-xl bg-slate-50 shadow-sm border border-slate-300 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-300 bg-slate-100/80 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Panel Tindakan (Action) KRS</h2>
        </div>
        <div class="p-6">
            <div class="mb-6 bg-white p-4 rounded-lg border border-slate-200 text-xs text-slate-500">
                Pengesahan dan pengembalian draf tetap boleh diproses setelah batas waktu (jendela) pengisian berlalu,
                selama status periode masih aktif. Tindakan Revisi dan Pengajuan Ulang wajib mematuhi jadwal batas pengisian
                KRS.
            </div>
            @include('admin.krs._operasi')
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden w-full mb-8">
        <div class="border-b border-slate-200 bg-slate-50/50 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-800">Riwayat Perubahan & Audit KRS</h2>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-500 mb-4">Tindakan terbaru akan ditampilkan di urutan teratas. Waktu sistem dicatat
                menggunakan zona waktu {{ config('siakad.timezone', 'Asia/Makassar') }}.</p>
            @include('admin.krs._audit')
        </div>
    </div>
@endsection
