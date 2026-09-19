<?php

namespace App\Services;

use App\Actions\KelolaNotifikasi;
use App\Models\User;
use Carbon\CarbonImmutable;

final class SinkronNotifikasi
{
    public function __construct(private SumberNotifikasi $sumber, private KelolaNotifikasi $aksi) {}
    public function pengguna(int $userId, CarbonImmutable $sejak): int
    {
        $u = User::query()->find($userId);
        if (! $u || ! $this->sumber->masuk($u)) {
            return 0;
        }
        $jumlah = 0;
        foreach ($this->sumber->aktif() as $jenis) {
            $def = $this->sumber->definisi($jenis);
            $table = $def['tabel'];
            $this->sumber->query($jenis, $u)->where($table . '.updated_at', '>=', $sejak->utc()->format('Y-m-d H:i:s'))
                ->select($table . '.id')->chunkById(100, function ($rows) use ($userId, $jenis, &$jumlah): void {
                    foreach ($rows as $row) {
                        if ($this->aksi->kirim($userId, $jenis, (int) $row->id)) {
                            $jumlah++;
                        }
                    }
                }, $table . '.id', 'id');
        }
        return $jumlah;
    }
}
