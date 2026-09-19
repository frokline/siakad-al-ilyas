@php($kelasAudit = $krs->details->keyBy('kelas_kuliah_id'))
<ol class="krs-audit-list">
    @forelse ($audits as $audit)
        @php
            $sebelum = $audit->sebelum ?? [];
            $sesudah = $audit->sesudah ?? [];
            $detailSebelum = collect($sebelum['details'] ?? [])->keyBy('id');
            $statusSebelum = $sebelum['status'] ?? null;
            $statusSesudah = $sesudah['status'] ?? null;
        @endphp
        <li class="krs-audit-item">
            <div class="krs-audit-heading">
                <strong>{{ \App\Models\Krs::OPERASI[$audit->aksi] ?? $audit->aksi }}</strong>
                <span class="badge">Versi {{ $audit->versi_entitas }}</span>
            </div>
            <p class="help">
                {{ $audit->pelaku?->nama ?? 'Sistem' }}
                · {{ $audit->waktu->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
            </p>
            <p>
                {{ \App\Models\Krs::STATUS[$statusSebelum] ?? 'Belum ada KRS' }}
                → {{ \App\Models\Krs::STATUS[$statusSesudah] ?? '—' }}
            </p>
            @if ($audit->alasan)
                <p><strong>Alasan:</strong></p>
                <p class="detail-multiline">{{ $audit->alasan }}</p>
            @endif

            <details class="krs-audit-rincian">
                <summary>Rincian perubahan versi {{ $audit->versi_entitas }}</summary>
                <div class="krs-audit-isi">
                    <dl class="detail-grid">
                        <div>
                            <dt>Catatan sebelum</dt>
                            <dd class="detail-multiline">{{ $sebelum['catatan'] ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Catatan sesudah</dt>
                            <dd class="detail-multiline">{{ $sesudah['catatan'] ?? '—' }}</dd>
                        </div>
                    </dl>
                    <div class="table-wrap">
                        <table class="krs-table">
                            <caption>Perubahan keikutsertaan mata kuliah</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Kelas</th>
                                    <th scope="col">Sebelum</th>
                                    <th scope="col">Sesudah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sesudah['details'] ?? [] as $detailAudit)
                                    @php
                                        $lama = $detailSebelum->get($detailAudit['id']);
                                        $kelasTerkait = $kelasAudit->get($detailAudit['kelas_kuliah_id']);
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $kelasTerkait?->kelasKuliah->kode ?? '#' . $detailAudit['kelas_kuliah_id'] }}
                                            @if ($kelasTerkait)
                                                <br>{{ $kelasTerkait->kelasKuliah->nama_mk_snapshot }}
                                            @endif
                                        </td>
                                        <td>{{ \App\Models\DetailKrs::STATUS[$lama['status'] ?? ''] ?? 'Belum ada' }}
                                        </td>
                                        <td>{{ \App\Models\DetailKrs::STATUS[$detailAudit['status']] ?? $detailAudit['status'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </details>
        </li>
    @empty
        <li>Belum ada catatan perubahan.</li>
    @endforelse
</ol>
<x-pagination :paginator="$audits" />
