<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_kuliah', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kelas_kuliah_id')
                ->constrained('kelas_kuliah')->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedTinyInteger('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->date('berlaku_mulai');
            $table->date('berlaku_selesai');
            $table->enum('metode', ['daring', 'luring', 'campuran']);
            $table->string('lokasi', 150)->nullable();
            $table->text('tautan_pertemuan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->unsignedInteger('revisi')->default(1);
            $table->dateTime('dinonaktifkan_at')->nullable();
            $table->timestamps();

            // Disiapkan untuk FK gabungan pertemuan pada modul berikutnya.
            $table->unique(['id', 'kelas_kuliah_id'], 'jadwal_identitas_kelas_unique');
            $table->index(['kelas_kuliah_id', 'aktif', 'hari'], 'jadwal_kelas_aktif_hari_index');
            $table->index(['aktif', 'hari', 'berlaku_mulai', 'berlaku_selesai'], 'jadwal_pola_index');
        });

        DB::statement(
            "ALTER TABLE jadwal_kuliah
             ADD CONSTRAINT jadwal_hari_valid CHECK (hari BETWEEN 1 AND 7),
             ADD CONSTRAINT jadwal_jam_valid CHECK (
                 jam_mulai >= '00:00:00' AND jam_selesai <= '23:59:00'
                 AND jam_mulai < jam_selesai
                 AND SECOND(jam_mulai) = 0 AND SECOND(jam_selesai) = 0
             ),
             ADD CONSTRAINT jadwal_tanggal_valid CHECK (berlaku_mulai <= berlaku_selesai),
             ADD CONSTRAINT jadwal_revisi_valid CHECK (revisi > 0),
             ADD CONSTRAINT jadwal_status_valid CHECK (
                 (aktif = 1 AND dinonaktifkan_at IS NULL)
                 OR (aktif = 0 AND dinonaktifkan_at IS NOT NULL)
             ),
             ADD CONSTRAINT jadwal_media_valid CHECK (
                 (metode = 'daring' AND lokasi IS NULL)
                 OR (metode = 'luring' AND lokasi IS NOT NULL
                     AND CHAR_LENGTH(TRIM(lokasi)) > 0 AND tautan_pertemuan IS NULL)
                 OR (metode = 'campuran' AND lokasi IS NOT NULL
                     AND CHAR_LENGTH(TRIM(lokasi)) > 0)
             )"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_kuliah');
    }
};
