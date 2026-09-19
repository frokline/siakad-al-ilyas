<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Berkas;

final class LogBerkas
{
    public function tulis(Berkas $file, ?int $pelakuId, string $aksi, ?array $sebelum, ?string $alasan): void
    {
        $audit = new AuditLog();
        $audit->pelaku_id = $pelakuId;
        $audit->entitas = 'berkas';
        $audit->entitas_id = $file->id;
        $audit->versi_entitas = $file->revisi;
        $audit->aksi = $aksi;
        $audit->sebelum = $sebelum;
        $audit->sesudah = $file->ringkasanAudit();
        $audit->alasan = $alasan;
        $audit->waktu = now('UTC');
        $audit->save();
    }
}
