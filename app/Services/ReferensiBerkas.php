<?php

namespace App\Services;

use App\Models\Berkas;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReferensiBerkas
{
    public function pastikanBelumDipakai(Berkas $file): void
    {
        // Dipanggil setelah row berkas dikunci. Tidak perlu menebak tabel modul masa depan.
        $db = DB::connection()->getDatabaseName();
        $refs = DB::select(
            'SELECT TABLE_SCHEMA AS db_name, TABLE_NAME AS table_name, COLUMN_NAME AS column_name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = ?',
            [$db, 'berkas', 'id']
        );
        foreach ($refs as $ref) {
            if (
                $ref->db_name !== $db || ! preg_match('/\A[a-zA-Z0-9_]+\z/', $ref->table_name)
                || ! preg_match('/\A[a-zA-Z0-9_]+\z/', $ref->column_name)
            ) {
                $this->gagal('Referensi berkas perlu diperiksa oleh pengelola sistem.');
            }
            if (DB::table($ref->table_name)->where($ref->column_name, $file->id)->exists()) {
                $this->gagal('Berkas sudah dipakai pada modul lain. Berkas tersebut tidak dapat dinonaktifkan atau diganti keterangannya dari halaman ini.');
            }
        }
    }
    private function gagal(string $pesan): never
    {
        throw ValidationException::withMessages(['berkas' => $pesan]);
    }
}
