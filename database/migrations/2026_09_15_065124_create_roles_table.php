<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 40)->unique();
            $table->string('nama', 80);
            $table->timestamps();
        });

        $now = now();

        DB::table('roles')->insert([
            [
                'kode' => 'mahasiswa',
                'nama' => 'Mahasiswa',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode' => 'dosen',
                'nama' => 'Dosen',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode' => 'admin_akademik',
                'nama' => 'Admin Akademik',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode' => 'admin_keuangan',
                'nama' => 'Admin Keuangan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
