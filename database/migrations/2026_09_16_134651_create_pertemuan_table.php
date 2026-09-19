<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertemuan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliah')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->unsignedBigInteger('jadwal_kuliah_id')->nullable();
            $table->unsignedBigInteger('pengajar_kelas_id');
            $table->unsignedSmallInteger('nomor');
            $table->enum('jenis', ['kuliah', 'uts', 'uas', 'pengganti']);
            $table->string('topik', 200);
            $table->text('rencana')->nullable();
            $table->text('realisasi')->nullable();
            $table->dateTime('mulai_rencana');
            $table->dateTime('selesai_rencana');
            $table->dateTime('mulai_aktual')->nullable();
            $table->dateTime('selesai_aktual')->nullable();
            $table->enum('metode', ['daring', 'luring', 'campuran']);
            $table->string('lokasi', 150)->nullable();
            $table->text('tautan_pertemuan')->nullable();
            $table->json('jadwal_snapshot')->nullable();
            $table->json('pengajar_snapshot');
            $table->enum('status', ['terjadwal', 'berlangsung', 'selesai', 'batal'])->default('terjadwal');
            $table->unsignedInteger('revisi')->default(1);
            $table->dateTime('dibatalkan_at')->nullable();
            $table->timestamps();

            $table->foreign(['jadwal_kuliah_id', 'kelas_kuliah_id'], 'pertemuan_jadwal_kelas_fk')
                ->references(['id', 'kelas_kuliah_id'])->on('jadwal_kuliah')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign(['pengajar_kelas_id', 'kelas_kuliah_id'], 'pertemuan_pengajar_kelas_fk')
                ->references(['id', 'kelas_kuliah_id'])->on('pengajar_kelas')->restrictOnDelete()->restrictOnUpdate();
            $table->unique(['kelas_kuliah_id', 'nomor'], 'pertemuan_nomor_kelas_unique');
            $table->unique(['id', 'kelas_kuliah_id'], 'pertemuan_identitas_kelas_unique');
            $table->index(['status', 'mulai_rencana', 'selesai_rencana'], 'pertemuan_waktu_index');
            $table->index(['pengajar_kelas_id', 'status'], 'pertemuan_pengajar_status_index');
        });

        DB::statement(
            "ALTER TABLE pertemuan
             ADD CONSTRAINT pertemuan_nomor_valid CHECK (nomor > 0),
             ADD CONSTRAINT pertemuan_revisi_valid CHECK (revisi > 0),
             ADD CONSTRAINT pertemuan_rencana_valid CHECK (mulai_rencana < selesai_rencana),
             ADD CONSTRAINT pertemuan_sumber_valid CHECK (
                 (jadwal_kuliah_id IS NULL AND jadwal_snapshot IS NULL)
                 OR (jadwal_kuliah_id IS NOT NULL AND jadwal_snapshot IS NOT NULL)
             ),
             ADD CONSTRAINT pertemuan_status_valid CHECK (
                 (status = 'terjadwal' AND mulai_aktual IS NULL AND selesai_aktual IS NULL
                     AND dibatalkan_at IS NULL AND realisasi IS NULL)
                 OR (status = 'berlangsung' AND mulai_aktual IS NOT NULL AND selesai_aktual IS NULL
                     AND dibatalkan_at IS NULL AND realisasi IS NULL)
                 OR (status = 'selesai' AND mulai_aktual IS NOT NULL AND selesai_aktual IS NOT NULL
                     AND selesai_aktual > mulai_aktual AND dibatalkan_at IS NULL
                     AND realisasi IS NOT NULL AND CHAR_LENGTH(TRIM(realisasi)) > 0)
                 OR (status = 'batal' AND mulai_aktual IS NULL AND selesai_aktual IS NULL
                     AND dibatalkan_at IS NOT NULL AND realisasi IS NULL)
             ),
             ADD CONSTRAINT pertemuan_media_valid CHECK (
                 (metode = 'daring' AND lokasi IS NULL)
                 OR (metode = 'luring' AND lokasi IS NOT NULL AND CHAR_LENGTH(TRIM(lokasi)) > 0
                     AND tautan_pertemuan IS NULL)
                 OR (metode = 'campuran' AND lokasi IS NOT NULL AND CHAR_LENGTH(TRIM(lokasi)) > 0)
             )"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('pertemuan');
    }
};
