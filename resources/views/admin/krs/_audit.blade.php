<?php $kelasAudit = $krs->details->keyBy('kelas_kuliah_id'); ?>
<div class="space-y-4">
    @forelse ($audits as $audit)
        <?php
        $sebelum = $audit->sebelum ?? [];
        $sesudah = $audit->sesudah ?? [];
        $detailSebelum = collect($sebelum['details'] ?? [])->keyBy('id');
        $statusSebelum = $sebelum['status'] ?? null;
        $statusSesudah = $sesudah['status'] ?? null;
        $detailsAudit = $sesudah['details'] ?? [];
        ?>
        <details class="group rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden"
            @if ($loop->first) open @endif>
            <summary
                class="flex cursor-pointer items-center justify-between bg-slate-50 px-5 py-4 hover:bg-slate-100 transition-colors">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <strong class="text-sm font-bold text-slate-800">
                            {{ \App\Models\Krs::OPERASI[$audit->aksi] ?? $audit->aksi }}
                        </strong>
                        <span
                            class="rounded-full bg-slate-200 px-2.5 py-0.5 text-[10px] font-bold text-slate-700 uppercase tracking-wider">Versi
                            {{ $audit->versi_entitas }}</span>
                    </div>
                    <span class="text-xs text-slate-500">
                        {{ $audit->pelaku?->nama ?? 'Sistem' }} &middot;
                        {{ $audit->waktu->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
                    </span>
                    <span class="text-xs font-medium text-slate-600 mt-1">
                        Status: <span
                            class="line-through text-slate-400">{{ \App\Models\Krs::STATUS[$statusSebelum] ?? 'Belum ada KRS' }}</span>
                        &rarr; <span
                            class="text-emerald-600 font-bold">{{ \App\Models\Krs::STATUS[$statusSesudah] ?? '—' }}</span>
                    </span>
                </div>
                <span class="text-slate-400 transition-transform duration-200 group-open:rotate-180">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </summary>

            <div class="border-t border-slate-200 p-5">
                @if ($audit->alasan)
                    <div class="mb-5">
                        <span class="font-bold text-slate-700 text-sm block mb-1">Alasan Tindakan:</span>
                        <p
                            class="text-slate-600 text-sm whitespace-pre-line bg-slate-50 p-3 rounded-lg border border-slate-100">
                            {{ $audit->alasan }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Catatan
                            Sebelum</span>
                        <p class="text-sm text-slate-600 whitespace-pre-line">{{ $sebelum['catatan'] ?? '—' }}</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Catatan
                            Sesudah</span>
                        <p class="text-sm text-slate-700 font-medium whitespace-pre-line">
                            {{ $sesudah['catatan'] ?? '—' }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-left text-sm text-slate-600 border-collapse">
                        <caption
                            class="bg-slate-50 px-4 py-3 text-xs font-bold text-slate-700 text-left border-b border-slate-200 uppercase tracking-wider">
                            Perubahan Keikutsertaan Mata Kuliah</caption>
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-semibold">Kelas & Mata Kuliah</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Status Sebelum</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Status Sesudah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($detailsAudit as $detailAudit)
                                <?php
                                $lama = $detailSebelum->get($detailAudit['id']);
                                $kelasTerkait = $kelasAudit->get($detailAudit['kelas_kuliah_id']);
                                $berubah = ($lama['status'] ?? '') !== $detailAudit['status'];
                                ?>
                                <tr class="{{ $berubah ? 'bg-amber-50/30' : '' }}">
                                    <td class="px-4 py-3">
                                        <strong
                                            class="font-bold text-slate-800 font-mono text-xs">{{ $kelasTerkait?->kelasKuliah->kode ?? '#' . $detailAudit['kelas_kuliah_id'] }}</strong>
                                        @if ($kelasTerkait)
                                            <div class="text-xs text-slate-500 mt-0.5">
                                                {{ $kelasTerkait->kelasKuliah->nama_mk_snapshot }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs {{ $berubah ? 'line-through text-slate-400' : '' }}">
                                        {{ \App\Models\DetailKrs::STATUS[$lama['status'] ?? ''] ?? 'Belum ada' }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs font-bold {{ $berubah ? 'text-emerald-700' : 'text-slate-700' }}">
                                        {{ \App\Models\DetailKrs::STATUS[$detailAudit['status']] ?? $detailAudit['status'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    @empty
        <div
            class="text-center py-8 text-slate-500 text-sm border border-dashed border-slate-300 rounded-xl bg-slate-50">
            Belum ada catatan perubahan riwayat KRS.
        </div>
    @endforelse
</div>
<div class="mt-6">
    <x-pagination :paginator="$audits" />
</div>
