<ol class="pengajar-audit-list">
    @forelse ($audits as $audit)
        @php
            $sebelum = $audit->sebelum ?? [];
            $sesudah = $audit->sesudah ?? [];
        @endphp
        <li class="pengajar-audit-item">
            <div class="pengajar-audit-heading">
                <strong>{{ \App\Models\PengajarKelas::AKSI_AUDIT[$audit->aksi] ?? $audit->aksi }}</strong>
                <span class="badge">Revisi {{ $audit->versi_entitas }}</span>
            </div>
            <p class="help">
                {{ $audit->pelaku?->nama ?? 'Sistem' }}
                · {{ $audit->waktu->timezone(config('siakad.timezone', 'Asia/Makassar'))->format('d-m-Y H:i:s') }}
            </p>
            <dl class="detail-grid">
                <div>
                    <dt>Peran</dt>
                    <dd>{{ \App\Models\PengajarKelas::PERAN[$sebelum['peran'] ?? ''] ?? 'Belum ada' }} →
                        {{ \App\Models\PengajarKelas::PERAN[$sesudah['peran'] ?? ''] ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Penugasan</dt>
                    <dd>
                        {{ array_key_exists('aktif', $sebelum) ? ($sebelum['aktif'] ? 'Aktif' : 'Nonaktif') : 'Belum ada' }}
                        → {{ $sesudah['aktif'] ?? false ? 'Aktif' : 'Nonaktif' }}
                    </dd>
                </div>
            </dl>
            <p><strong>Alasan:</strong></p>
            <p class="detail-multiline">{{ $audit->alasan }}</p>
            @if (!empty($sesudah['koordinator_pengganti_id']))
                <p>
                    Penggantian ini menetapkan
                    <a href="{{ route('admin.pengajar-kelas.show', $sesudah['koordinator_pengganti_id']) }}">
                        penugasan #{{ $sesudah['koordinator_pengganti_id'] }}
                    </a>
                    sebagai koordinator.
                </p>
            @endif
        </li>
    @empty
        <li>Belum ada riwayat perubahan.</li>
    @endforelse
</ol>
<x-pagination :paginator="$audits" />
