<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_studi', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('mahasiswa_id')
                ->constrained('mahasiswa')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('kurikulum_id')
                ->constrained('kurikulum')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->unsignedSmallInteger('angkatan');

            $table->foreignId('periode_mulai_id')
                ->constrained('periode_akademik')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('periode_akhir_id')
                ->nullable()
                ->constrained('periode_akademik')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('dosen_pa_id')
                ->nullable()
                ->constrained('dosen')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->enum('status', [
                'aktif',
                'selesai',
                'pindah',
                'keluar',
            ])->default('aktif');

            $table->tinyInteger('aktif_guard')
                ->nullable()
                ->storedAs(
                    "CASE WHEN status = 'aktif' THEN 1 ELSE NULL END"
                );

            $table->timestamps();

            $table->unique(
                ['mahasiswa_id', 'kurikulum_id', 'periode_mulai_id'],
                'rs_identitas_unique'
            );

            $table->unique(
                ['mahasiswa_id', 'aktif_guard'],
                'rs_satu_aktif_unique'
            );

            $table->index(
                ['status', 'angkatan'],
                'rs_status_angkatan_index'
            );
        });

        DB::statement(<<<'SQL'
            ALTER TABLE `riwayat_studi`
            ADD CONSTRAINT `rs_angkatan_check`
                CHECK (`angkatan` BETWEEN 1900 AND 9999),
            ADD CONSTRAINT `rs_status_akhir_check`
                CHECK (
                    (`status` = 'aktif' AND `periode_akhir_id` IS NULL)
                    OR
                    (
                        `status` IN ('selesai', 'pindah', 'keluar')
                        AND `periode_akhir_id` IS NOT NULL
                    )
                )
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_studi');
    }
};
