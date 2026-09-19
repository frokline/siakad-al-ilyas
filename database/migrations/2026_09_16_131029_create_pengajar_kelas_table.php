<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajar_kelas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kelas_kuliah_id')
                ->constrained('kelas_kuliah')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('dosen_id')
                ->constrained('dosen')->restrictOnDelete()->restrictOnUpdate();
            $table->enum('peran', ['koordinator', 'pengajar']);
            $table->boolean('aktif')->default(true);
            $table->unsignedInteger('revisi')->default(1);
            $table->dateTime('diaktifkan_at');
            $table->dateTime('dinonaktifkan_at')->nullable();
            $table->unsignedTinyInteger('koordinator_aktif')->nullable()
                ->storedAs("CASE WHEN aktif = 1 AND peran = 'koordinator' THEN 1 ELSE NULL END");
            $table->timestamps();

            $table->unique(['kelas_kuliah_id', 'dosen_id'], 'pengajar_kelas_dosen_unique');
            $table->unique(['kelas_kuliah_id', 'koordinator_aktif'], 'pengajar_koordinator_aktif_unique');
            // Referensi gabungan untuk penanggung jawab pertemuan pada tahap berikutnya.
            $table->unique(['id', 'kelas_kuliah_id'], 'pengajar_identitas_kelas_unique');
            $table->index(['dosen_id', 'aktif'], 'pengajar_dosen_aktif_index');
        });

        DB::statement(
            'ALTER TABLE pengajar_kelas
             ADD CONSTRAINT pengajar_revisi_positif CHECK (revisi > 0),
             ADD CONSTRAINT pengajar_status_waktu_valid CHECK (
                 (aktif = 1 AND dinonaktifkan_at IS NULL)
                 OR (aktif = 0 AND dinonaktifkan_at IS NOT NULL
                     AND dinonaktifkan_at >= diaktifkan_at)
             )'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajar_kelas');
    }
};
