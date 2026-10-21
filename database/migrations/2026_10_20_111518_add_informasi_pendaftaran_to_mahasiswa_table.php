<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table): void {
            $table->string('konsentrasi', 150)->nullable();
            $table->string('sistem_kuliah', 100)->nullable();
            $table->string('jenis_pendaftaran', 100)->nullable();
            $table->string('jalur_pendaftaran', 100)->nullable();
            $table->string('gelombang', 50)->nullable();
            $table->date('tanggal_awal_masuk')->nullable();
            $table->date('tanggal_daftar_ulang')->nullable();
            $table->text('kebutuhan_khusus')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table): void {
            $table->dropColumn([
                'konsentrasi',
                'sistem_kuliah',
                'jenis_pendaftaran',
                'jalur_pendaftaran',
                'gelombang',
                'tanggal_awal_masuk',
                'tanggal_daftar_ulang',
                'kebutuhan_khusus',
            ]);
        });
    }
};
