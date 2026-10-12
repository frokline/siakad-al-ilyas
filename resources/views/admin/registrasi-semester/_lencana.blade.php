{{-- Lencana status registrasi. Pakai: @include('admin.registrasi-semester._lencana', ['status' => $registrasi->status]) --}}
@php
    $kelasLencana = match ($status) {
        \App\Models\RegistrasiSemester::AKTIF => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        \App\Models\RegistrasiSemester::TERDAFTAR => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        \App\Models\RegistrasiSemester::CUTI => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        \App\Models\RegistrasiSemester::BATAL => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/20',
    };
@endphp
<span
    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $kelasLencana }}">
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ \App\Models\RegistrasiSemester::STATUS[$status] ?? ucfirst((string) $status) }}
</span>
