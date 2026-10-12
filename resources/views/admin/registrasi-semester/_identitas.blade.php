{{-- Identitas mahasiswa & riwayat studi. Butuh variabel $riwayat. Dibungkus kartu oleh halaman pemanggil. --}}
<div class="flex items-center gap-4">
    <span
        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-lg font-bold text-siakad-dark">
        {{ strtoupper(mb_substr($riwayat->mahasiswa->user->nama ?? '?', 0, 1)) }}
    </span>
    <div class="min-w-0">
        <p class="truncate text-base font-bold text-slate-900">{{ $riwayat->mahasiswa->user->nama }}</p>
        <p class="text-sm text-slate-500">NIM {{ $riwayat->mahasiswa->nim }}</p>
    </div>
</div>
<dl class="mt-5 grid gap-x-8 gap-y-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
    <div>
        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Program studi</dt>
        <dd class="mt-1 text-sm text-slate-800">{{ $riwayat->kurikulum->programStudi->nama }}</dd>
    </div>
    <div>
        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Kurikulum</dt>
        <dd class="mt-1 text-sm text-slate-800">{{ $riwayat->kurikulum->kode }} — {{ $riwayat->kurikulum->nama }}</dd>
    </div>
    <div>
        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Angkatan</dt>
        <dd class="mt-1 text-sm text-slate-800">{{ $riwayat->angkatan }}</dd>
    </div>
    <div>
        <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Status riwayat studi</dt>
        <dd class="mt-1">@include('riwayat_studi._lencana', ['status' => $riwayat->status])</dd>
    </div>
</dl>
