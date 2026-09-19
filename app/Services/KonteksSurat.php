<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class KonteksSurat
{
    // Pemanggil berada dalam transaksi. Snapshot tidak mengandung data pembayaran.
    public function kunci(int $registrasiId, int $pemohonId): array
    {
        $r = DB::table('registrasi_semester')->where('id', $registrasiId)->lockForUpdate()->first();
        $s = $r ? DB::table('riwayat_studi')->where('id', $r->riwayat_studi_id)->lockForUpdate()->first() : null;
        $m = $s ? DB::table('mahasiswa')->where('id', $s->mahasiswa_id)->lockForUpdate()->first() : null;
        abort_unless($m && (int) $m->user_id === $pemohonId, 403);
        $u = DB::table('users')->where('id', $pemohonId)->lockForUpdate()->first();
        $periode = DB::table('periode_akademik')->where('id', $r->periode_akademik_id)->lockForUpdate()->first();
        $role = DB::table('user_roles')->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $pemohonId)->where('roles.kode', 'mahasiswa')->lockForUpdate()->first(['roles.id']);
        if (
            $r->status !== 'aktif' || $s->status !== 'aktif' || ! $periode || $periode->status !== 'aktif'
            || ! $u || $u->status !== 'aktif' || ! $role
        ) {
            throw ValidationException::withMessages(['registrasi_semester_id' => 'Surat aktif kuliah memerlukan akun, riwayat studi, registrasi, dan periode akademik aktif.']);
        }
        return [
            'mahasiswa_id' => (int) $m->id,
            'nim' => $m->nim,
            'nama' => $u->nama,
            'riwayat_studi_id' => (int) $s->id,
            'registrasi_semester_id' => (int) $r->id,
            'periode_akademik_id' => (int) $r->periode_akademik_id,
            'semester_studi' => (int) $r->semester_studi,
            'registrasi_revisi' => (int) $r->revisi
        ];
    }
    public function pilihan(int $userId): \Illuminate\Database\Query\Builder
    {
        return DB::table('registrasi_semester as r')->join('riwayat_studi as s', 's.id', '=', 'r.riwayat_studi_id')
            ->join('mahasiswa as m', 'm.id', '=', 's.mahasiswa_id')->join('periode_akademik as p', 'p.id', '=', 'r.periode_akademik_id')
            ->where('m.user_id', $userId)->where('r.status', 'aktif')->where('s.status', 'aktif')->where('p.status', 'aktif')
            ->select(['r.id', 'r.periode_akademik_id', 'r.semester_studi', 'm.nim'])->orderByDesc('r.id');
    }
}
