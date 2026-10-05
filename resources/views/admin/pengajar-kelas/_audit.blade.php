<ol class="space-y-6">
    @forelse ($audits as $audit)
        @php
            $sebelum = $audit->sebelum ?? [];
            $sesudah = $audit->sesudah ?? [];
        @endphp
        <li class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <strong
                    class="text-sm font-bold text-slate-800">{{ \App\Models\PengajarKelas::AKSI_AUDIT[$audit->aksi] ?? $audit->aksi }}</strong>
                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">Revisi
                    {{ $audit->versi_entitas }}</span>
            </div>
            <p class="text-xs text-slate-500 mb-4">
                {{ $audit->pelaku?->nama ?? 'Sistem' }}
                &middot;
                {{ $audit->waktu->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
            </p>
            <div
                class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-3 rounded-lg border border-slate-100 text-xs mb-3">
                <div>
                    <span class="font-bold text-slate-400 uppercase tracking-wider block">Peran</span>
                    <span class="font-semibold text-slate-700 mt-0.5 block">
                        {{ \App\Models\PengajarKelas::PERAN[$sebelum['peran'] ?? ''] ?? 'Belum ada' }} →
                        {{ \App\Models\PengajarKelas::PERAN[$sesudah['peran'] ?? ''] ?? '—' }}
                    </span>
                </div>
                <div>
                    <span class="font-bold text-slate-400 uppercase tracking-wider block">Penugasan</span>
                    <span class="font-semibold text-slate-700 mt-0.5 block">
                        {{ array_key_exists('aktif', $sebelum) ? ($sebelum['aktif'] ? 'Aktif' : 'Nonaktif') : 'Belum ada' }}
                        →
                        {{ $sesudah['aktif'] ?? false ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
            <div class="text-sm">
                <span class="font-bold text-slate-700 block mb-1">Alasan:</span>
                <p class="text-slate-600 whitespace-pre-line bg-slate-50 p-3 rounded-lg border border-slate-100">
                    {{ $audit->alasan }}</p>
            </div>
            @if (!empty($sesudah['koordinator_pengganti_id']))
                <div class="mt-3 text-xs text-siakad-dark font-medium">
                    Penggantian ini menetapkan
                    <a href="{{ route('admin.pengajar-kelas.show', $sesudah['koordinator_pengganti_id']) }}"
                        class="underline font-bold">
                        penugasan #{{ $sesudah['koordinator_pengganti_id'] }}
                    </a>
                    sebagai koordinator.
                </div>
            @endif
        </li>
    @empty
        <li class="text-center py-6 text-slate-500 text-sm">Belum ada riwayat perubahan.</li>
    @endforelse
</ol>
<div class="mt-6">
    <x-pagination :paginator="$audits" />
</div>
