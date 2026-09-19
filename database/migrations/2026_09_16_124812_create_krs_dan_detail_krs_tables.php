<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('krs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registrasi_semester_id')->unique()
                ->constrained('registrasi_semester')->restrictOnDelete()->restrictOnUpdate();
            $table->enum('status', ['draf', 'diajukan', 'disahkan', 'dibatalkan'])->default('draf');
            $table->unsignedInteger('versi')->default(1);
            $table->dateTime('diajukan_at')->nullable();
            $table->foreignId('disahkan_oleh')->nullable()
                ->constrained('users')->restrictOnDelete()->restrictOnUpdate();
            $table->dateTime('disahkan_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['status', 'id'], 'krs_status_id_index');
        });

        DB::statement(
            'ALTER TABLE krs
             ADD CONSTRAINT krs_versi_positif CHECK (versi > 0),
             ADD CONSTRAINT krs_metadata_status_valid CHECK (
                 (status = \'draf\'
                     AND diajukan_at IS NULL
                     AND disahkan_oleh IS NULL AND disahkan_at IS NULL)
                 OR
                 (status = \'diajukan\'
                     AND diajukan_at IS NOT NULL
                     AND disahkan_oleh IS NULL AND disahkan_at IS NULL)
                 OR
                 (status = \'disahkan\'
                     AND diajukan_at IS NOT NULL
                     AND disahkan_oleh IS NOT NULL AND disahkan_at IS NOT NULL
                     AND disahkan_at >= diajukan_at)
                 OR
                 (status = \'dibatalkan\' AND (
                     (disahkan_oleh IS NULL AND disahkan_at IS NULL)
                     OR (diajukan_at IS NOT NULL
                         AND disahkan_oleh IS NOT NULL AND disahkan_at IS NOT NULL
                         AND disahkan_at >= diajukan_at)
                 ))
             )'
        );

        Schema::create('detail_krs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('krs_id')
                ->constrained('krs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('kelas_kuliah_id')
                ->constrained('kelas_kuliah')->restrictOnDelete()->restrictOnUpdate();
            $table->enum('status', ['terdaftar', 'aktif', 'dibatalkan'])->default('terdaftar');
            $table->dateTime('aktif_at')->nullable();
            $table->dateTime('batal_at')->nullable();
            $table->timestamps();

            $table->unique(['krs_id', 'kelas_kuliah_id'], 'detail_krs_kelas_unique');
            $table->index(['kelas_kuliah_id', 'status'], 'detail_krs_kelas_status_index');
        });

        DB::statement(
            'ALTER TABLE detail_krs
             ADD CONSTRAINT detail_krs_status_waktu_valid CHECK (
                 (status = \'terdaftar\' AND aktif_at IS NULL AND batal_at IS NULL)
                 OR (status = \'aktif\' AND aktif_at IS NOT NULL AND batal_at IS NULL)
                 OR (status = \'dibatalkan\' AND batal_at IS NOT NULL
                     AND (aktif_at IS NULL OR batal_at >= aktif_at))
             )'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_krs');
        Schema::dropIfExists('krs');
    }
};
