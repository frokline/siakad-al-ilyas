<?php

namespace Tests\Feature;

use App\Actions\KelolaNotifikasi;
use App\Models\{Notifikasi, Pengumuman, User};
use App\Policies\NotifikasiPolicy;
use App\Services\AksesNotifikasi;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Gate, Schema};

abstract class NotifikasiDatabaseTestCase extends PengumumanDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        config(['notifikasi.sumber_aktif' => ['pengumuman'], 'notifikasi.hari_sinkronisasi' => 30]);
        $paths = glob(database_path('migrations/*_create_notifikasi_table.php'));
        $this->assertCount(1, $paths);
        (require $paths[0])->up();
        Gate::policy(Notifikasi::class, NotifikasiPolicy::class);
        Gate::define('akses-notifikasi', fn(User $u): bool => app(AksesNotifikasi::class)->masuk($u));
    }
    protected function notif(int $user = 4, ?Pengumuman $p = null): Notifikasi
    {
        $p ??= $this->terbit($this->draf());
        app(KelolaNotifikasi::class)->kirim($user, 'pengumuman', (int) $p->id);
        return Notifikasi::query()->where('penerima_id', $user)->where('sumber_id', $p->id)->firstOrFail();
    }
    protected function sumberLain(): void
    {
        // Struktur minimal untuk query akses; data fixture dimasukkan langsung agar tidak meniru seluruh alur akademik.
        Schema::create('pertemuan', function (Blueprint $t): void {
            $t->id();
            $t->string('status');
        });
        foreach (['materi', 'kegiatan'] as $table) {
            Schema::create($table, function (Blueprint $t): void {
                $t->id();
                $t->unsignedBigInteger('kelas_kuliah_id');
                $t->unsignedBigInteger('pertemuan_id')->nullable();
                $t->string('status');
                $t->dateTime('terbit_at')->nullable();
                $t->unsignedInteger('revisi');
                $t->timestamps();
            });
            DB::table($table)->insert(['id' => 1, 'kelas_kuliah_id' => 1, 'status' => 'terbit', 'terbit_at' => now('UTC'), 'revisi' => 2, 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
        }
        Schema::create('tagihan', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('mahasiswa_id');
            $t->string('status');
            $t->dateTime('diterbitkan_at')->nullable();
            $t->unsignedInteger('revisi');
            $t->timestamps();
        });
        DB::table('tagihan')->insert(['id' => 1, 'mahasiswa_id' => 1, 'status' => 'terbit', 'diterbitkan_at' => now('UTC'), 'revisi' => 2, 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
        Schema::create('pembayaran', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('tagihan_id');
            $t->string('status');
            $t->unsignedInteger('revisi');
            $t->timestamps();
        });
        DB::table('pembayaran')->insert(['id' => 1, 'tagihan_id' => 1, 'status' => 'menunggu', 'revisi' => 1, 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
        Schema::create('permohonan_surat', function (Blueprint $t): void {
            $t->id();
            $t->unsignedBigInteger('pemohon_id');
            $t->unsignedBigInteger('mahasiswa_id');
            $t->string('status');
            $t->unsignedInteger('revisi');
            $t->timestamps();
        });
        DB::table('permohonan_surat')->insert(['id' => 1, 'pemohon_id' => 4, 'mahasiswa_id' => 1, 'status' => 'diajukan', 'revisi' => 1, 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
        Schema::create('kalender_akademik', function (Blueprint $t): void {
            $t->id();
            $t->string('status');
            $t->dateTime('diterbitkan_at')->nullable();
            $t->unsignedInteger('revisi');
            $t->timestamps();
        });
        DB::table('kalender_akademik')->insert(['id' => 1, 'status' => 'terbit', 'diterbitkan_at' => now('UTC'), 'revisi' => 2, 'created_at' => now('UTC'), 'updated_at' => now('UTC')]);
        Schema::table('krs', function (Blueprint $t): void {
            $t->unsignedInteger('versi')->default(1);
            $t->timestamps();
        });
        DB::table('krs')->update(['created_at' => now('UTC'), 'updated_at' => now('UTC')]);
        Gate::define('kelola-krs', fn(User $u): bool => app(\App\Services\AksesMateri::class)->admin($u));
        config(['notifikasi.sumber_aktif' => array_keys(\App\Services\SumberNotifikasi::DAFTAR)]);
    }
}
