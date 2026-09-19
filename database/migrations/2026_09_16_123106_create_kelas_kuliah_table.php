<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas_kuliah', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rombel_id')
                ->constrained('rombel')->restrictOnDelete()->restrictOnUpdate();
            $table->foreignId('detail_paket_id')
                ->constrained('detail_paket')->restrictOnDelete()->restrictOnUpdate();
            $table->string('kode', 40);
            $table->string('nama_mk_snapshot', 150);
            $table->decimal('sks_snapshot', 4, 1);
            $table->enum('status', ['persiapan', 'aktif', 'selesai', 'arsip'])
                ->default('persiapan');
            $table->unsignedInteger('revisi')->default(1);
            $table->timestamp('diaktifkan_at')->nullable();
            $table->timestamp('diselesaikan_at')->nullable();
            $table->timestamp('diarsipkan_at')->nullable();
            $table->timestamps();

            $table->unique(['rombel_id', 'detail_paket_id'], 'kelas_rombel_detail_unique');
            $table->unique(['rombel_id', 'kode'], 'kelas_rombel_kode_unique');
            $table->index(['rombel_id', 'status'], 'kelas_rombel_status_index');
            $table->index(['status', 'id'], 'kelas_status_id_index');
        });

        // CHECK untuk target MariaDB / MySQL proyek ini.
        DB::statement(
            'ALTER TABLE kelas_kuliah
             ADD CONSTRAINT kelas_sks_positif CHECK (sks_snapshot > 0),
             ADD CONSTRAINT kelas_revisi_positif CHECK (revisi > 0),
             ADD CONSTRAINT kelas_nama_tidak_kosong
                 CHECK (CHAR_LENGTH(TRIM(nama_mk_snapshot)) > 0),
             ADD CONSTRAINT kelas_status_waktu_valid CHECK (
                 (status = \'persiapan\'
                     AND diaktifkan_at IS NULL
                     AND diselesaikan_at IS NULL
                     AND diarsipkan_at IS NULL)
                 OR
                 (status = \'aktif\'
                     AND diaktifkan_at IS NOT NULL
                     AND diselesaikan_at IS NULL
                     AND diarsipkan_at IS NULL)
                 OR
                 (status = \'selesai\'
                     AND diaktifkan_at IS NOT NULL
                     AND diselesaikan_at IS NOT NULL
                     AND diselesaikan_at >= diaktifkan_at
                     AND diarsipkan_at IS NULL)
                 OR
                 (status = \'arsip\'
                     AND diarsipkan_at IS NOT NULL
                     AND (
                         (diaktifkan_at IS NULL AND diselesaikan_at IS NULL)
                         OR
                         (diaktifkan_at IS NOT NULL
                             AND diselesaikan_at IS NOT NULL
                             AND diselesaikan_at >= diaktifkan_at
                             AND diarsipkan_at >= diselesaikan_at)
                     ))
             )'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_kuliah');
    }
};
